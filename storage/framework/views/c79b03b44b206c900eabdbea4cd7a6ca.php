
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'BTL SWIFT Manager'); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

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
    <?php echo $__env->yieldPushContent('styles'); ?>
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


<nav class="sidebar" id="sidebar">

    <div class="sb-brand">
        <div class="sb-brand-logo">
            <img src="<?php echo e(asset('images/logo-btl.png')); ?>" alt="BTL"
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
            <?php echo e($primaryRole ?? 'Utilisateur'); ?>

        </span>
        <span class="sb-username"><?php echo e($user->name ?? ''); ?></span>
    </div>

    <div class="sb-body">

        
        <?php if($canViewReceived): ?>
            <a class="sec-btn" data-bs-toggle="collapse" href="#collapseRecu"
               aria-expanded="<?php echo e(request('direction') === 'RECU' ? 'true' : 'false'); ?>">
                <div class="sec-left">
                    <div class="sec-icon"><i class="fas fa-inbox"></i></div>
                    <span class="sec-label">Received Messages</span>
                </div>
                <div class="sec-right">
                    <span class="sec-count badge bg-success"><?php echo e($receivedTotal); ?></span>
                    <i class="fas fa-chevron-down sec-chevron"></i>
                </div>
            </a>
            <div class="collapse <?php echo e(request('direction') === 'RECU' ? 'show' : ''); ?>" id="collapseRecu">
                <?php $__empty_1 = true; $__currentLoopData = $receivedCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $catActive = request('categorie') === $cat['category'] && request('direction') === 'RECU'; ?>
                    <a class="cat-btn" data-bs-toggle="collapse"
                       href="#recu-<?php echo e(Str::slug($cat['category'])); ?>"
                       aria-expanded="<?php echo e($catActive ? 'true' : 'false'); ?>">
                        <div class="cat-left">
                            <i class="fas fa-folder cat-icon"></i>
                            <span class="cat-label"><?php echo e($cat['name']); ?></span>
                        </div>
                        <div class="cat-right">
                            <span class="cat-count badge bg-primary"><?php echo e($cat['total']); ?></span>
                            <i class="fas fa-chevron-right cat-chevron"></i>
                        </div>
                    </a>
                    <div class="collapse <?php echo e($catActive ? 'show' : ''); ?>" id="recu-<?php echo e(Str::slug($cat['category'])); ?>">
                        <div class="type-wrap">
                            <?php $__currentLoopData = $cat['types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $typeName  = $type['type']  ?? $type['filter'] ?? $type['name'] ?? '—';
                                    $typeCount = $type['count'] ?? 0;
                                    $isActive  = request('type_message') === $typeName && request('direction') === 'RECU';
                                ?>
                                <a class="type-link <?php echo e($isActive ? 'active' : ''); ?>"
                                   href="<?php echo e(route('swift.index')); ?>?direction=RECU&categorie=<?php echo e($cat['category']); ?>&type_message=<?php echo e($typeName); ?>">
                                    <div class="type-left">
                                        <span class="type-dot"></span>
                                        <span class="type-name"><?php echo e($typeName); ?></span>
                                    </div>
                                    <span class="type-badge"><?php echo e($typeCount); ?></span>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p style="font-size:.75rem;color:var(--text-muted);padding:.5rem 1.5rem;">Aucune catégorie</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="sec-btn is-disabled">
                <div class="sec-left">
                    <div class="sec-icon"><i class="fas fa-inbox"></i></div>
                    <span class="sec-label">Received Messages</span>
                </div>
                <span class="sec-count badge bg-secondary"><?php echo e($receivedTotal); ?></span>
            </div>
        <?php endif; ?>

        <div class="sb-divider"></div>

        
        <?php if($canViewEmitted): ?>
            <a class="sec-btn" data-bs-toggle="collapse" href="#collapseEmis"
               aria-expanded="<?php echo e(request('direction') === 'EMIS' ? 'true' : 'false'); ?>">
                <div class="sec-left">
                    <div class="sec-icon"><i class="fas fa-paper-plane"></i></div>
                    <span class="sec-label">Emitted Messages</span>
                </div>
                <div class="sec-right">
                    <span class="sec-count badge bg-warning text-dark"><?php echo e($emittedTotal); ?></span>
                    <i class="fas fa-chevron-down sec-chevron"></i>
                </div>
            </a>
            <div class="collapse <?php echo e(request('direction') === 'EMIS' ? 'show' : ''); ?>" id="collapseEmis">
                <?php $__empty_1 = true; $__currentLoopData = $emittedCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $catActive = request('categorie') === $cat['category'] && request('direction') === 'EMIS'; ?>
                    <a class="cat-btn" data-bs-toggle="collapse"
                       href="#emis-<?php echo e(Str::slug($cat['category'])); ?>"
                       aria-expanded="<?php echo e($catActive ? 'true' : 'false'); ?>">
                        <div class="cat-left">
                            <i class="fas fa-folder cat-icon"></i>
                            <span class="cat-label"><?php echo e($cat['name']); ?></span>
                        </div>
                        <div class="cat-right">
                            <span class="cat-count badge bg-success"><?php echo e($cat['total']); ?></span>
                            <i class="fas fa-chevron-right cat-chevron"></i>
                        </div>
                    </a>
                    <div class="collapse <?php echo e($catActive ? 'show' : ''); ?>" id="emis-<?php echo e(Str::slug($cat['category'])); ?>">
                        <div class="type-wrap">
                            <?php $__currentLoopData = $cat['types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $typeName  = $type['type']  ?? $type['filter'] ?? $type['name'] ?? '—';
                                    $typeCount = $type['count'] ?? 0;
                                    $isActive  = request('type_message') === $typeName && request('direction') === 'EMIS';
                                ?>
                                <a class="type-link <?php echo e($isActive ? 'active' : ''); ?>"
                                   href="<?php echo e(route('swift.index')); ?>?direction=EMIS&categorie=<?php echo e($cat['category']); ?>&type_message=<?php echo e($typeName); ?>">
                                    <div class="type-left">
                                        <span class="type-dot"></span>
                                        <span class="type-name"><?php echo e($typeName); ?></span>
                                    </div>
                                    <span class="type-badge"><?php echo e($typeCount); ?></span>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p style="font-size:.75rem;color:var(--text-muted);padding:.5rem 1.5rem;">Aucune catégorie</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="sec-btn is-disabled">
                <div class="sec-left">
                    <div class="sec-icon"><i class="fas fa-paper-plane"></i></div>
                    <span class="sec-label">Emitted Messages</span>
                </div>
                <span class="sec-count badge bg-secondary"><?php echo e($emittedTotal); ?></span>
            </div>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['import', 'export'], App\Models\MessageSwift::class)): ?>
            <div class="sb-divider"></div>
            <span class="sb-section-label">Outils</span>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('import', App\Models\MessageSwift::class)): ?>
            <a class="util-link <?php echo e(request()->is('swift/importer') ? 'active' : ''); ?>"
               href="<?php echo e(route('swift.import.form')); ?>">
                <span class="util-icon"><i class="fas fa-upload"></i></span>
                Import SWIFT
            </a>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('export', App\Models\MessageSwift::class)): ?>
            <a class="util-link <?php echo e(request()->is('swift/export-center') ? 'active' : ''); ?>"
               href="<?php echo e(route('swift.export-center')); ?>">
                <span class="util-icon"><i class="fas fa-download"></i></span>
                Export Center
            </a>
        <?php endif; ?>

        
        <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'swift-manager|super-admin')): ?>
            <div class="sb-divider"></div>
            <span class="sb-section-label">Intelligence Artificielle</span>
            <a class="util-link <?php echo e(request()->is('international-admin/ia-analytics') ? 'active' : ''); ?>"
               href="<?php echo e(route('international-admin.ia-analytics')); ?>">
                <span class="util-icon"><i class="fas fa-chart-bar"></i></span>
                Graphiques IA
            </a>
            <a class="util-link <?php echo e(request()->is('swift/anomalies*') ? 'active' : ''); ?>"
               href="<?php echo e(route('swift.anomalies.index')); ?>">
                <span class="util-icon"><i class="fas fa-brain"></i></span>
                Anomalies IA
            </a>
        <?php endif; ?>

        
        <?php if($canViewAdmin): ?>
            <div class="sb-divider"></div>
            <span class="sb-section-label">Administration</span>
            <a class="util-link <?php echo e(request()->is('admin/users*') ? 'active' : ''); ?>"
               href="<?php echo e(route('admin.users.index')); ?>">
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
        <?php endif; ?>

    </div>

    <div class="sb-footer">
        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="logout-btn">
                <i class="fas fa-arrow-right-from-bracket"></i>
                Déconnexion
            </button>
        </form>
    </div>

