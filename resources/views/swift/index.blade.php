@extends('layouts.app')

@section('title', 'Messages SWIFT - BTL Bank')

@push('styles')
<style>
/* ════════════════════════════════════════════
   VARIABLES COULEURS
   ════════════════════════════════════════════ */
:root {
    --c-teal:        #0f766e;
    --c-teal-dark:   #134e4a;
    --c-teal-light:  #f0fdfa;
    --c-green:       #10b981;
    --c-amber:       #d97706;
    --c-amber-dark:  #92400e;
    --c-navy:        #0f172a;
    --c-slate:       #1e293b;
    --c-danger:      #ef4444;
    --c-shadow:      rgba(0,0,0,.07);
}

/* ════════════════════════════════════════════
   PAGE HEADER
   ════════════════════════════════════════════ */
.swift-page-header {
    background: linear-gradient(135deg, var(--c-navy) 0%, #1e3a5f 55%, #1a4a3a 100%);
    border-radius: 14px;
    padding: 1.4rem 1.8rem;
    margin-bottom: 1.5rem;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}
.swift-page-header h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .6rem;
}
.swift-page-header p {
    margin: .25rem 0 0;
    font-size: .78rem;
    opacity: .6;
}

/* ════════════════════════════════════════════
   BOUTONS D'ACTION HEADER
   ════════════════════════════════════════════ */
