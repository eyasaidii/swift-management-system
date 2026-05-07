@extends('layouts.app')
@section('title', 'Dashboard Swift Operator - BTL Bank')
@section('page-title', 'Swift Operator')
@push('styles')
<style>
/* ── Layout ── */
.dash-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.dash-header-left h1{font-size:1.3rem;font-weight:700;color:#111827;margin:0;line-height:1.3;display:flex;align-items:center;gap:.5rem;}
.dash-header-left p{font-size:.78rem;color:#6b7280;margin:.2rem 0 0;}
.dash-btns{display:flex;gap:.4rem;flex-wrap:wrap;align-items:center;}
.dbtn{font-size:.78rem;font-weight:600;padding:.38rem .9rem;border-radius:8px;display:inline-flex;align-items:center;gap:.38rem;white-space:nowrap;text-decoration:none;border:none;transition:filter .15s;cursor:pointer;}
.dbtn:hover{filter:brightness(.9);text-decoration:none;}
.dbtn-green{background:#10b981;color:#fff;}
.dbtn-amber{background:#f59e0b;color:#fff;}

/* ── KPI Cards ── */
.kpi-card{border-radius:14px;padding:1.2rem 1.4rem;position:relative;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,.11);height:100%;transition:transform .15s;}
.kpi-card:hover{transform:translateY(-2px);}
.kpi-val{font-size:2rem;font-weight:800;color:#fff;line-height:1.05;position:relative;z-index:1;}
.kpi-lbl{font-size:.68rem;font-weight:600;color:rgba(255,255,255,.8);margin-top:.25rem;text-transform:uppercase;letter-spacing:.05em;position:relative;z-index:1;}
.kpi-sub{font-size:.62rem;color:rgba(255,255,255,.55);margin-top:.15rem;position:relative;z-index:1;}
.kpi-ico{position:absolute;right:.8rem;top:50%;transform:translateY(-50%);font-size:3rem;color:rgba(255,255,255,.15);pointer-events:none;}
.kpi-blue{background:linear-gradient(135deg,#3b82f6,#1d4ed8);}
.kpi-cyan{background:linear-gradient(135deg,#06b6d4,#0369a1);}
.kpi-green{background:linear-gradient(135deg,#10b981,#047857);}
.kpi-emerald{background:linear-gradient(135deg,#059669,#065f46);}
.kpi-amber{background:linear-gradient(135deg,#f59e0b,#b45309);}
.kpi-red{background:linear-gradient(135deg,#ef4444,#b91c1c);}
.kpi-indigo{background:linear-gradient(135deg,#6366f1,#4338ca);}

/* ── Section summary bar ── */
.summary-bar{background:#fff;border:1px solid #f1f5f9;border-radius:12px;padding:.7rem 1.2rem;display:flex;gap:1.5rem;flex-wrap:wrap;align-items:center;margin-bottom:1.25rem;box-shadow:0 1px 4px rgba(0,0,0,.05);}
.sbar-item{display:flex;align-items:center;gap:.5rem;font-size:.75rem;}
.sbar-dot{width:9px;height:9px;border-radius:50%;}

/* ── Filter card ── */
.filt-card{background:#fff;border-radius:12px;padding:1rem 1.25rem;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:1.25rem;border:1px solid #f3f4f6;}
.filt-title{font-size:.8rem;font-weight:700;color:#374151;margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem;}

/* ── Table ── */
.tbl-card{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.07);border:1px solid #f1f5f9;}
.tbl-card-hd{display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.1rem;border-bottom:2px solid #f1f5f9;}
.tbl-card-title{font-size:.88rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:.5rem;}
.swift-tbl{width:100%;border-collapse:collapse;}
.swift-tbl thead th{font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;background:#f8fafc;padding:.55rem .85rem;border-bottom:1px solid #e2e8f0;white-space:nowrap;}
.swift-tbl tbody td{padding:.6rem .85rem;font-size:.8rem;color:#1e293b!important;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.swift-tbl tbody td span:not(.sbadge):not(.dir-tag){color:#1e293b!important;}
.swift-tbl tbody tr:last-child td{border-bottom:none;}
.swift-tbl tbody tr:hover td{background:#f8fafc;}

/* ── Status badges ── */
.sbadge{display:inline-flex;align-items:center;gap:.25rem;font-size:.68rem;font-weight:600;padding:.22rem .6rem;border-radius:20px;white-space:nowrap;}
.sb-pending   {background:#fffbeb;color:#92400e;}
.sb-processed {background:#eff6ff;color:#1e40af;}
.sb-authorized{background:#dcfce7;color:#166534;}
.sb-rejected  {background:#fee2e2;color:#dc2626;}
.sb-suspended {background:#f3f4f6;color:#6b7280;}

/* ── Direction tags ── */
.dir-tag{display:inline-block;font-size:.63rem;font-weight:700;padding:.16rem .42rem;border-radius:4px;}
.dir-in {background:#dbeafe;color:#1d4ed8;}
.dir-out{background:#dcfce7;color:#166534;}

/* ── Action buttons ── */
.tbl-actions{display:flex;gap:.22rem;}
.ta-btn{font-size:.68rem;font-weight:600;padding:.22rem .5rem;border-radius:5px;border:1px solid;text-decoration:none;display:inline-flex;align-items:center;gap:.2rem;transition:background .12s;background:transparent;cursor:pointer;white-space:nowrap;}
.ta-view{border-color:#cbd5e1;color:#475569;}.ta-view:hover{background:#f1f5f9;}
.ta-mt  {border-color:#bbf7d0;color:#065f46;}.ta-mt:hover  {background:#dcfce7;}
.ta-mx  {border-color:#e0e7ff;color:#4338ca;}.ta-mx:hover  {background:#eef2ff;}

/* ── Misc ── */
.empty-state{text-align:center;padding:3rem 1rem;color:#9ca3af;}
.empty-state i{font-size:2.2rem;margin-bottom:.7rem;display:block;opacity:.2;}
.tbl-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;padding:.65rem 1.1rem;border-top:1px solid #f1f5f9;}
.tbl-footer-info{font-size:.72rem;color:#94a3b8;}
</style>
@endpush

@section('content')
<div style="max-width:1440px;">

    {{-- ── HEADER ── --}}
    <div class="dash-header">
        <div class="dash-header-left">
            <h1><i class="fas fa-globe-americas" style="color:#3b82f6;font-size:1.1rem;"></i> Swift Operator</h1>
            <p>Opérations transfrontalières &mdash; <span style="color:#10b981;font-weight:600;">{{ $transCount ?? 0 }}</span> messages au total</p>
        </div>
        <div class="dash-btns">
            @can('create', App\Models\MessageSwift::class)
                <a href="{{ route('swift.create') }}" class="dbtn dbtn-green"><i class="fas fa-plus"></i> Nouveau SWIFT</a>
            @endcan
            @can('import', App\Models\MessageSwift::class)
                <a href="{{ route('swift.import.form') }}" class="dbtn dbtn-amber"><i class="fas fa-file-import"></i> Importer</a>
            @endcan
        </div>
    </div>

    {{-- ── KPI CARDS ── --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="kpi-card kpi-blue">
                <div class="kpi-val">{{ $transCount ?? 0 }}</div>
                <div class="kpi-lbl">Total messages</div>
                <i class="fas fa-exchange-alt kpi-ico"></i>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="kpi-card kpi-cyan">
                <div class="kpi-val">{{ $inCount ?? 0 }}</div>
                <div class="kpi-lbl">Reçus</div>
                <i class="fas fa-arrow-down kpi-ico"></i>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="kpi-card kpi-indigo">
                <div class="kpi-val">{{ $outCount ?? 0 }}</div>
                <div class="kpi-lbl">Émis</div>
                <i class="fas fa-arrow-up kpi-ico"></i>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="kpi-card kpi-emerald">
                <div class="kpi-val">{{ $authorizedCount ?? 0 }}</div>
                <div class="kpi-lbl">Autorisés</div>
                <i class="fas fa-check-circle kpi-ico"></i>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="kpi-card kpi-red">
                <div class="kpi-val">{{ $rejectedCount ?? 0 }}</div>
                <div class="kpi-lbl">Rejetés</div>
                <i class="fas fa-times-circle kpi-ico"></i>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="kpi-card kpi-green">
                <div class="kpi-val" style="font-size:1.3rem;">{{ $volumeFormatted ?? '0' }}</div>
                <div class="kpi-lbl">Volume traité</div>
                @if(isset($volumeByDevise) && count($volumeByDevise) > 0)
                    <div class="kpi-sub">
                        @foreach(array_slice($volumeByDevise, 0, 3, true) as $d => $v)
                            {{ $d }}: {{ number_format($v/1000,0) }}K&nbsp;
                        @endforeach
                    </div>
                @endif
                <i class="fas fa-coins kpi-ico"></i>
            </div>
        </div>
    </div>

    {{-- ── SUMMARY BAR ── --}}
    <div class="summary-bar">
        <div style="font-size:.75rem;font-weight:700;color:#374151;margin-right:.5rem;">Répartition :</div>
        @php
            $tot = max(1, $transCount ?? 1);
        @endphp
        <div class="sbar-item">
            <div class="sbar-dot" style="background:#22c55e;"></div>
            <span>Autorisés <strong style="color:#16a34a;">{{ $authorizedCount ?? 0 }}</strong>
            ({{ round(($authorizedCount??0)/$tot*100) }}%)</span>
        </div>
        <div class="sbar-item">
            <div class="sbar-dot" style="background:#ef4444;"></div>
            <span>Rejetés <strong style="color:#dc2626;">{{ $rejectedCount ?? 0 }}</strong>
            ({{ round(($rejectedCount??0)/$tot*100) }}%)</span>
        </div>
        <div class="sbar-item">
            <div class="sbar-dot" style="background:#3b82f6;"></div>
            <span>Reçus <strong style="color:#1d4ed8;">{{ $inCount ?? 0 }}</strong></span>
        </div>
        <div class="sbar-item">
            <div class="sbar-dot" style="background:#6366f1;"></div>
            <span>Émis <strong style="color:#4338ca;">{{ $outCount ?? 0 }}</strong></span>
        </div>
        @if(($pendingAuth ?? 0) > 0)
        <div class="sbar-item ms-auto">
            <i class="fas fa-exclamation-circle" style="color:#f59e0b;font-size:.8rem;"></i>
            <span style="color:#92400e;font-weight:600;">{{ $pendingAuth }} en attente de traitement</span>
        </div>
        @endif
    </div>

    {{-- ── FILTERS ── --}}
    <div class="filt-card">
        <div class="filt-title"><i class="fas fa-sliders-h" style="color:#94a3b8;"></i> Filtres avancés</div>
        <form method="GET" action="{{ route('swift-operator.dashboard') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label" style="font-size:.72rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Direction</label>
                <select name="direction" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    <option value="RECU" @selected(request('direction')=='RECU')>Reçus</option>
                    <option value="EMIS" @selected(request('direction')=='EMIS')>Émis</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" style="font-size:.72rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Statut</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="authorized" @selected(request('status')=='authorized')>✓ Autorisé</option>
                    <option value="rejected"   @selected(request('status')=='rejected')>✗ Rejeté</option>
                    <option value="pending"    @selected(request('status')=='pending')>⏳ En attente</option>
                    <option value="processed"  @selected(request('status')=='processed')>Traité</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" style="font-size:.72rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Devise</label>
                <select name="currency" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    <option value="USD" @selected(request('currency')=='USD')>USD</option>
                    <option value="EUR" @selected(request('currency')=='EUR')>EUR</option>
                    <option value="GBP" @selected(request('currency')=='GBP')>GBP</option>
                    <option value="TND" @selected(request('currency')=='TND')>TND</option>
                    <option value="CNY" @selected(request('currency')=='CNY')>CNY</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" style="font-size:.72rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Date début</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" style="font-size:.72rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Date fin</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill" style="font-size:.73rem;">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
                <a href="{{ route('swift-operator.dashboard') }}" class="btn btn-sm btn-light flex-fill" style="font-size:.73rem;border:1px solid #e2e8f0;">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- ── TABLE ── --}}
    <div class="tbl-card">
        <div class="tbl-card-hd">
            <div class="tbl-card-title">
                <i class="fas fa-list" style="color:#94a3b8;"></i> Messages SWIFT
            </div>
            <div class="d-flex align-items-center gap-2">
                @if(request()->hasAny(['direction','status','currency','date_from','date_to']))
                    <span class="badge" style="background:#eff6ff;color:#1d4ed8;font-size:.68rem;">Filtre actif</span>
                @endif
                <span style="font-size:.72rem;color:#94a3b8;">{{ $messages->total() ?? 0 }} résultat(s)</span>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="swift-tbl">
                <thead>
                    <tr>
                        <th style="width:100px;">Date</th>
                        <th style="width:60px;">Dir.</th>
                        <th style="width:75px;">Type</th>
                        <th style="width:170px;">Référence</th>
                        <th style="width:180px;">Émetteur</th>
                        <th style="width:180px;">Bénéficiaire</th>
                        <th style="width:130px;text-align:right;">Montant</th>
                        <th style="width:110px;">Statut</th>
                        <th style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $msg)
                    @php
                        $type   = strtoupper($msg->type_message ?? '');
                        $dir    = strtoupper($msg->direction ?? '');
                        $status = strtolower($msg->status ?? '');
                        $typeBg = match(true) {
                            str_starts_with($type, 'PACS') => ['#dbeafe','#1d4ed8'],
                            str_starts_with($type, 'CAMT') => ['#ede9fe','#7c3aed'],
                            str_starts_with($type, 'MT')   => ['#e0f2fe','#0369a1'],
                            default                        => ['#f3f4f6','#374151'],
                        };
                    @endphp
                    <tr>
                        <td>
                            <div style="font-size:.76rem;font-weight:600;">{{ $msg->created_at?->format('d/m/Y') ?? '—' }}</div>
                            <div style="font-size:.65rem;color:#94a3b8;">{{ $msg->created_at?->format('H:i') ?? '' }}</div>
                        </td>
                        <td>
                            @if(in_array($dir, ['IN','RECU']))
                                <span class="dir-tag dir-in">Reçu</span>
                            @else
                                <span class="dir-tag dir-out">Émis</span>
                            @endif
                        </td>
                        <td>
                            <span style="background:{{ $typeBg[0] }};color:{{ $typeBg[1] }};font-size:.64rem;font-weight:700;padding:.18rem .42rem;border-radius:4px;font-family:monospace;">
                                {{ $type ?: '—' }}
                            </span>
                        </td>
                        <td style="font-family:monospace;font-size:.72rem;color:#334155!important;">
                            {{ Str::limit($msg->reference ?? '—', 22) }}
                        </td>
                        <td>
                            <div style="font-weight:600;font-size:.78rem;">{{ Str::limit($msg->sender_name ?? $msg->sender_bic ?? 'N/A', 22) }}</div>
                            @if($msg->sender_bic)
                            <div style="font-size:.62rem;color:#94a3b8;font-family:monospace;">{{ $msg->sender_bic }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:.78rem;">{{ Str::limit($msg->receiver_name ?? $msg->receiver_bic ?? 'N/A', 22) }}</div>
                            @if($msg->receiver_bic)
                            <div style="font-size:.62rem;color:#94a3b8;font-family:monospace;">{{ $msg->receiver_bic }}</div>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div style="font-weight:700;font-size:.82rem;">{{ number_format((float)($msg->amount ?? 0), 2, ',', ' ') }}</div>
                            <div style="font-size:.64rem;color:#94a3b8;">{{ $msg->currency ?? '' }}</div>
                        </td>
                        <td>
                            @switch($status)
                                @case('authorized')
                                    <span class="sbadge sb-authorized"><i class="fas fa-check-circle" style="font-size:.6rem;"></i> Autorisé</span>
                                    @break
                                @case('rejected')
                                    <span class="sbadge sb-rejected"><i class="fas fa-times-circle" style="font-size:.6rem;"></i> Rejeté</span>
                                    @break
                                @case('pending')
                                    <span class="sbadge sb-pending"><i class="fas fa-clock" style="font-size:.6rem;"></i> En attente</span>
                                    @break
                                @case('processed')
                                    <span class="sbadge sb-processed"><i class="fas fa-check-double" style="font-size:.6rem;"></i> Traité</span>
                                    @break
                                @case('suspended')
                                    <span class="sbadge sb-suspended"><i class="fas fa-pause-circle" style="font-size:.6rem;"></i> Suspendu</span>
                                    @break
                                @default
                                    <span class="sbadge sb-pending">{{ $msg->status ?? '—' }}</span>
                            @endswitch
                        </td>
                        <td>
                            <div class="tbl-actions">
                                <a href="{{ route('swift.show', $msg->id) }}" class="ta-btn ta-view" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="ta-btn ta-mt open-raw-file"
                                        data-url="{{ route('swift.view-mt', $msg->id) }}" data-title="MT" title="MT">
                                    <i class="fas fa-file-alt"></i>
                                </button>
                                <a href="{{ route('swift.view-mx', $msg->id) }}" target="_blank" class="ta-btn ta-mx" title="MX/XML">
                                    <i class="fas fa-code"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p class="fw-semibold mb-0">Aucun message trouvé</p>
                                <p class="small text-muted mt-1">Modifiez les filtres ou importez de nouveaux messages</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
        <div class="tbl-footer">
            <div class="tbl-footer-info">
                Affichage {{ $messages->firstItem() }}–{{ $messages->lastItem() }} sur {{ $messages->total() }}
            </div>
            {{ $messages->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
