<?php

namespace App\Http\Controllers;

use App\Models\MessageSwift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function isGlobalRole(): bool
    {
        return Auth::user()->hasRole(['super-admin', 'swift-manager', 'swift-operator']);
    }

    private function baseQuery()
    {
        if ($this->isGlobalRole()) {
            return MessageSwift::query();
        }

        return MessageSwift::readable(Auth::user());
    }

    private function getSwiftMessages(Request $request, ?string $defaultDirection = null, array $with = [])
    {
        $direction = match ($request->query('direction')) {
            'RECU' => 'IN',
            'EMIS' => 'OUT',
            default => $defaultDirection,
        };

        $query = $this->isGlobalRole()
            ? MessageSwift::with(array_merge(['creator'], $with))
            : MessageSwift::with(array_merge(['creator'], $with))->readable(Auth::user());

        if ($direction) {
            $query->where('DIRECTION', $direction);
        }
        if ($request->filled('categorie')) {
            $query->where('CATEGORIE', $request->categorie);
        }
        if ($request->filled('type_message')) {
            $query->where('TYPE_MESSAGE', $request->type_message);
        }
        if ($request->filled('status')) {
            $query->where('STATUS', $request->status);
        }
        if ($request->filled('sender_bic')) {
            $query->where('SENDER_BIC', 'like', '%'.trim($request->sender_bic).'%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('VALUE_DATE', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('VALUE_DATE', '<=', $request->date_to);
        }
        if ($request->filled('currency')) {
            $query->where('CURRENCY', $request->currency);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('REFERENCE', 'like', "%{$s}%")
                    ->orWhere('SENDER_NAME', 'like', "%{$s}%")
                    ->orWhere('RECEIVER_NAME', 'like', "%{$s}%")
                    ->orWhere('DESCRIPTION', 'like', "%{$s}%");
            });
        }

        return $query->orderBy('CREATED_AT', 'desc')->paginate(15)->withQueryString();
    }

    private function getSidebarData(): array
    {
        $user = Auth::user();
        $swiftController = app(MessageSwiftController::class);

        $canSeeReceived = $user->can('view-received-messages')
            || $user->hasRole(['super-admin', 'swift-manager', 'swift-operator', 'backoffice', 'monetique']);
        $canSeeEmitted = $user->can('view-emitted-messages')
            || $user->hasRole(['super-admin', 'swift-manager', 'swift-operator', 'chef-agence', 'chargee']);

        $receivedCategories = $canSeeReceived ? $swiftController->getSidebarCategories('IN') : [];
        $emittedCategories = $canSeeEmitted ? $swiftController->getSidebarCategories('OUT') : [];

        return [
            'receivedCategories' => $receivedCategories,
            'receivedTotal' => collect($receivedCategories)->sum('total'),
            'emittedCategories' => $emittedCategories,
            'emittedTotal' => collect($emittedCategories)->sum('total'),
        ];
    }

    private function formatVolume(float $amount, string $currency = 'USD'): string
    {
        $symbol = match ($currency) {
            'EUR' => '€', 'GBP' => '£', 'TND' => 'TND ', 'LYD' => 'LYD ', default => '$',
        };
        if ($amount >= 1_000_000) {
            return $symbol.number_format($amount / 1_000_000, 1).'M';
        }
        if ($amount >= 1_000) {
            return $symbol.number_format($amount / 1_000, 1).'K';
        }

        return $symbol.number_format($amount, 2);
    }

    private function getVolumeByDevise($baseQuery): array
    {
        $rows = (clone $baseQuery)
            ->whereIn('STATUS', ['processed', 'authorized'])
            ->whereNotNull('CURRENCY')
            ->selectRaw('CURRENCY, SUM(AMOUNT) as TOTAL')
            ->groupBy('CURRENCY')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $currency = $row->CURRENCY ?? $row->currency ?? null;
            $total = $row->TOTAL ?? $row->total ?? 0;
            if ($currency) {
                $result[$currency] = (float) $total;
            }
        }

        return $result;
    }

    private function getDominantVolumeFormatted(array $volumeByDevise): string
    {
        if (empty($volumeByDevise)) {
            return '$0';
        }
        $usdVolume = $volumeByDevise['USD'] ?? 0;
        $eurVolume = $volumeByDevise['EUR'] ?? 0;
        if ($usdVolume > 0 || $eurVolume > 0) {
            $dominantCcy = $usdVolume >= $eurVolume ? 'USD' : 'EUR';

            return $this->formatVolume(max($usdVolume, $eurVolume), $dominantCcy);
        }
        $firstCcy = array_key_first($volumeByDevise);

        return $this->formatVolume($volumeByDevise[$firstCcy], $firstCcy);
    }

    private function getVolumeTransactions(string $direction = 'IN'): \Illuminate\Support\Collection
    {
        try {
            return DB::table('transactions')
                ->join('messages_swift', 'transactions.message_swift_id', '=', 'messages_swift.id')
                ->where('messages_swift.direction', $direction)
                ->selectRaw('transactions.devise, SUM(transactions.montant) as total, COUNT(*) as nb')
                ->groupBy('transactions.devise')
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    // =========================================================
    // EXPORT CENTER
    // =========================================================

    public function exportCenter()
    {
        try {
            $exportJobs = DB::table('export_jobs')
                ->orderBy('date_demande', 'desc')
                ->limit(20)
                ->get();
        } catch (\Throwable $e) {
            $exportJobs = collect();
        }

        return view('swift.export-center', compact('exportJobs'));
    }

    // =========================================================
    // ADMIN → SUPER-ADMIN
    // =========================================================

    public function admin(Request $request)
    {
        $messages = $this->getSwiftMessages($request, null, ['transaction']);
        $sidebarData = $this->getSidebarData();

        $totalCount = MessageSwift::count();
        $receivedCount = MessageSwift::where('DIRECTION', 'IN')->count();
        $emittedCount = MessageSwift::where('DIRECTION', 'OUT')->count();
        $pendingCount = MessageSwift::where('STATUS', 'pending')->count();

        $volumeByDevise = $this->getVolumeByDevise(MessageSwift::query());
        $volumeFormatted = collect($volumeByDevise)
            ->map(fn ($v, $k) => $this->formatVolume((float) $v, $k))
            ->implode(' / ') ?: '$0';

        return view('super-admin.dashboard', array_merge(
            compact('messages', 'totalCount', 'receivedCount', 'emittedCount', 'pendingCount', 'volumeByDevise', 'volumeFormatted'),
            $sidebarData
        ));
    }

    // =========================================================
    // INTERNATIONAL ADMIN → SWIFT MANAGER
    // =========================================================

    public function internationalAdmin(Request $request)
    {
        $messages = $this->getSwiftMessages($request, null, ['transaction']);
        $sidebarData = $this->getSidebarData();
        $base = $this->baseQuery();

        $transCount = (clone $base)->count();
        $volumeByDevise = $this->getVolumeByDevise($base);
        $volumeFormatted = $this->getDominantVolumeFormatted($volumeByDevise);

        $bankCount = (clone $base)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('SENDER_BIC')->where('SENDER_BIC', '!=', '');
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('RECEIVER_BIC')->where('RECEIVER_BIC', '!=', '');
                });
            })
            ->selectRaw('COUNT(DISTINCT COALESCE(SENDER_BIC, RECEIVER_BIC)) as cnt')
            ->value('cnt') ?? 0;

        $pendingAuth = (clone $base)->where('STATUS', 'processed')->count();
        $authorizedToday = (clone $base)->where('STATUS', 'authorized')->whereDate('AUTHORIZED_AT', today())->count();
        $suspendedCount = (clone $base)->where('STATUS', 'suspended')->count();
        $transactions = $messages;

        return view('swift-manager.dashboard', array_merge(
            compact('messages', 'transactions', 'transCount', 'volumeFormatted', 'volumeByDevise',
                'bankCount', 'pendingAuth', 'authorizedToday', 'suspendedCount'),
            $sidebarData
        ));
    }

    // =========================================================
    // IA ANALYTICS — Graphiques BI (page dédiée)
    // =========================================================

    public function iaAnalytics(\Illuminate\Http\Request $request)
    {
        $sidebarData = $this->getSidebarData();

        // ── Filtres BI : période, niveau de risque, type de message
        $days        = (int) $request->get('days', 30);
        $days        = in_array($days, [7, 30, 90, 180, 365]) ? $days : 30;
        $filterLevel = $request->get('niveau');   // HIGH / MEDIUM / LOW / null
        $filterType  = $request->get('type_msg'); // MT103 / PACS.008 / etc.
        $dateFrom    = now()->subDays($days);

        // ── Base query filtrée par période
        $baseQuery = fn () => \App\Models\AnomalySwift::where('ANOMALIES_SWIFT.CREATED_AT', '>=', $dateFrom);

        // Appliquer filtre niveau si demandé
        $levelFilter = function ($q) use ($filterLevel) {
            if ($filterLevel) $q->where('ANOMALIES_SWIFT.NIVEAU_RISQUE', strtoupper($filterLevel));
        };

        $anomalyByLevel = ($baseQuery)()->selectRaw('NIVEAU_RISQUE as niveau, COUNT(*) as total')
            ->groupBy('NIVEAU_RISQUE')
            ->get()->pluck('total', 'niveau')->toArray();

        $anomalyByType = \App\Models\AnomalySwift::join('MESSAGES_SWIFT', 'ANOMALIES_SWIFT.MESSAGE_ID', '=', 'MESSAGES_SWIFT.ID')
            ->where('ANOMALIES_SWIFT.CREATED_AT', '>=', $dateFrom)
            ->where('ANOMALIES_SWIFT.NIVEAU_RISQUE', '!=', 'LOW')
            ->selectRaw('MESSAGES_SWIFT.TYPE_MESSAGE as type_msg, COUNT(*) as total')
            ->groupBy('MESSAGES_SWIFT.TYPE_MESSAGE')
            ->get()->pluck('total', 'type_msg')->toArray();

        // Timeline avec période variable
        $scoreTimeline = \App\Models\AnomalySwift::selectRaw('TRUNC(CREATED_AT) as jour, ROUND(AVG(SCORE), 1) as avg_score')
            ->where('CREATED_AT', '>=', $dateFrom)
            ->groupByRaw('TRUNC(CREATED_AT)')
            ->orderByRaw('TRUNC(CREATED_AT)')
            ->get()
            ->map(fn ($r) => ['jour' => \Carbon\Carbon::parse($r->jour)->format('d/m'), 'avg_score' => (float) $r->avg_score])
            ->toArray();

        // ── KPIs sur la période filtrée
        $totalAnomalies = ($baseQuery)()->count();
        $highCount      = ($baseQuery)()->where('NIVEAU_RISQUE', 'HIGH')->count();
        $mediumCount    = ($baseQuery)()->where('NIVEAU_RISQUE', 'MEDIUM')->count();
        $lowCount       = ($baseQuery)()->where('NIVEAU_RISQUE', 'LOW')->count();
        $avgScore       = round((float) ($baseQuery)()->avg('SCORE'), 1);

        // ── KPI tendance : comparaison avec la période précédente (même durée)
        $prevFrom  = now()->subDays($days * 2);
        $prevTo    = now()->subDays($days);
        $prevTotal = \App\Models\AnomalySwift::whereBetween('CREATED_AT', [$prevFrom, $prevTo])->count();
        $prevHigh  = \App\Models\AnomalySwift::whereBetween('CREATED_AT', [$prevFrom, $prevTo])->where('NIVEAU_RISQUE', 'HIGH')->count();
        $prevAvg   = round((float) \App\Models\AnomalySwift::whereBetween('CREATED_AT', [$prevFrom, $prevTo])->avg('SCORE'), 1);
        $trendTotal = $prevTotal > 0 ? round((($totalAnomalies - $prevTotal) / $prevTotal) * 100, 1) : null;
        $trendHigh  = $prevHigh  > 0 ? round((($highCount  - $prevHigh)  / $prevHigh)  * 100, 1) : null;
        $trendAvg   = $prevAvg   > 0 ? round($avgScore - $prevAvg, 1) : null;

        // Statut vérification
        $verifiedCount = ($baseQuery)()->whereNotNull('verifie_par')->count();
        $rejectedCount = ($baseQuery)()->whereNotNull('rejetee_par')->count();
        $pendingCount  = ($baseQuery)()->whereNull('verifie_par')->whereNull('rejetee_par')->count();

        // Anomalies MEDIUM+HIGH par type séparées
        $anomalyByTypeMedium = \App\Models\AnomalySwift::join('MESSAGES_SWIFT', 'ANOMALIES_SWIFT.MESSAGE_ID', '=', 'MESSAGES_SWIFT.ID')
            ->where('ANOMALIES_SWIFT.CREATED_AT', '>=', $dateFrom)
            ->where('ANOMALIES_SWIFT.NIVEAU_RISQUE', 'MEDIUM')
            ->selectRaw('MESSAGES_SWIFT.TYPE_MESSAGE as type_msg, COUNT(*) as total')
            ->groupBy('MESSAGES_SWIFT.TYPE_MESSAGE')
            ->get()->pluck('total', 'type_msg')->toArray();

        $anomalyByTypeHigh = \App\Models\AnomalySwift::join('MESSAGES_SWIFT', 'ANOMALIES_SWIFT.MESSAGE_ID', '=', 'MESSAGES_SWIFT.ID')
            ->where('ANOMALIES_SWIFT.CREATED_AT', '>=', $dateFrom)
            ->where('ANOMALIES_SWIFT.NIVEAU_RISQUE', 'HIGH')
            ->selectRaw('MESSAGES_SWIFT.TYPE_MESSAGE as type_msg, COUNT(*) as total')
            ->groupBy('MESSAGES_SWIFT.TYPE_MESSAGE')
            ->get()->pluck('total', 'type_msg')->toArray();

        $allTypes = array_unique(array_merge(array_keys($anomalyByTypeMedium), array_keys($anomalyByTypeHigh)));
        sort($allTypes);

        // ── Top règles déclenchées (BI : quelles règles sont les plus fréquentes ?)
        $topRules = \App\Models\AnomalySwift::where('CREATED_AT', '>=', $dateFrom)
            ->whereNotNull('RAISONS')
            ->get(['RAISONS'])
            ->flatMap(fn ($a) => is_array($a->raisons) ? $a->raisons : [])
            ->countBy()
            ->sortDesc()
            ->take(8)
            ->toArray();

        // Taux de résolution
        $resolutionRate = $totalAnomalies > 0
            ? round(($verifiedCount + $rejectedCount) / $totalAnomalies * 100, 1)
            : 0;

        // Top 5 HIGH non traitées
        $topHighAnomalies = \App\Models\AnomalySwift::where('NIVEAU_RISQUE', 'HIGH')
            ->whereNull('verifie_par')->whereNull('rejetee_par')
            ->with('message')->orderByDesc('SCORE')->limit(5)->get();

        // Distribution scores
        $scoreRanges = [
            '0–19'   => ($baseQuery)()->whereBetween('SCORE', [0, 19.99])->count(),
            '20–39'  => ($baseQuery)()->whereBetween('SCORE', [20, 39.99])->count(),
            '40–59'  => ($baseQuery)()->whereBetween('SCORE', [40, 59.99])->count(),
            '60–79'  => ($baseQuery)()->whereBetween('SCORE', [60, 79.99])->count(),
            '80–100' => ($baseQuery)()->whereBetween('SCORE', [80, 100])->count(),
        ];

        return view('swift-manager.ia-analytics', array_merge(
            compact('anomalyByLevel', 'anomalyByType', 'scoreTimeline',
                'totalAnomalies', 'highCount', 'mediumCount', 'lowCount', 'avgScore',
                'verifiedCount', 'rejectedCount', 'pendingCount',
                'anomalyByTypeMedium', 'anomalyByTypeHigh', 'allTypes',
                'resolutionRate', 'topHighAnomalies', 'scoreRanges',
                'days', 'filterLevel', 'filterType',
                'trendTotal', 'trendHigh', 'trendAvg',
                'topRules'),
            $sidebarData
        ));
    }

    // =========================================================
    // INTERNATIONAL USER → SWIFT OPERATOR
    // =========================================================

    public function internationalUser(Request $request)
    {
        $messages = $this->getSwiftMessages($request, null, ['transaction']);
        $sidebarData = $this->getSidebarData();
        $base = $this->baseQuery();

        $transCount = (clone $base)->count();
        $inCount = (clone $base)->whereIn('DIRECTION', ['IN', 'RECU'])->count();
        $outCount = (clone $base)->whereIn('DIRECTION', ['OUT', 'EMIS'])->count();
        $authorizedCount = (clone $base)->where('STATUS', 'authorized')->count();
        $rejectedCount = (clone $base)->where('STATUS', 'rejected')->count();
        $pendingAuth = (clone $base)->whereNotIn('STATUS', ['authorized', 'rejected', 'suspended'])->count();

        $volumeByDevise = $this->getVolumeByDevise($base);
        $volumeFormatted = $this->getDominantVolumeFormatted($volumeByDevise);
        $transactions = $messages;

        return view('swift-operator.dashboard', array_merge(
            compact('messages', 'transactions', 'transCount', 'inCount', 'outCount',
                'authorizedCount', 'rejectedCount', 'pendingAuth',
                'volumeFormatted', 'volumeByDevise'),
            $sidebarData
        ));
    }

    // =========================================================
    // BACKOFFICE
    // =========================================================

    public function backoffice(Request $request)
    {
        $user = Auth::user();
        $messages = $this->getSwiftMessages($request, 'IN', ['transaction']);
        $sidebarData = $this->getSidebarData();

        $base = MessageSwift::readable($user);
        $totalCount = (clone $base)->count();
        $inCount = (clone $base)->where('DIRECTION', 'IN')->count();
        $pendingCount = (clone $base)->where('STATUS', 'pending')->count();

        $volumeParDevise = $this->getVolumeTransactions('IN');

        return view('backoffice.dashboard', array_merge(
            compact('messages', 'totalCount', 'inCount', 'pendingCount', 'volumeParDevise'),
            $sidebarData
        ));
    }

    // =========================================================
    // MONÉTIQUE
    // =========================================================

    public function monetique(Request $request)
    {
        $user = Auth::user();
        $messages = $this->getSwiftMessages($request, 'IN', ['transaction']);
        $sidebarData = $this->getSidebarData();

        $base = MessageSwift::readable($user);
        $totalCount = (clone $base)->count();
        $inCount = (clone $base)->where('DIRECTION', 'IN')->count();
        $pendingCount = (clone $base)->where('STATUS', 'pending')->count();

        return view('monetique.dashboard', array_merge(
            compact('messages', 'totalCount', 'inCount', 'pendingCount'),
            $sidebarData
        ));
    }

    // =========================================================
    // CHEF D'AGENCE
    // =========================================================

    public function chefAgence(Request $request)
    {
        $user = Auth::user();
        $messages = $this->getSwiftMessages($request, 'OUT', ['transaction']);
        $sidebarData = $this->getSidebarData();

        $base = MessageSwift::readable($user);
        $totalCount = (clone $base)->count();
        $outCount = (clone $base)->where('DIRECTION', 'OUT')->count();
        $pendingCount = (clone $base)->where('STATUS', 'pending')->count();

        return view('chef-agence.dashboard', array_merge(
            compact('messages', 'totalCount', 'outCount', 'pendingCount'),
            $sidebarData
        ));
    }

    // =========================================================
    // CHARGÉ(E)
    // =========================================================

    public function chargee(Request $request)
    {
        $user = Auth::user();
        $messages = $this->getSwiftMessages($request, 'OUT', ['transaction']);
        $sidebarData = $this->getSidebarData();

        $base = MessageSwift::readable($user);
        $totalCount = (clone $base)->count();
        $outCount = (clone $base)->where('DIRECTION', 'OUT')->count();
        $pendingCount = (clone $base)->where('STATUS', 'pending')->count();

        return view('chargee.dashboard', array_merge(
            compact('messages', 'totalCount', 'outCount', 'pendingCount'),
            $sidebarData
        ));
    }

    // =========================================================
    // COMPLIANCE
    // =========================================================

    public function compliance(Request $request)
    {
        $messages = $this->getSwiftMessages($request, null, ['transaction']);
        $sidebarData = $this->getSidebarData();

        $totalCount = MessageSwift::count();
        $pendingCount = MessageSwift::where('STATUS', 'pending')->count();

        try {
            $reglesAml = DB::table('regles_aml')
                ->where('active', 1)
                ->orderBy('seuil', 'desc')
                ->get();
        } catch (\Throwable $e) {
            $reglesAml = collect();
        }

        return view('compliance.dashboard', array_merge(
            compact('messages', 'totalCount', 'pendingCount', 'reglesAml'),
            $sidebarData
        ));
    }
}