</nav>


<div class="main-wrapper">

    
    <header class="top-navbar">

        
        <button class="btn btn-sm me-3 d-lg-none"
                style="background:none;border:1px solid #e5e7eb;color:#374151;"
                onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="fas fa-bars"></i>
        </button>

        
        <div style="display:flex;align-items:center;gap:12px;flex:1;">
            <div style="width:40px;height:40px;background:#f0fdf4;border-radius:50%;
                        display:flex;align-items:center;justify-content:center;
                        border:1.5px solid #bbf7d0;flex-shrink:0;">
                <i class="fas fa-globe" style="color:#16a34a;font-size:.92rem;"></i>
            </div>
            <div>
                <div style="font-family:'Sora',sans-serif;font-size:1rem;font-weight:700;
                            color:#111827;line-height:1.2;">
                    <?php echo $__env->yieldContent('page-title', 'SWIFT Messages'); ?>
                </div>
                <div id="hdr-datetime" style="font-size:.71rem;color:#9ca3af;margin-top:2px;"></div>
            </div>
        </div>

        
        <div style="display:flex;align-items:center;gap:14px;">

            <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'swift-manager')): ?>
            
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
            <?php endif; ?>

            
            <button id="darkToggleBtn"
                    style="width:38px;height:38px;background:#f9fafb;border:1px solid #e5e7eb;
                           border-radius:50%;display:flex;align-items:center;justify-content:center;
                           cursor:pointer;flex-shrink:0;transition:background .2s,border-color .2s;"
                    title="Basculer mode sombre / clair">
                <i class="fas fa-moon" id="darkToggleIcon" style="color:#6b7280;font-size:.78rem;"></i>
            </button>

            
            <div>
                
                <div style="display:flex;align-items:center;gap:8px;justify-content:flex-end;">
                    <span style="font-size:.84rem;font-weight:700;color:#111827;white-space:nowrap;">
                        <?php echo e(auth()->user()->name); ?>

                    </span>
                    <span style="background:#dc2626;color:#fff;font-size:.58rem;font-weight:800;
                                 letter-spacing:.07em;text-transform:uppercase;
                                 padding:2px 9px;border-radius:20px;white-space:nowrap;">
                        <?php echo e($primaryRole ?? 'Utilisateur'); ?>

                    </span>
                </div>
                
                <div style="display:flex;align-items:center;gap:6px;justify-content:flex-end;margin-top:4px;">
                    <span style="display:flex;align-items:center;gap:5px;font-size:.68rem;color:#6b7280;">
                        <span class="online-dot"></span>En ligne
                    </span>
                    <span style="color:#d1d5db;font-size:.7rem;">·</span>
                    <span style="font-size:.68rem;color:#9ca3af;">Tunisian Libyan Bank</span>
                </div>
            </div>

            
            <div style="position:relative;flex-shrink:0;">
                <div style="width:38px;height:38px;background:#0a4d2b;border:2.5px solid #86efac;
                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                            color:#fff;font-weight:700;font-size:.88rem;">
                    <?php echo e($initials); ?>

                </div>
                <span style="position:absolute;bottom:0;right:0;width:10px;height:10px;
                             background:#22c55e;border-radius:50%;border:2px solid #fff;"></span>
            </div>

        </div>
    </header>

    <main class="main-content">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el, { html: false, trigger: 'hover' });
        });
    });
