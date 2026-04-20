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
        $query = AnomalySwift::with(['message', 'verificateur'])
            ->orderByDesc('created_at');

        if ($request->filled('niveau_risque')) {
            $query->where('niveau_risque', $request->niveau_risque);
        }

        if ($request->input('verifie') === 'oui') {
            $query->whereNotNull('verifie_par');
        } elseif ($request->input('verifie') === 'non') {
            $query->whereNull('verifie_par');
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
            'non_verifiees' => AnomalySwift::whereNull('verifie_par')->where('niveau_risque', 'HIGH')->count(),
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
