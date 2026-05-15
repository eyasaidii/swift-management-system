{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BTL SWIFT Manager')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --green-900:  #052e16;
            --green-800:  #083d22;
            --green-700:  #0a4d2b;
            --green-600:  #0d6e3d;
            --green-500:  #16a34a;
            --green-400:  #22c55e;
            --green-300:  #4ade80;
            --green-100:  #dcfce7;
            --accent:     #34d399;
            --gold:       #f59e0b;
            --sidebar-w:  290px;
            --header-h:   64px;
            --text-primary:   rgba(255,255,255,0.95);
            --text-secondary: rgba(255,255,255,0.55);
            --text-muted:     rgba(255,255,255,0.30);
            --border:         rgba(255,255,255,0.07);
            --hover-bg:       rgba(255,255,255,0.06);
            --active-bg:      rgba(52,211,153,0.12);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #eef2ee; color: #111; }

        /* ══════════════════════════════════════════
           SIDEBAR
        ══════════════════════════════════════════ */
        .sidebar {
            position: fixed; inset: 0 auto 0 0; width: var(--sidebar-w);
            background: linear-gradient(180deg, var(--green-800) 0%, var(--green-900) 100%);
            display: flex; flex-direction: column; z-index: 1000;
            transition: width .3s ease;
            box-shadow: 6px 0 32px rgba(0,0,0,.35); overflow: hidden;
        }
        .sb-brand {
            flex-shrink: 0; display: flex; align-items: center; gap: .9rem;
            padding: 1.1rem 1.25rem 1rem; border-bottom: 1px solid var(--border);
            background: rgba(0,0,0,.2);
        }
        .sb-brand-logo {
            width: 42px; height: 42px; background: white; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; overflow: hidden; padding: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,.3);
        }
        .sb-brand-logo img { width: 100%; height: 100%; object-fit: contain; }
        .sb-brand-text .eyebrow {
            font-family: 'Sora', sans-serif; font-size: .58rem; font-weight: 600;
            letter-spacing: .2em; text-transform: uppercase; color: var(--accent);
            display: block; margin-bottom: .1rem;
        }
        .sb-brand-text .name {
            font-family: 'Sora', sans-serif; font-size: .92rem; font-weight: 700;
            color: var(--text-primary); line-height: 1.2; letter-spacing: .01em;
        }
        .sb-user {
            flex-shrink: 0; display: flex; align-items: center; justify-content: space-between;
            padding: .65rem 1.25rem; border-bottom: 1px solid var(--border);
            background: rgba(0,0,0,.1);
        }
        .sb-role-badge {
            display: inline-flex; align-items: center; gap: .35rem;
            background: rgba(220,38,38,.8); color: #fff;
            font-family: 'Sora', sans-serif; font-size: .65rem; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase;
            padding: .28rem .75rem; border-radius: 20px;
        }
        .sb-username {
            font-size: .73rem; font-weight: 500; color: var(--text-secondary);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 110px;
        }
        .sb-body {
            flex: 1; overflow-y: auto; overflow-x: hidden; padding: .6rem 0 1.5rem;
            scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.1) transparent;
        }
        .sb-body::-webkit-scrollbar { width: 3px; }
        .sb-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 3px; }

        .sec-btn {
            display: flex; align-items: center; justify-content: space-between;
            width: calc(100% - 1.6rem); margin: .5rem .8rem 0; padding: .65rem 1rem;
            border-radius: 10px; cursor: pointer; text-decoration: none;
            border: 1px solid rgba(255,255,255,.08); background: rgba(255,255,255,.04);
            transition: background .15s, border-color .15s;
        }
        .sec-btn:hover { background: rgba(255,255,255,.09); border-color: rgba(255,255,255,.14); }
        .sec-btn[aria-expanded="true"] { background: rgba(52,211,153,.1); border-color: rgba(52,211,153,.25); }
        .sec-btn .sec-left { display: flex; align-items: center; gap: .65rem; }
        .sec-btn .sec-icon {
            width: 30px; height: 30px; background: rgba(255,255,255,.08); border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem; color: var(--accent); flex-shrink: 0; transition: background .15s;
        }
        .sec-btn[aria-expanded="true"] .sec-icon { background: rgba(52,211,153,.2); }
        .sec-btn .sec-label {
            font-family: 'Sora', sans-serif; font-size: .75rem; font-weight: 700;
            letter-spacing: .08em; text-transform: uppercase; color: var(--text-primary);
        }
        .sec-btn .sec-right { display: flex; align-items: center; gap: .4rem; }
        .sec-btn .sec-count { font-size: .68rem; font-weight: 700; padding: .15rem .55rem; border-radius: 20px; line-height: 1.4; }
        .sec-btn .sec-chevron { color: var(--text-muted); font-size: .65rem; transition: transform .25s ease; }
        .sec-btn[aria-expanded="true"] .sec-chevron { transform: rotate(180deg); }
        .sec-btn.is-disabled { opacity: .3; cursor: not-allowed; pointer-events: none; }

        .cat-btn {
            display: flex; align-items: center; justify-content: space-between;
            width: calc(100% - 2rem); margin: .2rem 1rem 0; padding: .5rem .85rem;
            border-radius: 8px; cursor: pointer; text-decoration: none; border: none;
            background: rgba(255,255,255,.03); border-left: 2px solid rgba(255,255,255,.06);
            transition: background .12s, border-color .12s;
        }
        .cat-btn:hover { background: rgba(255,255,255,.07); border-left-color: rgba(255,255,255,.18); }
        .cat-btn[aria-expanded="true"] { background: rgba(255,255,255,.06); border-left-color: var(--accent); }
        .cat-btn .cat-left { display: flex; align-items: center; gap: .5rem; min-width: 0; }
        .cat-btn .cat-icon { font-size: .62rem; color: var(--text-muted); flex-shrink: 0; transition: color .15s; }
        .cat-btn[aria-expanded="true"] .cat-icon { color: var(--accent); }
        .cat-btn .cat-label {
            font-family: 'Sora', sans-serif; font-size: .72rem; font-weight: 600;
            letter-spacing: .04em; text-transform: uppercase; color: rgba(255,255,255,.65);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color .15s;
        }
        .cat-btn[aria-expanded="true"] .cat-label { color: var(--text-primary); }
        .cat-btn .cat-right { display: flex; align-items: center; gap: .3rem; flex-shrink: 0; }
        .cat-btn .cat-count { font-size: .62rem; font-weight: 700; padding: .1rem .42rem; border-radius: 20px; line-height: 1.4; }
        .cat-btn .cat-chevron { color: var(--text-muted); font-size: .55rem; transition: transform .2s ease, color .15s; }
        .cat-btn[aria-expanded="true"] .cat-chevron { transform: rotate(90deg); color: var(--accent); }

        .type-wrap { padding: .1rem 1rem 0 2.3rem; }
        .type-link {
            display: flex; align-items: center; justify-content: space-between;
            padding: .4rem .75rem; border-radius: 7px; text-decoration: none; gap: .5rem;
            transition: background .1s ease; border-left: 2px solid transparent; margin-bottom: .1rem;
        }
        .type-link:hover { background: var(--hover-bg); border-left-color: rgba(255,255,255,.15); }
        .type-link.active { background: var(--active-bg); border-left-color: var(--accent); }
        .type-link .type-left { display: flex; align-items: center; gap: .5rem; flex: 1; min-width: 0; }
        .type-link .type-dot {
            width: 5px; height: 5px; border-radius: 50%; background: rgba(255,255,255,.2);
            flex-shrink: 0; transition: background .15s;
        }
        .type-link:hover .type-dot  { background: rgba(255,255,255,.4); }
        .type-link.active .type-dot { background: var(--accent); box-shadow: 0 0 6px var(--accent); }
        .type-link .type-name {
            font-size: .8rem; font-weight: 500; color: rgba(255,255,255,.62);
            white-space: normal; word-break: break-word; line-height: 1.35; transition: color .15s;
        }
        .type-link:hover .type-name  { color: rgba(255,255,255,.88); }
        .type-link.active .type-name { color: var(--text-primary); font-weight: 600; }
        .type-link .type-badge {
            font-size: .63rem; font-weight: 700; padding: .1rem .42rem; border-radius: 20px;
            background: rgba(255,255,255,.08); color: rgba(255,255,255,.45); flex-shrink: 0;
            min-width: 22px; text-align: center; transition: background .15s, color .15s;
        }
        .type-link.active .type-badge { background: rgba(52,211,153,.2); color: var(--accent); }

        .sb-divider { height: 1px; background: var(--border); margin: .8rem 1.25rem; }
        .sb-section-label {
            font-family: 'Sora', sans-serif; font-size: .58rem; font-weight: 700;
            letter-spacing: .18em; text-transform: uppercase; color: var(--text-muted);
            padding: .3rem 1.3rem .15rem; display: block;
        }
        .util-link {
            display: flex; align-items: center; gap: .6rem; padding: .48rem 1rem;
            margin: .08rem .8rem 0; border-radius: 8px; text-decoration: none;
            color: rgba(255,255,255,.6); font-size: .8rem; font-weight: 500;
            transition: background .1s, color .1s; border-left: 2px solid transparent;
        }
        .util-link:hover  { background: var(--hover-bg); color: var(--text-primary); border-left-color: rgba(255,255,255,.2); }
        .util-link.active { background: var(--active-bg); color: var(--text-primary); border-left-color: var(--accent); }
        .util-link .util-icon {
            width: 26px; height: 26px; background: rgba(255,255,255,.06); border-radius: 6px;
            display: flex; align-items: center; justify-content: center; font-size: .75rem;
            flex-shrink: 0; color: rgba(255,255,255,.45); transition: background .1s, color .1s;
        }
        .util-link:hover .util-icon  { background: rgba(255,255,255,.1); color: var(--text-primary); }
        .util-link.active .util-icon { background: rgba(52,211,153,.15); color: var(--accent); }

        .sb-footer {
            flex-shrink: 0; padding: .75rem 1.25rem; border-top: 1px solid var(--border);
            background: rgba(0,0,0,.15);
        }
        .logout-btn {
            display: flex; align-items: center; gap: .55rem; background: none; border: none;
            cursor: pointer; color: rgba(248,113,113,.75); font-size: .8rem; font-weight: 500;
            padding: .4rem .6rem; border-radius: 7px; width: 100%; transition: background .12s, color .12s;
        }
        .logout-btn:hover { background: rgba(220,38,38,.12); color: #fca5a5; }
        .logout-btn i { font-size: .82rem; }

        /* ══════════════════════════════════════════
           TOP NAVBAR — fond blanc
        ══════════════════════════════════════════ */
        .top-navbar {
            position: sticky; top: 0; z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            height: var(--header-h);
            display: flex; align-items: center; padding: 0 2rem;
            box-shadow: 0 1px 8px rgba(0,0,0,.06);
        }

        /* Point vert animé En ligne */
        .online-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #22c55e; flex-shrink: 0;
            position: relative; display: inline-block;
        }
        .online-dot::after {
            content: ''; position: absolute; inset: -3px; border-radius: 50%;
            background: #22c55e; opacity: .35;
            animation: onlinePulse 1.8s ease-in-out infinite;
        }
        @keyframes onlinePulse {
            0%, 100% { transform: scale(1); opacity: .35; }
            50%       { transform: scale(1.9); opacity: 0; }
        }

        /* ══════════════════════════════════════════
           MAIN WRAPPER
        ══════════════════════════════════════════ */
        .main-wrapper {
            margin-left: var(--sidebar-w); min-height: 100vh;
            display: flex; flex-direction: column; transition: margin-left .3s ease;
        }
        .main-content { flex: 1; padding: 1.75rem 2rem; }
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04); }

        /* Tableaux SWIFT : colonnes compactes */
        .table-responsive .table { table-layout: fixed; width: 100%; }
        .table-responsive .table th,
        .table-responsive .table td { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: middle; padding: .5rem .6rem; font-size: .85rem; }
        .table-responsive .table td.wrap-cell { white-space: normal; }

        /* Fix : Tailwind .collapse { visibility: collapse } écrase Bootstrap */
        .collapse.show { visibility: visible !important; }
        .collapsing { visibility: visible !important; }

        @media (max-width: 992px) {
            .sidebar { width: 0; overflow: hidden; }
            .sidebar.show { width: var(--sidebar-w); }
            .main-wrapper { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

<?php
    $user        = auth()->user();
    $primaryRole = $user ? $user->getRoleNames()->first() : null;
    $canViewReceived = in_array($primaryRole, ['super-admin','swift-manager','swift-operator','backoffice','monetique']);
    $canViewEmitted  = in_array($primaryRole, ['super-admin','swift-manager','swift-operator','chargee','chef-agence']);
    $canViewAdmin    = in_array($primaryRole, ['super-admin','swift-manager']);
    $receivedCategories = $receivedCategories ?? [];
    $emittedCategories  = $emittedCategories  ?? [];
    $receivedTotal      = $receivedTotal      ?? 0;
    $emittedTotal       = $emittedTotal       ?? 0;
    $initials = $user ? strtoupper(substr($user->name, 0, 1)) : 'U';
?>

{{-- ══════════════════════════════════════════
     SIDEBAR
══════════════════════════════════════════ --}}
<nav class="sidebar" id="sidebar">

    <div class="sb-brand">
        <div class="sb-brand-logo">
            <img src="{{ asset('images/logo-btl.png') }}" alt="BTL"
                 onerror="this.onerror=null;this.style.display='none';this.parentElement.innerHTML='<span style=\'font-weight:900;color:#0a4d2b;font-size:.9rem\'>BTL</span>';">
        </div>
        <div class="sb-brand-text">
            <span class="eyebrow">Swift Manager</span>
            <span class="name">Tunisian Libyan Bank</span>
        </div>
    </div>

    <div class="sb-user">
        <span class="sb-role-badge">
            <i class="fas fa-crown" style="font-size:.55rem;"></i>
            {{ $primaryRole ?? 'Utilisateur' }}
        </span>
        <span class="sb-username">{{ $user->name ?? '' }}</span>
    </div>

    <div class="sb-body">

        {{-- ── RECEIVED ── --}}
        @if($canViewReceived)
            <a class="sec-btn" data-bs-toggle="collapse" href="#collapseRecu"
               aria-expanded="{{ request('direction') === 'RECU' ? 'true' : 'false' }}">
                <div class="sec-left">
                    <div class="sec-icon"><i class="fas fa-inbox"></i></div>
                    <span class="sec-label">Received Messages</span>
                </div>
                <div class="sec-right">
                    <span class="sec-count badge bg-success">{{ $receivedTotal }}</span>
                    <i class="fas fa-chevron-down sec-chevron"></i>
                </div>
            </a>
            <div class="collapse {{ request('direction') === 'RECU' ? 'show' : '' }}" id="collapseRecu">
                @forelse($receivedCategories as $cat)
                    @php $catActive = request('categorie') === $cat['category'] && request('direction') === 'RECU'; @endphp
                    <a class="cat-btn" data-bs-toggle="collapse"
                       href="#recu-{{ Str::slug($cat['category']) }}"
                       aria-expanded="{{ $catActive ? 'true' : 'false' }}">
                        <div class="cat-left">
                            <i class="fas fa-folder cat-icon"></i>
                            <span class="cat-label">{{ $cat['name'] }}</span>
                        </div>
                        <div class="cat-right">
                            <span class="cat-count badge bg-primary">{{ $cat['total'] }}</span>
                            <i class="fas fa-chevron-right cat-chevron"></i>
                        </div>
                    </a>
                    <div class="collapse {{ $catActive ? 'show' : '' }}" id="recu-{{ Str::slug($cat['category']) }}">
                        <div class="type-wrap">
                            @foreach($cat['types'] as $type)
                                @php
                                    $typeName  = $type['type']  ?? $type['filter'] ?? $type['name'] ?? '—';
                                    $typeCount = $type['count'] ?? 0;
                                    $isActive  = request('type_message') === $typeName && request('direction') === 'RECU';
                                @endphp
                                <a class="type-link {{ $isActive ? 'active' : '' }}"
                                   href="{{ route('swift.index') }}?direction=RECU&categorie={{ $cat['category'] }}&type_message={{ $typeName }}">
                                    <div class="type-left">
                                        <span class="type-dot"></span>
                                        <span class="type-name">{{ $typeName }}</span>
                                    </div>
                                    <span class="type-badge">{{ $typeCount }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p style="font-size:.75rem;color:var(--text-muted);padding:.5rem 1.5rem;">Aucune catégorie</p>
                @endforelse
            </div>
        @else
            <div class="sec-btn is-disabled">
                <div class="sec-left">
                    <div class="sec-icon"><i class="fas fa-inbox"></i></div>
                    <span class="sec-label">Received Messages</span>
                </div>
                <span class="sec-count badge bg-secondary">{{ $receivedTotal }}</span>
            </div>
        @endif

        <div class="sb-divider"></div>

        {{-- ── EMITTED ── --}}
        @if($canViewEmitted)
            <a class="sec-btn" data-bs-toggle="collapse" href="#collapseEmis"
               aria-expanded="{{ request('direction') === 'EMIS' ? 'true' : 'false' }}">
                <div class="sec-left">
                    <div class="sec-icon"><i class="fas fa-paper-plane"></i></div>
                    <span class="sec-label">Emitted Messages</span>
                </div>
                <div class="sec-right">
                    <span class="sec-count badge bg-warning text-dark">{{ $emittedTotal }}</span>
                    <i class="fas fa-chevron-down sec-chevron"></i>
                </div>
            </a>
            <div class="collapse {{ request('direction') === 'EMIS' ? 'show' : '' }}" id="collapseEmis">
                @forelse($emittedCategories as $cat)
                    @php $catActive = request('categorie') === $cat['category'] && request('direction') === 'EMIS'; @endphp
                    <a class="cat-btn" data-bs-toggle="collapse"
                       href="#emis-{{ Str::slug($cat['category']) }}"
                       aria-expanded="{{ $catActive ? 'true' : 'false' }}">
                        <div class="cat-left">
                            <i class="fas fa-folder cat-icon"></i>
                            <span class="cat-label">{{ $cat['name'] }}</span>
                        </div>
                        <div class="cat-right">
                            <span class="cat-count badge bg-success">{{ $cat['total'] }}</span>
                            <i class="fas fa-chevron-right cat-chevron"></i>
                        </div>
                    </a>
                    <div class="collapse {{ $catActive ? 'show' : '' }}" id="emis-{{ Str::slug($cat['category']) }}">
                        <div class="type-wrap">
                            @foreach($cat['types'] as $type)
                                @php
                                    $typeName  = $type['type']  ?? $type['filter'] ?? $type['name'] ?? '—';
                                    $typeCount = $type['count'] ?? 0;
                                    $isActive  = request('type_message') === $typeName && request('direction') === 'EMIS';
                                @endphp
                                <a class="type-link {{ $isActive ? 'active' : '' }}"
                                   href="{{ route('swift.index') }}?direction=EMIS&categorie={{ $cat['category'] }}&type_message={{ $typeName }}">
                                    <div class="type-left">
                                        <span class="type-dot"></span>
                                        <span class="type-name">{{ $typeName }}</span>
                                    </div>
                                    <span class="type-badge">{{ $typeCount }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p style="font-size:.75rem;color:var(--text-muted);padding:.5rem 1.5rem;">Aucune catégorie</p>
                @endforelse
            </div>
        @else
            <div class="sec-btn is-disabled">
                <div class="sec-left">
                    <div class="sec-icon"><i class="fas fa-paper-plane"></i></div>
                    <span class="sec-label">Emitted Messages</span>
                </div>
                <span class="sec-count badge bg-secondary">{{ $emittedTotal }}</span>
            </div>
        @endif

        {{-- ── OUTILS ── --}}
        @canany(['import', 'export'], App\Models\MessageSwift::class)
            <div class="sb-divider"></div>
            <span class="sb-section-label">Outils</span>
        @endcanany

        @can('import', App\Models\MessageSwift::class)
            <a class="util-link {{ request()->is('swift/importer') ? 'active' : '' }}"
               href="{{ route('swift.import.form') }}">
                <span class="util-icon"><i class="fas fa-upload"></i></span>
                Import SWIFT
            </a>
        @endcan

        @can('export', App\Models\MessageSwift::class)
            <a class="util-link {{ request()->is('swift/export-center') ? 'active' : '' }}"
               href="{{ route('swift.export-center') }}">
                <span class="util-icon"><i class="fas fa-download"></i></span>
                Export Center
            </a>
        @endcan

        {{-- ── INTELLIGENCE ARTIFICIELLE ── --}}
        @hasanyrole('swift-manager|super-admin')
            <div class="sb-divider"></div>
            <span class="sb-section-label">Intelligence Artificielle</span>
            <a class="util-link {{ request()->is('international-admin/ia-analytics') ? 'active' : '' }}"
               href="{{ route('international-admin.ia-analytics') }}">
                <span class="util-icon"><i class="fas fa-chart-bar"></i></span>
                Graphiques IA
            </a>
            <a class="util-link {{ request()->is('swift/anomalies*') ? 'active' : '' }}"
               href="{{ route('swift.anomalies.index') }}">
                <span class="util-icon"><i class="fas fa-brain"></i></span>
                Anomalies IA
            </a>
        @endhasanyrole

        {{-- ── ADMINISTRATION ── --}}
        @if($canViewAdmin)
            <div class="sb-divider"></div>
            <span class="sb-section-label">Administration</span>
            <a class="util-link {{ request()->is('admin/users*') ? 'active' : '' }}"
               href="{{ route('admin.users.index') }}">
                <span class="util-icon"><i class="fas fa-users"></i></span>
                Users
            </a>
            <a class="util-link" href="#">
                <span class="util-icon"><i class="fas fa-user-shield"></i></span>
                Roles
            </a>
            <a class="util-link" href="#">
                <span class="util-icon"><i class="fas fa-key"></i></span>
                Permissions
            </a>
        @endif

    </div>{{-- /sb-body --}}

    <div class="sb-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-arrow-right-from-bracket"></i>
                Déconnexion
            </button>
        </form>
    </div>

</nav>

{{-- ══════════════════════════════════════════
     MAIN WRAPPER
══════════════════════════════════════════ --}}
<div class="main-wrapper">

    {{-- ── TOP NAVBAR (fond blanc) ── --}}
    <header class="top-navbar">

        {{-- Burger mobile --}}
        <button class="btn btn-sm me-3 d-lg-none"
                style="background:none;border:1px solid #e5e7eb;color:#374151;"
                onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="fas fa-bars"></i>
        </button>

        {{-- Gauche : globe + titre page + date/heure temps réel --}}
        <div style="display:flex;align-items:center;gap:12px;flex:1;">
            <div style="width:40px;height:40px;background:#f0fdf4;border-radius:50%;
                        display:flex;align-items:center;justify-content:center;
                        border:1.5px solid #bbf7d0;flex-shrink:0;">
                <i class="fas fa-globe" style="color:#16a34a;font-size:.92rem;"></i>
            </div>
            <div>
                <div style="font-family:'Sora',sans-serif;font-size:1rem;font-weight:700;
                            color:#111827;line-height:1.2;">
                    @yield('page-title', 'SWIFT Messages')
                </div>
                <div id="hdr-datetime" style="font-size:.71rem;color:#9ca3af;margin-top:2px;"></div>
            </div>
        </div>

        {{-- Droite : notifications bell + toggle dark/light + user info + avatar --}}
        <div style="display:flex;align-items:center;gap:14px;">

            @hasanyrole('swift-manager')
            {{-- ── Cloche notifications SWIFT ── --}}
            <div style="position:relative;" id="notif-bell-wrapper">
                <button id="notif-bell-btn"
                        style="width:38px;height:38px;background:#f9fafb;border:1px solid #e5e7eb;
                               border-radius:50%;display:flex;align-items:center;justify-content:center;
                               cursor:pointer;flex-shrink:0;transition:background .2s,border-color .2s;position:relative;"
                        title="Notifications SWIFT">
                    <i class="fas fa-bell" style="color:#6b7280;font-size:.78rem;"></i>
                    <span id="notif-badge"
                          style="display:none;position:absolute;top:-3px;right:-3px;
                                 background:#dc2626;color:#fff;font-size:.58rem;font-weight:800;
                                 min-width:17px;height:17px;border-radius:10px;
                                 align-items:center;justify-content:center;
                                 padding:0 4px;border:2px solid #fff;line-height:1;">0</span>
                </button>
                {{-- Dropdown --}}
                <div id="notif-dropdown"
                     style="display:none;position:absolute;top:46px;right:0;width:380px;
                            background:#fff;border-radius:12px;
                            box-shadow:0 8px 32px rgba(0,0,0,.18),0 2px 8px rgba(0,0,0,.09);
                            z-index:9998;border:1px solid #e5e7eb;overflow:hidden;">
                    <div style="padding:14px 16px 10px;border-bottom:1px solid #f3f4f6;
                                display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:.88rem;font-weight:700;color:#111827;">
                            <i class="fas fa-bell" style="color:#16a34a;margin-right:6px;font-size:.8rem;"></i>
                            Notifications SWIFT
                        </span>
                        <button id="notif-mark-all"
                                style="font-size:.72rem;color:#16a34a;background:none;border:none;
                                       cursor:pointer;font-weight:600;padding:4px 8px;border-radius:6px;">
                            Tout marquer lu
                        </button>
                    </div>
                    <div id="notif-list" style="max-height:360px;overflow-y:auto;"></div>
                    <div id="notif-empty"
                         style="display:none;padding:28px 16px;text-align:center;color:#9ca3af;font-size:.82rem;">
                        <i class="fas fa-check-circle" style="font-size:1.6rem;color:#bbf7d0;display:block;margin-bottom:8px;"></i>
                        Aucune nouvelle notification
                    </div>
                </div>
            </div>
            @endhasanyrole

            {{-- Bouton toggle dark / light --}}
            <button id="darkToggleBtn"
                    style="width:38px;height:38px;background:#f9fafb;border:1px solid #e5e7eb;
                           border-radius:50%;display:flex;align-items:center;justify-content:center;
                           cursor:pointer;flex-shrink:0;transition:background .2s,border-color .2s;"
                    title="Basculer mode sombre / clair">
                <i class="fas fa-moon" id="darkToggleIcon" style="color:#6b7280;font-size:.78rem;"></i>
            </button>

            {{-- Infos utilisateur --}}
            <div>
                {{-- Ligne 1 : Nom + badge rôle sur la MÊME ligne --}}
                <div style="display:flex;align-items:center;gap:8px;justify-content:flex-end;">
                    <span style="font-size:.84rem;font-weight:700;color:#111827;white-space:nowrap;">
                        {{ auth()->user()->name }}
                    </span>
                    <span style="background:#dc2626;color:#fff;font-size:.58rem;font-weight:800;
                                 letter-spacing:.07em;text-transform:uppercase;
                                 padding:2px 9px;border-radius:20px;white-space:nowrap;">
                        {{ $primaryRole ?? 'Utilisateur' }}
                    </span>
                </div>
                {{-- Ligne 2 : point animé + En ligne · Banque --}}
                <div style="display:flex;align-items:center;gap:6px;justify-content:flex-end;margin-top:4px;">
                    <span style="display:flex;align-items:center;gap:5px;font-size:.68rem;color:#6b7280;">
                        <span class="online-dot"></span>En ligne
                    </span>
                    <span style="color:#d1d5db;font-size:.7rem;">·</span>
                    <span style="font-size:.68rem;color:#9ca3af;">Tunisian Libyan Bank</span>
                </div>
            </div>

            {{-- Avatar avec petit point vert en bas à droite --}}
            <div style="position:relative;flex-shrink:0;">
                <div style="width:38px;height:38px;background:#0a4d2b;border:2.5px solid #86efac;
                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                            color:#fff;font-weight:700;font-size:.88rem;">
                    {{ $initials }}
                </div>
                <span style="position:absolute;bottom:0;right:0;width:10px;height:10px;
                             background:#22c55e;border-radius:50%;border:2px solid #fff;"></span>
            </div>

        </div>
    </header>

    <main class="main-content">
        @yield('content')
    </main>

</div>{{-- /main-wrapper --}}

{{-- ══════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Tooltips Bootstrap globaux --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el, { html: false, trigger: 'hover' });
        });
    });
</script>

{{-- Date/heure temps réel + toggle dark/light --}}
<script>
(function () {
    const days   = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    const months = ['janvier','février','mars','avril','mai','juin',
                    'juillet','août','septembre','octobre','novembre','décembre'];

    function tick() {
        const n   = new Date();
        const h   = String(n.getHours()).padStart(2, '0');
        const m   = String(n.getMinutes()).padStart(2, '0');
        const txt = days[n.getDay()] + ' ' + n.getDate() + ' ' + months[n.getMonth()] +
                    ' ' + n.getFullYear() + ' à ' + h + ':' + m;
        const el  = document.getElementById('hdr-datetime');
        if (el) el.textContent = txt;
    }
    tick();
    setInterval(tick, 30000);

    /* Toggle dark / light */
    var btn  = document.getElementById('darkToggleBtn');
    var icon = document.getElementById('darkToggleIcon');
    if (btn) {
        btn.addEventListener('click', function () {
            var dark = document.body.classList.toggle('dark-mode');
            if (dark) {
                icon.classList.remove('fa-moon'); icon.classList.add('fa-sun');
                btn.style.background  = '#1f2937';
                btn.style.borderColor = '#374151';
                icon.style.color      = '#facc15';
            } else {
                icon.classList.remove('fa-sun'); icon.classList.add('fa-moon');
                btn.style.background  = '#f9fafb';
                btn.style.borderColor = '#e5e7eb';
                icon.style.color      = '#6b7280';
            }
        });
    }
})();
</script>

{{-- Modal global viewer SWIFT (MT / MX) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('modalRawFile') == null) {
            var wrap = document.createElement('div');
            wrap.innerHTML = `
            <div class="modal fade" id="modalRawFile" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header border-bottom-0 pb-2">
                            <h5 class="modal-title fw-semibold" id="modalRawTitle">Swift-Mt</h5>
                            <div class="d-flex align-items-center gap-2 ms-auto">
                                <button type="button" class="btn btn-primary btn-sm px-3" id="modalRawPrint">Print</button>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                        </div>
                        <div class="modal-body pt-1">
                            <pre id="modalRawContent"
                                 style="font-family:monospace;font-size:13px;white-space:pre-wrap;word-break:break-all;background:#fff;border:none;margin:0;padding:4px 0;min-height:200px;"></pre>
                        </div>
                    </div>
                </div>
            </div>`;
            document.body.appendChild(wrap);
        }

        var rawModal = new bootstrap.Modal(document.getElementById('modalRawFile'));

        function openRawFile(url, title) {
            var contentEl = document.getElementById('modalRawContent');
            var titleEl   = document.getElementById('modalRawTitle');
            if (!contentEl || !titleEl) return;
            titleEl.textContent = 'SWIFT Message Detail';
            contentEl.textContent = 'Chargement…';
            rawModal.show();
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.text(); })
                .then(function(text) { contentEl.textContent = text; })
                .catch(function() { contentEl.textContent = 'Impossible de charger le contenu MT.'; });
        }

        document.addEventListener('click', function (e) {
            var el = e.target.closest && e.target.closest('.open-raw-file');
            if (!el) return;
            e.preventDefault();
            var url   = el.getAttribute('data-url');
            var title = el.getAttribute('data-title') || el.textContent.trim();
            if (url) openRawFile(url, title);
        });

        var printBtn = document.getElementById('modalRawPrint');
        if (printBtn) {
            printBtn.addEventListener('click', function () {
                var content = document.getElementById('modalRawContent');
                if (!content) return;
                var w = window.open('', '_blank');
                w.document.write('<!doctype html><html><head><meta charset="utf-8"><title>SWIFT Message Detail</title>' +
                    '<style>body{font-family:monospace;font-size:13px;padding:24px;white-space:pre-wrap;}</style></head><body>');
                w.document.write(content.textContent.replace(/</g,'&lt;').replace(/>/g,'&gt;'));
                w.document.write('</body></html>');
                w.document.close();
                w.focus();
                setTimeout(function(){ try{ w.print(); w.close(); }catch(err){ console.error(err); } }, 300);
            });
        }
    });