</script>


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

<?php echo $__env->yieldPushContent('scripts'); ?>

<?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'swift-manager')): ?>


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
<?php endif; ?>

<?php
    $quickQuestions = match($primaryRole) {
        'swift-manager', 'super-admin' => [
            'Anomalies HIGH du jour ?',
            'Messages en attente ?',
            'Volume traité ce mois ?',
            'Risques à surveiller ?'
        ],
        'swift-operator' => [
            'Mes derniers imports ?','Messages rejetés ?','Comment créer un MT103 ?','Statut de la queue ?'
        ],
        'backoffice', 'monetique' => [
            'PACS.008 reçus aujourd\'hui ?','CAMT.053 en attente ?','Volume USD reçu ?','Rapprochements à faire ?'
        ],
        default => [
            'Transactions de mon agence ?','Volume ce mois ?','Messages MT103 émis ?','Comment lire un SWIFT ?'
        ]
    };
?>

<!-- Floating Chat FAB + Panel -->
    <style>
    .fab-chat { position: fixed; bottom: 24px; right: 24px; z-index: 9998; }
    .fab-btn {
        width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;
        background: transparent; color:#fff; box-shadow:0 8px 28px rgba(6,24,44,.14);
        border:none; cursor:pointer; transition:transform .18s ease; padding:0;
    }
    .fab-dot { position:absolute; top:-4px; right:-4px; width:10px; height:10px; border-radius:50%; background:#22c55e; box-shadow:0 0 0 rgba(34,197,94, .7); }
    .fab-dot::after { content:''; position:absolute; inset:-6px; border-radius:50%; background:#22c55e; opacity:.3; animation:onlinePulse 1.8s infinite; }

    .chat-panel {
        position: fixed; bottom: 88px; right: 24px; z-index: 9997; width:380px; max-width:94vw; border-radius:14px;
        box-shadow:0 12px 48px rgba(6,24,44,.18); background:var(--panel-bg,#fff); overflow:hidden;
        transform: translateY(8px); opacity:0; transition:transform .28s ease,opacity .28s ease;
        border:1px solid #eef2f3;
    }
    .chat-panel.show { transform: translateY(0); opacity:1; }
    .chat-header { padding:14px 16px; display:flex;align-items:center;gap:12px;border-bottom:1px solid #f3f6f7; background: linear-gradient(180deg, #ffffff, #fbfdff); }
    .chat-header .avatar { width:48px;height:48px;border-radius:10px;background:#fff;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;box-shadow:0 4px 14px rgba(10,77,43,.08) }
    .chat-header .title { font-weight:800; font-size:1rem; color:#0f172a }
    .chat-header .subtitle { font-size:.78rem;color:#6b7280;display:flex;align-items:center;gap:8px }
    .chat-body { height:220px; overflow-y:auto; padding:12px; background:var(--chat-bg,#fff); }
    .chat-footer { padding:10px; border-top:1px solid #f3f4f6; display:flex; gap:8px; align-items:center }
    .suggestions { padding:10px 12px;border-bottom:1px solid #f3f4f6; }
    .suggestions-scroll { display:flex;gap:8px;flex-wrap:wrap; }
    .chip { display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:#f7fafb;color:#0f172a;font-size:.86rem;margin:6px 6px 0 0;cursor:pointer;border:1px solid #eef2f3;transition:transform .12s ease,box-shadow .12s ease }
    .chip:hover { transform:translateY(-3px); box-shadow:0 6px 18px rgba(15,23,42,.06) }
    .chip .c-icon { width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;border-radius:4px;background:linear-gradient(135deg,#ecfeff,#dbeafe);color:#0369a1;font-size:.78rem }
    .chip.btn { border:none;background:#fff }
    .chip:active { transform:translateY(-1px) }
    .chat-input { flex:1;padding:10px;border-radius:10px;border:1px solid #e6eef0;font-size:.92rem;transition:box-shadow .12s,border-color .12s }
    .chat-input:focus { outline:none; box-shadow:0 6px 20px rgba(16,185,129,.08); border-color:#10b981 }
    .send-btn { background:linear-gradient(135deg,#0a4d2b,#16a34a);border:none;padding:9px 11px;border-radius:10px;color:#fff;display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px }
    .send-btn[disabled] { opacity:.6; cursor:not-allowed }
    .fa-spin { animation: fa-spin 1s linear infinite; }
    @keyframes fa-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .msg-user { text-align:right }
    .bubble { display:inline-block;padding:10px 14px;border-radius:12px;max-width:78%;font-size:.9rem;line-height:1.25 }
    .bubble.user { background:linear-gradient(135deg,#dcfce7,#bbf7d0);color:#064e23 }
    .bubble.ai { background:#fbfbfc;color:#0f172a;border:1px solid #eef2f3 }

    /* Actualités */
    .chat-news { padding:10px;border-top:1px solid #f3f4f6;background:linear-gradient(180deg,#ffffff,#fbfdff); }
    .chat-news .news-item { display:flex;gap:10px;padding:8px;border-radius:8px;align-items:flex-start;text-decoration:none;color:inherit;transition:background .12s }
    .chat-news .news-item:hover { background:#f8fafb }
    .chat-news .news-item img { width:64px;height:44px;object-fit:cover;border-radius:6px;flex-shrink:0 }
    .chat-news .news-title { font-size:.86rem;font-weight:700;color:#0f172a }
    .chat-news .news-excerpt { font-size:.72rem;color:#6b7280;margin-top:4px }
    @media (max-width:600px) { .chat-panel { right: 5vw; left:5vw; width:90vw; bottom: 80px } }
</style>

<div x-data="{
    open:false, question:'', loading:false, history:[], messages:[], news:[], loadingNews:false,
    send(q){
        if(!q) return; if(this.loading) return;
        const now = new Date();
        const time = now.getHours().toString().padStart(2,'0')+':'+now.getMinutes().toString().padStart(2,'0');
        this.messages.push({from:'user', text:q, time:time});
        this.history.push({role:'user', content:q});
        if(this.history.length>10) this.history = this.history.slice(-10);
        this.question=''; this.loading=true;
        const payload = { question: q, history: this.history, role: '<?php echo e($primaryRole ?? 'user'); ?>', page: 'dashboard', stats: this.getStats() };
        const token = document.querySelector('meta[name=csrf-token]').content;
        fetch('/chat-global', { method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN': token, 'Accept':'application/json'}, body: JSON.stringify(payload) })
            .then(r=>{ if(!r.ok) throw r; return r.json(); })
            .then(data=>{
                const ans = data.answer || 'Réponse indisponible.';
                const now2 = new Date(); const time2 = now2.getHours().toString().padStart(2,'0')+':'+now2.getMinutes().toString().padStart(2,'0');
                this.messages.push({from:'ai', text: ans, time: time2});
                this.history.push({role:'assistant', content: ans});
                if(this.history.length>10) this.history = this.history.slice(-10);
            })
            .catch(err=>{
                const msg = 'Service temporairement indisponible';
                const now3 = new Date(); const time3 = now3.getHours().toString().padStart(2,'0')+':'+now3.getMinutes().toString().padStart(2,'0');
                this.messages.push({from:'ai', text: msg, time: time3});
            })
            .finally(()=>{ this.loading=false; this.$nextTick(()=>{ const el=this.$refs.scroll; if(el) el.scrollTop = el.scrollHeight; }); });
    },
    loadNews(){
        try {
            this.loadingNews = true;
            const self = this;
            fetch('/api/news?limit=3', { headers: { 'Accept': 'application/json' } })
                .then(function(r){ if(!r.ok) throw r; return r.json(); })
                .then(function(res){
                    // expected res.items = [{id,title,excerpt,url,image}]
                    self.news = Array.isArray(res.items) ? res.items : (res.items || []);
                }).catch(function(){ self.news = []; })
                .finally(function(){ self.loadingNews = false; });
        } catch (e) { this.news = []; this.loadingNews = false; }
    },
    getStats(){ return {
        total: '<?php echo e($transCount ?? 0); ?>', pending: '<?php echo e($pendingAuth ?? 0); ?>', anomalies: '<?php echo e($criticalCount ?? 0); ?>',
        volume: '<?php echo e($volumeFormatted ?? "0"); ?>', received: '<?php echo e($receivedTotal ?? 0); ?>', emitted: '<?php echo e($emittedTotal ?? 0); ?>'
    }; }
}" x-init="loadNews()">

    <div class="fab-chat" style="<?php echo e(request()->is('login') ? 'display:none' : ''); ?>">
        <div style="position:relative">
            <button class="fab-btn" @click="open = !open" :class="{'rotated': open}" :aria-expanded="open.toString()" title="Assistant IA" aria-label="Assistant IA">
                <svg width="40" height="40" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <defs>
                        <linearGradient id="gFab" x1="0" x2="1" y1="0" y2="1">
                            <stop offset="0" stop-color="#0a702f" />
                            <stop offset="1" stop-color="#16a34a" />
                        </linearGradient>
                    </defs>
                    <circle cx="24" cy="24" r="22" fill="url(#gFab)" />
                    <!-- robot head -->
                    <rect x="14" y="14" width="20" height="16" rx="4" fill="#ffffff" />
                    <!-- eyes -->
                    <rect x="18.5" y="18.5" width="3" height="3" rx="0.8" fill="#0a4d2b" />
                    <rect x="26.5" y="18.5" width="3" height="3" rx="0.8" fill="#0a4d2b" />
                    <!-- mouth -->
                    <rect x="20.5" y="23.5" width="7" height="2" rx="1" fill="#d1ffd8" />
                    <!-- antenna -->
                    <circle cx="24" cy="11.5" r="2" fill="#ffffff" />
                    <rect x="23.4" y="12.5" width="1.2" height="4" rx="0.6" fill="#ffffff" />
                </svg>
            </button>
            <span class="fab-dot"></span>
        </div>
    </div>

    <div class="chat-panel" x-show="open" x-bind:class="open ? 'show' : ''" x-transition @click.outside="open = false">
            <div class="chat-header">
            <div class="avatar" style="background:transparent;padding:0;">
                <img src="<?php echo e(asset('images/logo-btl.png')); ?>" alt="BTL" style="width:42px;height:42px;object-fit:contain;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.12);background:#fff;padding:4px;" />
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700">Assistant IA</div>
                <div style="font-size:.78rem;color:#6b7280;display:flex;align-items:center;gap:6px;">
                    <span class="online-dot"></span>
                    <span>En ligne</span>
                </div>
            </div>
            <button @click="open=false" style="background:none;border:none;font-size:1.05rem;color:#6b7280">✕</button>
        </div>

        <div class="suggestions">
            <div class="suggestions-scroll">
                <?php $__currentLoopData = $quickQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" class="chip" @click.prevent="send('<?php echo e(addslashes($q)); ?>')" aria-label="Suggestion: <?php echo e($q); ?>">
                        <span class="c-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="12" rx="2" fill="#ecfeff" />
                                <rect x="7" y="8" width="3" height="3" rx="0.6" fill="#0369a1" />
                                <rect x="14" y="8" width="3" height="3" rx="0.6" fill="#0369a1" />
                                <rect x="9" y="12" width="6" height="1.6" rx="0.6" fill="#cfeffd" />
                            </svg>
                        </span>
                        <span style="font-weight:600;font-size:.85rem;"><?php echo e($q); ?></span>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="chat-body" x-ref="scroll">
            <template x-if="messages.length === 0">
                <div style="font-size:.9rem;color:#6b7280;padding:8px">Bonjour <?php echo e(auth()->user()->name); ?> ! Je connais vos stats en temps réel. Que souhaitez-vous analyser ?</div>
            </template>
            <template x-for="m in messages" :key="m.time + m.text">
                <div style="margin-bottom:8px;display:flex;flex-direction:column;">
                    <div x-bind:class="m.from === 'user' ? 'msg-user' : ''">
                        <span class="bubble" x-bind:class="m.from === 'user' ? 'user' : 'ai'" x-text="m.text"></span>
                    </div>
                    <div style="font-size:.68rem;color:#9ca3af;margin-top:4px;" x-text="m.time"></div>
                </div>
            </template>
        </div>

        <div class="chat-news" x-show="news.length > 0" x-cloak>
            <div style="font-weight:700;margin-bottom:8px;font-size:.9rem">Actualités</div>
            <template x-for="n in news" :key="n.id">
                <a :href="n.url || '#'" target="_blank" class="news-item">
                    <img x-show="n.image" :src="n.image" alt="" />
                    <div>
                        <div class="news-title" x-text="n.title"></div>
                        <div class="news-excerpt" x-text="n.excerpt"></div>
                    </div>
                </a>
            </template>
        </div>

        <div class="chat-footer">
            <input x-model="question" @keyup.enter="send(question)" :disabled="loading" placeholder="Posez votre question..." class="chat-input" />
            <button class="send-btn" @click.prevent="send(question)" :disabled="loading" aria-label="Envoyer">
                <i class="fas fa-circle-notch fa-spin" x-show="loading" style="font-size:1rem;display:none"></i>
                <i class="fas fa-paper-plane" x-show="!loading" style="font-size:1rem"></i>
            </button>
        </div>
    </div>

</div>

</body>
</html><?php /**PATH /var/www/resources/views/layouts/app.blade.php ENDPATH**/ ?>