
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
            --header-h:   60px;
            --text-primary:   rgba(255,255,255,0.95);
            --text-secondary: rgba(255,255,255,0.55);
            --text-muted:     rgba(255,255,255,0.30);
            --border:         rgba(255,255,255,0.07);
            --hover-bg:       rgba(255,255,255,0.06);
            --active-bg:      rgba(52,211,153,0.12);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #eef2ee; color: #111; }

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
        .top-navbar {
            position: sticky; top: 0; z-index: 100; background: white;
            border-bottom: 1px solid #e5e7eb; height: var(--header-h);
            display: flex; align-items: center; padding: 0 2rem;
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
        }
        .tn-sup    { font-size: .62rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: #9ca3af; }
        .tn-title  { font-family: 'Sora', sans-serif; font-size: 1.35rem; font-weight: 700; color: #111827; line-height: 1.1; }
        .tn-user   { display: flex; align-items: center; gap: .7rem; }
        .tn-uname  { font-size: .84rem; font-weight: 600; color: #111827; text-align: right; }
        .tn-urole  { font-size: .68rem; color: #6b7280; text-align: right; }
        .tn-avatar {
            width: 34px; height: 34px; background: var(--green-700); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: .82rem; font-weight: 700; flex-shrink: 0;
        }
        .main-wrapper {
            margin-left: var(--sidebar-w); min-height: 100vh;
            display: flex; flex-direction: column; transition: margin-left .3s ease;
        }
        .main-content { flex: 1; padding: 1.75rem 2rem; }
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04); }
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
        <div style="flex:1;">
            <div class="tn-sup">Operations Dashboard</div>
            <div class="tn-title"><?php echo $__env->yieldContent('page-title', 'SWIFT Messages'); ?></div>
        </div>
        <div class="tn-user">
            <div>
                <div class="tn-uname"><?php echo e(auth()->user()->name); ?></div>
                <div class="tn-urole"><?php echo e($primaryRole ?? 'Utilisateur'); ?></div>
            </div>
            <div class="tn-avatar"><?php echo e($initials); ?></div>
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
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH /var/www/resources/views/layouts/app.blade.php ENDPATH**/ ?>