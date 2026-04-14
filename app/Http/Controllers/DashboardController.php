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
        $direction = match($request->query('direction')) {
            'RECU'  => 'IN',
            'EMIS'  => 'OUT',
            default => $defaultDirection,
        };

        $query = $this->isGlobalRole()
            ? MessageSwift::with(array_merge(['creator'], $with))
            : MessageSwift::with(array_merge(['creator'], $with))->readable(Auth::user());

        if ($direction)                  { $query->where('DIRECTION', $direction); }
        if ($request->filled('categorie'))    { $query->where('CATEGORIE', $request->categorie); }
        if ($request->filled('type_message')) { $query->where('TYPE_MESSAGE', $request->type_message); }
        if ($request->filled('status'))       { $query->where('STATUS', $request->status); }
        if ($request->filled('sender_bic'))   { $query->where('SENDER_BIC', 'like', '%' . trim($request->sender_bic) . '%'); }
        if ($request->filled('date_from'))    { $query->whereDate('VALUE_DATE', '>=', $request->date_from); }
        if ($request->filled('date_to'))      { $query->whereDate('VALUE_DATE', '<=', $request->date_to); }
        if ($request->filled('currency'))     { $query->where('CURRENCY', $request->currency); }
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
        $user            = Auth::user();
        $swiftController = app(MessageSwiftController::class);

        $canSeeReceived = $user->can('view-received-messages')
            || $user->hasRole(['super-admin', 'swift-manager', 'swift-operator', 'backoffice', 'monetique']);
        $canSeeEmitted  = $user->can('view-emitted-messages')
            || $user->hasRole(['super-admin', 'swift-manager', 'swift-operator', 'chef-agence', 'chargee']);

        $receivedCategories = $canSeeReceived ? $swiftController->getSidebarCategories('IN')  : [];
        $emittedCategories  = $canSeeEmitted  ? $swiftController->getSidebarCategories('OUT') : [];

        return [
            'receivedCategories' => $receivedCategories,
            'receivedTotal'      => collect($receivedCategories)->sum('total'),
            'emittedCategories'  => $emittedCategories,
            'emittedTotal'       => collect($emittedCategories)->sum('total'),
        ];
    }

    private function formatVolume(float $amount, string $currency = 'USD'): string
    {
        $symbol = match($currency) {
            'EUR' => '€', 'GBP' => '£', 'TND' => 'TND ', 'LYD' => 'LYD ', default => '$',
        };
        if ($amount >= 1_000_000) return $symbol . number_format($amount / 1_000_000, 1) . 'M';
        if ($amount >= 1_000)     return $symbol . number_format($amount / 1_000, 1) . 'K';
        return $symbol . number_format($amount, 2);
    }

    private function getVolumeByDevise($baseQuery): array
    {
        $rows = (clone $baseQuery)
            ->where('STATUS', 'processed')
            ->whereNotNull('CURRENCY')
            ->selectRaw('CURRENCY, SUM(AMOUNT) as TOTAL')
            ->groupBy('CURRENCY')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $currency = $row->CURRENCY ?? $row->currency ?? null;
            $total    = $row->TOTAL    ?? $row->total    ?? 0;
            if ($currency) $result[$currency] = (float) $total;
        }
        return $result;
    }

    private function getDominantVolumeFormatted(array $volumeByDevise): string
    {
        if (empty($volumeByDevise)) return '$0';
        $usdVolume = $volumeByDevise['USD'] ?? 0;
        $eurVolume = $volumeByDevise['EUR'] ?? 0;
        if ($usdVolume > 0 || $eurVolume > 0) {
            $dominantCcy    = $usdVolume >= $eurVolume ? 'USD' : 'EUR';
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
        $messages    = $this->getSwiftMessages($request, null, ['transaction']);
        $sidebarData = $this->getSidebarData();

        $totalCount    = MessageSwift::count();
        $receivedCount = MessageSwift::where('DIRECTION', 'IN')->count();
        $emittedCount  = MessageSwift::where('DIRECTION', 'OUT')->count();
        $pendingCount  = MessageSwift::where('STATUS', 'pending')->count();

        $volumeByDevise  = $this->getVolumeByDevise(MessageSwift::query());
        $volumeFormatted = collect($volumeByDevise)
            ->map(fn($v, $k) => $this->formatVolume((float)$v, $k))
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
        $messages    = $this->getSwiftMessages($request, null, ['transaction']);
        $sidebarData = $this->getSidebarData();
        $base        = $this->baseQuery();

        $transCount      = (clone $base)->count();
        $volumeByDevise  = $this->getVolumeByDevise($base);
        $volumeFormatted = $this->getDominantVolumeFormatted($volumeByDevise);

        $bankCount = (clone $base)
            ->whereNotNull('SENDER_BIC')->where('SENDER_BIC', '!=', '')
            ->distinct('SENDER_BIC')->count('SENDER_BIC');

        $pendingAuth     = (clone $base)->where('STATUS', 'processed')->count();
        $authorizedToday = (clone $base)->where('STATUS', 'authorized')->whereDate('AUTHORIZED_AT', today())->count();
        $suspendedCount  = (clone $base)->where('STATUS', 'suspended')->count();
        $transactions    = $messages;

        return view('swift-manager.dashboard', array_merge(
            compact('messages', 'transactions', 'transCount', 'volumeFormatted', 'volumeByDevise',
                    'bankCount', 'pendingAuth', 'authorizedToday', 'suspendedCount'),
            $sidebarData
        ));
    }

    // =========================================================
    // INTERNATIONAL USER → SWIFT OPERATOR
    // =========================================================

    public function internationalUser(Request $request)
    {
        $messages    = $this->getSwiftMessages($request, null, ['transaction']);
        $sidebarData = $this->getSidebarData();
        $base        = $this->baseQuery();

        $transCount      = (clone $base)->count();
        $volumeByDevise  = $this->getVolumeByDevise($base);
        $volumeFormatted = $this->getDominantVolumeFormatted($volumeByDevise);

        $bankCount = (clone $base)
            ->whereNotNull('SENDER_BIC')->where('SENDER_BIC', '!=', '')
            ->distinct('SENDER_BIC')->count('SENDER_BIC');

        $pendingAuth  = (clone $base)->where('STATUS', 'processed')->count();
        $transactions = $messages;

        return view('swift-operator.dashboard', array_merge(
            compact('messages', 'transactions', 'transCount', 'volumeFormatted', 'volumeByDevise', 'bankCount', 'pendingAuth'),
            $sidebarData
        ));
    }

    // =========================================================
    // BACKOFFICE
    // =========================================================

    public function backoffice(Request $request)
    {
        $user        = Auth::user();
        $messages    = $this->getSwiftMessages($request, 'IN', ['transaction']);
        $sidebarData = $this->getSidebarData();

        $base         = MessageSwift::readable($user);
        $totalCount   = (clone $base)->count();
        $inCount      = (clone $base)->where('DIRECTION', 'IN')->count();
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
        $user        = Auth::user();
        $messages    = $this->getSwiftMessages($request, 'IN', ['transaction']);
        $sidebarData = $this->getSidebarData();

        $base         = MessageSwift::readable($user);
        $totalCount   = (clone $base)->count();
        $inCount      = (clone $base)->where('DIRECTION', 'IN')->count();
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
        $user        = Auth::user();
        $messages    = $this->getSwiftMessages($request, 'OUT', ['transaction']);
        $sidebarData = $this->getSidebarData();

        $base         = MessageSwift::readable($user);
        $totalCount   = (clone $base)->count();
        $outCount     = (clone $base)->where('DIRECTION', 'OUT')->count();
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
        $user        = Auth::user();
        $messages    = $this->getSwiftMessages($request, 'OUT', ['transaction']);
        $sidebarData = $this->getSidebarData();

        $base         = MessageSwift::readable($user);
        $totalCount   = (clone $base)->count();
        $outCount     = (clone $base)->where('DIRECTION', 'OUT')->count();
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
        $messages    = $this->getSwiftMessages($request, null, ['transaction']);
        $sidebarData = $this->getSidebarData();

        $totalCount   = MessageSwift::count();
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