.btn-swift-dashboard {
    background: rgba(255,255,255,.13);
    border: 1px solid rgba(255,255,255,.25);
    color: #fff;
    border-radius: 8px;
    padding: .42rem 1rem;
    font-size: .8rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: background .2s;
}
.btn-swift-dashboard:hover { background: rgba(255,255,255,.25); color: #fff; }

.btn-swift-new {
    background: linear-gradient(135deg, var(--c-teal-dark), var(--c-teal));
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: .42rem 1rem;
    font-size: .8rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: opacity .2s;
}
.btn-swift-new:hover { opacity: .88; color: #fff; }

.btn-swift-import {
    background: linear-gradient(135deg, var(--c-amber-dark), var(--c-amber));
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: .42rem 1rem;
    font-size: .8rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: opacity .2s;
}
.btn-swift-import:hover { opacity: .88; color: #fff; }

.btn-swift-export {
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.2);
    color: #fff;
    border-radius: 8px;
    padding: .42rem 1rem;
    font-size: .8rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: background .2s;
}
.btn-swift-export:hover { background: rgba(255,255,255,.22); color: #fff; }

/* ════════════════════════════════════════════
   ALERTES
   ════════════════════════════════════════════ */
.swift-alert-ok {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-left: 4px solid #22c55e;
    border-radius: 10px;
    padding: .8rem 1.1rem;
    margin-bottom: 1rem;
    font-size: .83rem;
    color: #166534;
    display: flex;
    align-items: center;
    gap: .6rem;
}
.swift-alert-err {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-left: 4px solid var(--c-danger);
    border-radius: 10px;
    padding: .8rem 1.1rem;
    margin-bottom: 1rem;
    font-size: .83rem;
    color: #b91c1c;
    display: flex;
    align-items: center;
    gap: .6rem;
}

/* ════════════════════════════════════════════
   TABLEAU
   ════════════════════════════════════════════ */
.swift-table-wrap {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 14px var(--c-shadow);
    overflow: hidden;
}
.swift-table-wrap .table {
    margin: 0;
    font-size: .82rem;
}
.swift-table-wrap .table thead th {
    background: #f8fafc;
    color: #64748b;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    border-bottom: 1px solid #e2e8f0;
    border-top: none;
    padding: .75rem 1rem;
    white-space: nowrap;
}
.swift-table-wrap .table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background .15s;
}
.swift-table-wrap .table tbody tr:last-child { border-bottom: none; }
.swift-table-wrap .table tbody tr:hover { background: #fafcff; }
.swift-table-wrap .table td {
    padding: .65rem 1rem;
    vertical-align: middle;
    color: #334155;
    border: none;
}

/* ── Badges type message ── */
.type-badge {
    display: inline-block;
    padding: .22rem .55rem;
    border-radius: 6px;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
}
.type-badge.mt103  { background: #dcfce7; color: #166534; }
.type-badge.mt202  { background: #dbeafe; color: #1e40af; }
.type-badge.mt900  { background: #fef3c7; color: #92400e; }
.type-badge.pacs   { background: #ede9fe; color: #5b21b6; }
.type-badge.error  { background: #fee2e2; color: #991b1b; }
.type-badge.other  { background: #f1f5f9; color: #475569; }

/* ── Montant ── */
.amount-cell {
    font-weight: 700;
    color: var(--c-slate);
    text-align: right;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}

/* ── Référence tronquée ── */
.ref-cell {
    font-family: 'Courier New', monospace;
    font-size: .75rem;
    color: #475569;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ── Boutons actions lignes ── */
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .28rem .65rem;
    border-radius: 6px;
    font-size: .72rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    border: 1.5px solid transparent;
    transition: background .15s, color .15s;
    white-space: nowrap;
}
.action-btn.view {
    color: var(--c-teal);
    border-color: #99f6e4;
    background: var(--c-teal-light);
}
.action-btn.view:hover { background: var(--c-teal); color: #fff; }

.action-btn.mt {
    color: #059669;
    border-color: #a7f3d0;
    background: #f0fdf4;
}
.action-btn.mt:hover { background: #059669; color: #fff; }

.action-btn.mx {
    color: #475569;
    border-color: #e2e8f0;
    background: #f8fafc;
}
.action-btn.mx:hover { background: #475569; color: #fff; }

.action-btn.del {
    color: var(--c-danger);
    border-color: #fecaca;
    background: #fef2f2;
}
.action-btn.del:hover { background: var(--c-danger); color: #fff; }

/* ── Empty state ── */
.empty-state {
    padding: 3rem 1rem;
    text-align: center;
    color: #94a3b8;
}
.empty-state i { font-size: 2.5rem; margin-bottom: .8rem; display: block; }
.empty-state p { font-size: .85rem; margin: 0; }

/* ════════════════════════════════════════════
   PAGINATION
   ════════════════════════════════════════════ */
.pagination-wrap {
    padding: .9rem 1rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: center;
}
.pagination-wrap .pagination .page-link {
    border-radius: 7px;
    font-size: .78rem;
    color: var(--c-teal);
    border-color: #e2e8f0;
    margin: 0 2px;
}
.pagination-wrap .pagination .page-item.active .page-link {
    background: var(--c-teal);
    border-color: var(--c-teal);
    color: #fff;
}

/* ════════════════════════════════════════════
   MODAL SUPPRESSION
   ════════════════════════════════════════════ */
.modal-delete .modal-content {
    border: none;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,.15);
}
.modal-delete .modal-header {
    background: #fef2f2;
    border-bottom: 1px solid #fecaca;
    padding: 1rem 1.4rem;
}
.modal-delete .modal-header .modal-title {
    font-size: .95rem;
    font-weight: 700;
    color: #991b1b;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.modal-delete .modal-body {
    padding: 1.8rem 1.4rem;
    text-align: center;
}
.modal-delete .modal-body .del-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #fef2f2;
    border: 2px solid #fecaca;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto .9rem;
    font-size: 1.4rem;
    color: var(--c-danger);
}
.modal-delete .modal-body .ref-tag {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    padding: .3rem .8rem;
    font-size: .78rem;
    font-family: monospace;
    color: #475569;
    display: inline-block;
    margin-bottom: .6rem;
}
.modal-delete .modal-footer {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: .8rem 1.4rem;
    display: flex;
    justify-content: flex-end;
    gap: .6rem;
}
.btn-modal-cancel {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #475569;
    border-radius: 8px;
    padding: .42rem 1.1rem;
    font-size: .82rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.btn-modal-cancel:hover { background: #e2e8f0; }
.btn-modal-delete {
    background: var(--c-danger);
    border: none;
    color: #fff;
    border-radius: 8px;
    padding: .42rem 1.1rem;
    font-size: .82rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .15s;
}
.btn-modal-delete:hover { opacity: .88; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- ═══════════════════════════════════════
         HEADER
         ═══════════════════════════════════════ --}}
    <div class="swift-page-header">
        <div>
            <h1>
                <i class="fas fa-exchange-alt" style="color:#6ee7b7;"></i>
                Gestion des Messages SWIFT
            </h1>
            <p>Administration complète des messages SWIFT — Tunisian Libyan Bank</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('dashboard') }}" class="btn-swift-dashboard">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>

            @can('create', App\Models\MessageSwift::class)
                <a href="{{ route('swift.create') }}" class="btn-swift-new">
                    <i class="fas fa-plus"></i>Nouveau message
                </a>
            @endcan

            @can('import', App\Models\MessageSwift::class)
                <a href="{{ route('swift.import.form') }}" class="btn-swift-import">
                    <i class="fas fa-file-import"></i>Importer
                </a>
            @endcan

            @can('export', App\Models\MessageSwift::class)
                <a href="{{ route('swift.export') }}" class="btn-swift-export">
                    <i class="fas fa-download"></i>Exporter
                </a>
            @endcan
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         ALERTES
         ═══════════════════════════════════════ --}}
    @if(session('success'))
        <div class="swift-alert-ok">
            <i class="fas fa-check-circle fa-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="swift-alert-err">
            <i class="fas fa-exclamation-circle fa-lg"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ═══════════════════════════════════════
         TABLEAU
         ═══════════════════════════════════════ --}}
    <div class="swift-table-wrap">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:13%">Date</th>
                        <th style="width:9%">Type</th>
                        <th style="width:20%">Émetteur</th>
                        <th style="width:22%">Référence</th>
                        <th style="width:12%;text-align:right">Montant</th>
                        <th style="width:5%">Dev.</th>
                        <th style="width:19%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $msg)
                        @php
                            $type = strtolower($msg->type_message ?? '');
                            $badgeClass = match(true) {
                                str_starts_with($type,'mt103') => 'mt103',
                                str_starts_with($type,'mt202') => 'mt202',
                                str_starts_with($type,'mt900') => 'mt900',
                                str_starts_with($type,'pacs')  => 'pacs',
                                $type === 'error'               => 'error',
                                default                         => 'other',
                            };
                        @endphp
                        <tr>
                            <td style="font-size:.78rem;color:#64748b;white-space:nowrap;">
                                {{ $msg->value_date
                                    ? $msg->value_date->format('Y-m-d H:i')
                                    : ($msg->created_at ? $msg->created_at->format('Y-m-d H:i') : '—') }}
                            </td>

                            <td>
                                <span class="type-badge {{ $badgeClass }}">
                                    {{ strtoupper($msg->type_message ?? '?') }}
                                </span>
                            </td>

                            <td style="font-size:.8rem;color:#334155;">
                                {{ Str::limit($msg->sender_name ?? $msg->sender_bic ?? 'N/A', 28) }}
                            </td>

                            <td>
                                <span class="ref-cell" title="{{ $msg->reference ?? '' }}">
                                    {{ $msg->reference ?? 'N/A' }}
                                </span>
                            </td>

                            <td class="amount-cell">
                                {{ number_format((float)($msg->amount ?? 0), 2, '.', ',') }}
                            </td>

                            <td style="font-size:.78rem;font-weight:600;color:#475569;text-transform:uppercase;">
                                {{ $msg->currency ?? '—' }}
                            </td>

                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="{{ route('swift.show', $msg->id) }}"
                                       class="action-btn view" title="Voir le détail">
                                        <i class="fas fa-eye"></i>View
                                    </a>

                                    <button type="button"
                                            class="action-btn mt open-raw-file"
                                            data-url="{{ route('swift.view-mt', $msg->id) }}"
                                            data-title="MT"
                                            title="Format MT">
                                        <i class="fas fa-file-alt"></i>MT
                                    </button>

                                    <a href="{{ route('swift.view-mx', $msg->id) }}"
                                       target="_blank"
                                       class="action-btn mx" title="Format MX">
                                        <i class="fas fa-code"></i>MX
                                    </a>

                                    @can('delete', $msg)
                                    <button type="button"
                                            class="action-btn del"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $msg->id }}"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Aucun message SWIFT trouvé</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrap">
            {{ $messages->appends(request()->query())->links() }}
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════
     MODALS SUPPRESSION
     ═══════════════════════════════════════ --}}
@foreach($messages as $msg)
    <div class="modal fade modal-delete" id="deleteModal{{ $msg->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i>Confirmer la suppression
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="del-icon"><i class="fas fa-trash"></i></div>
                    <div class="ref-tag">{{ Str::limit($msg->reference ?? 'N/A', 30) }}</div>
                    <p style="font-size:.83rem;color:#64748b;margin:0;">
                        Cette action est <strong>irréversible</strong>. Le message sera définitivement supprimé.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <form action="{{ route('swift.destroy', $msg->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-modal-delete">
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-fermeture alertes après 5s
    setTimeout(() => {
        document.querySelectorAll('.swift-alert-ok, .swift-alert-err').forEach(el => {
            el.style.transition = 'opacity .5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 5000);
});
</script>
@endpush