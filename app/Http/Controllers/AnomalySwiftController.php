<?php

namespace App\Http\Controllers;

use App\Models\AnomalySwift;
use App\Models\MessageSwift;
use App\Services\AnomalyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnomalySwiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================
    // LISTE — index()
    // =========================================================

    public function index(Request $request)
    {
        $query = AnomalySwift::with(['message', 'verificateur', 'rejecteur'])
            ->orderByDesc('created_at');

        if ($request->filled('niveau_risque')) {
            $query->where('niveau_risque', $request->niveau_risque);
        }

        if ($request->input('verifie') === 'oui') {
            $query->where(function ($q) {
                $q->whereNotNull('verifie_par')->orWhereNotNull('rejetee_par');
            });
        } elseif ($request->input('verifie') === 'non') {
            $query->whereNull('verifie_par')->whereNull('rejetee_par');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $anomalies = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => AnomalySwift::count(),
            'high' => AnomalySwift::where('niveau_risque', 'HIGH')->count(),
            'medium' => AnomalySwift::where('niveau_risque', 'MEDIUM')->count(),
            'low' => AnomalySwift::where('niveau_risque', 'LOW')->count(),
            'non_verifiees' => AnomalySwift::where('niveau_risque', 'HIGH')
                ->whereNull('verifie_par')
                ->whereNull('rejetee_par')
                ->count(),
        ];

        return view('swift.anomalies.index', compact('anomalies', 'stats'));
    }

    // =========================================================
    // DÉTAIL — show()
    // =========================================================

    public function show($id)
    {
        $anomaly = AnomalySwift::with(['message.details', 'verificateur'])->findOrFail($id);

        return view('swift.anomalies.show', compact('anomaly'));
    }

    // =========================================================
    // VÉRIFIER — verify()
    // =========================================================

    public function verify($id)
    {
        $anomaly = AnomalySwift::findOrFail($id);
        $user = Auth::user();

        if (! $user->hasRole(['super-admin', 'swift-manager'])) {
            abort(403, 'Action non autorisée.');
        }

        $anomaly->update([
            'verifie_par' => $user->id,
            'verifie_at' => now(),
        ]);

        return back()->with('success', "Anomalie #$id marquée comme vérifiée.");
    }

    // =========================================================
    // REJETER — reject()
    // Marque l'anomalie comme rejetée ET passe le message en STATUS=rejected
    // =========================================================

    public function reject($id)
    {
        $anomaly = AnomalySwift::findOrFail($id);
        $user = Auth::user();

        if (! $user->hasRole(['super-admin', 'swift-manager'])) {
            abort(403, 'Action non autorisée.');
        }

        // Marquer l'anomalie comme rejetée (prise en charge)
        $anomaly->update([
            'rejetee_par' => $user->id,
            'rejetee_at'  => now(),
        ]);

        // Passer le message SWIFT en rejected
        if ($anomaly->message) {
            $anomaly->message->update(['STATUS' => 'rejected']);
        }

        return back()->with('success', "Anomalie #$id rejetée — message marqué comme rejeté.");
    }

    // =========================================================
    // AUTO-DÉCISION IA SUR TOUTES LES ANOMALIES EXISTANTES
    // LOW  → STATUS=authorized   (score < 20, risque faible)
    // MEDIUM → STATUS=processed  (score 20-59, risque moyen)
    // HIGH → laissé en attente   (score ≥ 60, revue manuelle)
    // =========================================================

    public function autoDecideAll()
    {
        $user = Auth::user();

        if (! $user->hasRole(['super-admin', 'swift-manager'])) {
            abort(403, 'Action non autorisée.');
        }

        $anomalies = AnomalySwift::whereNull('verifie_par')
            ->whereNull('rejetee_par')
            ->whereIn('niveau_risque', ['LOW', 'MEDIUM'])
            ->with('message')
            ->get();

        $authorized = 0;
        $processed  = 0;

        foreach ($anomalies as $anomaly) {
            if (! $anomaly->message) {
                continue;
            }

            $currentStatus = strtolower($anomaly->message->STATUS ?? $anomaly->message->status ?? '');
            if (in_array($currentStatus, ['authorized', 'rejected', 'suspended'])) {
                continue;
            }

            if ($anomaly->niveau_risque === 'LOW') {
                $anomaly->message->update(['STATUS' => 'authorized']);
                $anomaly->update(['verifie_par' => $user->id, 'verifie_at' => now()]);
                $authorized++;
            } elseif ($anomaly->niveau_risque === 'MEDIUM') {
                $anomaly->message->update(['STATUS' => 'authorized']);
                $anomaly->update(['verifie_par' => $user->id, 'verifie_at' => now()]);
                $authorized++;
            }
        }

        $total = $authorized;
        return redirect()->route('swift.anomalies.index')
            ->with('success', "{$total} anomalie(s) traitées automatiquement par l'IA → toutes autorisées (LOW + MEDIUM). Les anomalies critiques (HIGH) restent en attente de revue manuelle.");
    }

    // =========================================================
    // AUTO-TRAITER LES FAIBLES — autoProcessLow()
    // Vérifie automatiquement toutes les anomalies LOW non traitées
    // et passe leurs messages en STATUS=processed
    // =========================================================

    public function autoProcessLow()
    {
        $user = Auth::user();

        if (! $user->hasRole(['super-admin', 'swift-manager'])) {
            abort(403, 'Action non autorisée.');
        }

        $anomalies = AnomalySwift::where('niveau_risque', 'LOW')
            ->whereNull('verifie_par')
            ->whereNull('rejetee_par')
            ->with('message')
            ->get();

        $count = 0;
        foreach ($anomalies as $anomaly) {
            $anomaly->update([
                'verifie_par' => $user->id,
                'verifie_at'  => now(),
            ]);
            if ($anomaly->message) {
                $anomaly->message->update(['STATUS' => 'processed']);
            }
            $count++;
        }

        return redirect()->route('swift.anomalies.index')
            ->with('success', "{$count} anomalies à risque faible traitées automatiquement.");
    }

    // =========================================================
    // RE-ANALYSER — reanalyze()
    // =========================================================

    public function reanalyze($id)
    {
        $anomaly = AnomalySwift::findOrFail($id);
        $user = Auth::user();

        if (! $user->hasRole(['super-admin', 'swift-manager'])) {
            abort(403, 'Action non autorisée.');
        }

        $result = app(AnomalyService::class)->analyze($anomaly->message);

        return back()->with('success',
            "Re-analyse effectuée. Nouveau score : {$result['score']}/100 ({$result['niveau_risque']})"
        );
    }

    // =========================================================
    // ANALYSER UN SEUL MESSAGE — analyzeSingle()            ← NOUVEAU
    // =========================================================

    public function analyzeSingle($id)
    {
        $message = MessageSwift::findOrFail($id);
        $user = Auth::user();

        if (! $user->hasRole(['super-admin', 'swift-manager'])) {
            abort(403, 'Action non autorisée.');
        }

        $result = app(AnomalyService::class)->analyze($message);

        return back()->with('success',
            "Analyse terminée — Score : {$result['score']}/100 ({$result['niveau_risque']})"
        );
    }

    // =========================================================
    // ANALYSER TOUS — analyzeAll()
    // =========================================================

    public function analyzeAll()
    {
        $user = Auth::user();

        if (! $user->hasRole(['super-admin', 'swift-manager'])) {
            abort(403, 'Action non autorisée.');
        }

        $service = app(AnomalyService::class);
        $count = 0;

        MessageSwift::chunk(100, function ($messages) use ($service, &$count) {
            foreach ($messages as $message) {
                try {
                    $service->analyze($message);
                    $count++;
                } catch (\Throwable $e) {
                    \Log::warning("Analyse échouée #{$message->id} : {$e->getMessage()}");
                }
            }
        });

        return redirect()->route('swift.anomalies.index')
            ->with('success', "{$count} messages analysés avec succès.");
    }
}