</script>

@stack('scripts')

@hasanyrole('swift-manager')
{{-- ══════════════════════════════════════════
     TOAST CONTAINER — Notifications WebSocket
     Visible uniquement par swift-manager
══════════════════════════════════════════ --}}

<div id="swift-toast-container"
     style="position:fixed;top:80px;right:24px;z-index:9999;
            display:flex;flex-direction:column;gap:10px;
            max-width:380px;pointer-events:none;">
</div>

<script>
(function () {

    /* ─── helpers direction ─── */
    function dirLabel(dir) { return dir === 'IN' ? 'Reçu' : 'Émis'; }
    function dirColor(dir) { return dir === 'IN' ? '#0d6e3d' : '#b45309'; }

    /* ─────────────────────────────────────────────
       TOAST  (temps réel)
    ───────────────────────────────────────────── */
    function showSwiftToast(data) {
        var container = document.getElementById('swift-toast-container');
        if (!container) return;

        var amountLine = '';
        if (data.amount && data.currency) {
            amountLine = '<div style="margin-top:4px;font-size:.78rem;color:#374151;">' +
                '<i class="fas fa-coins" style="color:#f59e0b;margin-right:4px;"></i>' +
                data.amount + ' ' + data.currency + '</div>';
        }
        var senderLine = '';
        if (data.sender) {
            senderLine = '<div style="font-size:.74rem;color:#6b7280;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' +
                '<i class="fas fa-building" style="margin-right:4px;"></i>' + data.sender + '</div>';
        }

        var toast = document.createElement('div');
        toast.style.cssText = [
            'background:#fff','border-radius:12px',
            'box-shadow:0 8px 32px rgba(0,0,0,.18),0 2px 8px rgba(0,0,0,.10)',
            'border-left:4px solid ' + dirColor(data.direction),
            'padding:14px 16px','display:flex','align-items:flex-start','gap:12px',
            'pointer-events:all','animation:swiftSlideIn .35s ease','min-width:300px',
        ].join(';');

        toast.innerHTML =
            '<div style="flex-shrink:0;width:36px;height:36px;border-radius:50%;' +
                'background:' + dirColor(data.direction) + ';' +
                'display:flex;align-items:center;justify-content:center;">' +
                '<i class="fas fa-envelope" style="color:#fff;font-size:.85rem;"></i>' +
            '</div>' +
            '<div style="flex:1;min-width:0;">' +
                '<div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">' +
                    '<span style="font-size:.72rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;' +
                        'color:' + dirColor(data.direction) + ';">Nouveau message SWIFT</span>' +
                    '<span style="font-size:.68rem;color:#9ca3af;white-space:nowrap;">' + (data.time || '') + '</span>' +
                '</div>' +
                '<div style="font-size:.84rem;font-weight:700;color:#111827;margin-top:3px;">' +
                    (data.type || 'SWIFT') + ' &nbsp;<span style="font-weight:400;color:#6b7280;font-size:.78rem;">' + (data.reference || '') + '</span>' +
                '</div>' +
                '<div style="margin-top:2px;">' +
                    '<span style="font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:20px;' +
                        'background:' + dirColor(data.direction) + '22;color:' + dirColor(data.direction) + ';">' +
                        dirLabel(data.direction) +
                    '</span>' +
                    ' <span style="font-size:.7rem;color:#6b7280;margin-left:4px;">• En attente d\'autorisation</span>' +
                '</div>' +
                senderLine + amountLine +
            '</div>' +
            '<button onclick="this.closest(\'[data-swift-toast]\').remove()" ' +
                'style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:.75rem;' +
                       'padding:2px;flex-shrink:0;line-height:1;align-self:flex-start;">' +
                '<i class="fas fa-times"></i></button>';

        toast.setAttribute('data-swift-toast', '1');
        container.appendChild(toast);

        // Pas d'auto-dismiss : le toast reste jusqu'au clic sur ×
    }

    /* ─────────────────────────────────────────────
       BELL / DROPDOWN — Notifications persistantes
    ───────────────────────────────────────────── */
    var bellBtn     = null;
    var dropdown    = null;
    var badgeEl     = null;
    var listEl      = null;
    var emptyEl     = null;
    var markAllBtn  = null;
    var dropOpen    = false;
    var unreadCount = 0;

    function updateBadge(count) {
        unreadCount = count;
        if (!badgeEl) return;
        if (count > 0) {
            badgeEl.textContent = count > 99 ? '99+' : count;
            badgeEl.style.display = 'flex';
        } else {
            badgeEl.style.display = 'none';
        }
    }

    function renderNotifItem(item) {
        var d   = item.data || {};
        var dir = d.direction || 'IN';
        var col = dirColor(dir);
        return '<div data-notif-id="' + item.id + '" ' +
            'style="padding:12px 16px;border-bottom:1px solid #f3f4f6;cursor:pointer;' +
                   'display:flex;gap:10px;align-items:flex-start;transition:background .15s;" ' +
            'onmouseover="this.style.background=\'#f9fafb\'" ' +
            'onmouseout="this.style.background=\'\'">' +
            '<div style="flex-shrink:0;width:32px;height:32px;border-radius:50%;' +
                'background:' + col + ';display:flex;align-items:center;justify-content:center;">' +
                '<i class="fas fa-envelope" style="color:#fff;font-size:.72rem;"></i>' +
            '</div>' +
            '<div style="flex:1;min-width:0;">' +
                '<div style="display:flex;justify-content:space-between;gap:6px;">' +
                    '<span style="font-size:.8rem;font-weight:700;color:#111827;">' +
                        (d.type || 'SWIFT') + ' ' +
                        '<span style="font-weight:400;color:#6b7280;">' + (d.reference || '') + '</span>' +
                    '</span>' +
                    '<span style="font-size:.66rem;color:#9ca3af;white-space:nowrap;">' + (item.created_at || '') + '</span>' +
                '</div>' +
                '<div style="margin-top:3px;display:flex;gap:6px;align-items:center;">' +
                    '<span style="font-size:.67rem;font-weight:700;padding:1px 7px;border-radius:12px;' +
                        'background:' + col + '22;color:' + col + ';">' + dirLabel(dir) + '</span>' +
                    (d.amount ? '<span style="font-size:.68rem;color:#374151;">' +
                        '<i class="fas fa-coins" style="color:#f59e0b;margin-right:2px;"></i>' +
                        d.amount + ' ' + (d.currency || '') + '</span>' : '') +
                '</div>' +
                (d.sender ? '<div style="font-size:.68rem;color:#9ca3af;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' +
                    '<i class="fas fa-building" style="margin-right:3px;"></i>' + d.sender + '</div>' : '') +
            '</div>' +
            '<span style="width:8px;height:8px;background:#dc2626;border-radius:50%;flex-shrink:0;margin-top:4px;"></span>' +
        '</div>';
    }

    function loadNotifications() {
        fetch('/notifications/unread', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function (r) {
            if (!r.ok) { console.error('[Notifs] HTTP ' + r.status); return null; }
            return r.json();
        })
        .then(function (res) {
            if (!res) return;
            updateBadge(res.count || 0);
            if (!listEl || !emptyEl) return;
            listEl.innerHTML = '';
            if (!res.items || res.items.length === 0) {
                emptyEl.style.display = 'block';
            } else {
                emptyEl.style.display = 'none';
                res.items.forEach(function (item) {
                    listEl.insertAdjacentHTML('beforeend', renderNotifItem(item));
                });
            }
        })
        .catch(function (err) { console.error('[Notifs] fetch error:', err); });
    }

    function markOneRead(id) {
        var csrfToken = document.querySelector('meta[name="csrf-token"]') ?
            document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        fetch('/notifications/' + id + '/read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
        }).catch(function () {});

        var el = listEl && listEl.querySelector('[data-notif-id="' + id + '"]');
        if (el) el.remove();
        updateBadge(Math.max(0, unreadCount - 1));
        if (listEl && listEl.children.length === 0 && emptyEl) {
            emptyEl.style.display = 'block';
        }
    }

    function markAllRead() {
        var csrfToken = document.querySelector('meta[name="csrf-token"]') ?
            document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
        }).catch(function () {});

        if (listEl) listEl.innerHTML = '';
        if (emptyEl) emptyEl.style.display = 'block';
        updateBadge(0);
    }

    function prependNotification(data) {
        if (!listEl || !emptyEl) return;
        emptyEl.style.display = 'none';
        var item = {
            id: 'ws-' + Date.now(),
            data: {
                reference: data.reference,
                type:      data.type,
                direction: data.direction,
                sender:    data.sender,
                amount:    data.amount,
                currency:  data.currency,
            },
            created_at: "à l'instant",
        };
        listEl.insertAdjacentHTML('afterbegin', renderNotifItem(item));
    }

    function openDropdown() {
        if (!dropdown) return;
        dropOpen = true;
        dropdown.style.display = 'block';
        loadNotifications();
    }

    function closeDropdown() {
        if (!dropdown) return;
        dropOpen = false;
        dropdown.style.display = 'none';
    }

    /* ─────────────────────────────────────────────
       WebSocket listener (toast + badge)
    ───────────────────────────────────────────── */
    function initEchoListener() {
        if (!window.Echo) { setTimeout(initEchoListener, 300); return; }
        window.Echo.private('swift-managers')
            .listen('.swift.message.pending', function (data) {
                showSwiftToast(data);
                updateBadge(unreadCount + 1);
                prependNotification(data);
            });
    }

    /* ─────────────────────────────────────────────
       Init (compatible script en bas de <body>)
       DOMContentLoaded peut avoir déjà été déclenché
       → on utilise readyState pour appeler directement
    ───────────────────────────────────────────── */
    function initNotifications() {
        bellBtn    = document.getElementById('notif-bell-btn');
        dropdown   = document.getElementById('notif-dropdown');
        badgeEl    = document.getElementById('notif-badge');
        listEl     = document.getElementById('notif-list');
        emptyEl    = document.getElementById('notif-empty');
        markAllBtn = document.getElementById('notif-mark-all');

        /* charge le badge au chargement de page */
        loadNotifications();

        /* toggle dropdown */
        if (bellBtn) {
            bellBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (dropOpen) { closeDropdown(); } else { openDropdown(); }
            });
        }

        /* clic en dehors -> fermer */
        document.addEventListener('click', function (e) {
            var wrapper = document.getElementById('notif-bell-wrapper');
            if (dropOpen && wrapper && !wrapper.contains(e.target)) {
                closeDropdown();
            }
        });

        /* clic sur un item -> marquer lu */
        if (listEl) {
            listEl.addEventListener('click', function (e) {
                var item = e.target.closest('[data-notif-id]');
                if (item) markOneRead(item.getAttribute('data-notif-id'));
            });
        }

        /* marquer tout lu */
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                markAllRead();
            });
        }

        /* demarrer WebSocket */
        initEchoListener();
    }

    /* Le script est en bas de body → DOM déjà parsé dans la plupart des cas */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotifications);
    } else {
        initNotifications();
    }

})();
</script>

<style>
@keyframes swiftSlideIn {
    from { opacity: 0; transform: translateX(30px); }
    to   { opacity: 1; transform: translateX(0);    }
}
</style>
@endhasanyrole
</body>
</html>