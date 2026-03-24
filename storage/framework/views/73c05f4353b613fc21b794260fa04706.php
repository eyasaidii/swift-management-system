<?php $__env->startSection('title', 'Connexion sécurisée - BTL Bank'); ?>

<?php $__env->startSection('content'); ?>

<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<div class="login-page">

    
    <div class="bg-layer"></div>
    
    <div class="bg-overlay"></div>

    
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    
    <div class="card-wrap">
        <div class="login-card">

            
            <div class="card-top-bar"></div>

            
            <div class="card-header-section">
                <div class="logo-ring">
                    <img src="<?php echo e(asset('images/logo-btl.png')); ?>" alt="BTL Bank" height="52"
                         onerror="this.onerror=null;this.src='https://via.placeholder.com/130x52/0a4d2b/ffffff?text=BTL';">
                </div>
                <div class="divider-line"></div>
                <h1 class="card-title">Bienvenue</h1>
                <p class="card-subtitle">
                    <i class="fas fa-shield-halved me-1"></i>
                    Plateforme des Messages SWIFT
                </p>
            </div>

            
            <?php if($errors->any()): ?>
            <div class="error-alert">
                <i class="fas fa-circle-exclamation"></i>
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            </div>
            <?php endif; ?>

            
            <form method="POST" action="<?php echo e(route('login')); ?>" id="loginForm" autocomplete="off">
                <?php echo csrf_field(); ?>

                
                <div class="field-group">
                    <label for="email">Email professionnel</label>
                    <div class="field-wrap">
                        <span class="field-icon"><i class="far fa-envelope"></i></span>
                        <input id="email"
                               type="email"
                               name="email"
                               class="field-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               placeholder="email"
                               required
                               autofocus>
                    </div>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="field-error"><i class="fas fa-triangle-exclamation me-1"></i><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="field-group">
                    <label for="password">Mot de passe</label>
                    <div class="field-wrap">
                        <span class="field-icon"><i class="far fa-lock"></i></span>
                        <input id="password"
                               type="password"
                               name="password"
                               class="field-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               placeholder="••••••••"
                               required
                               autocomplete="current-password">
                        <button type="button" class="eye-btn" onclick="togglePassword()" tabindex="-1">
                            <i class="far fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="field-error"><i class="fas fa-triangle-exclamation me-1"></i><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="row-options">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" id="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                        <span class="check-box"></span>
                        Se souvenir de moi
                    </label>
                    <?php if(Route::has('password.request')): ?>
                        <a href="<?php echo e(route('password.request')); ?>" class="forgot-link">Mot de passe oublié ?</a>
                    <?php endif; ?>
                </div>

                
                <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="btn-inner">
                        <span class="btn-text">Se connecter</span>
                        <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
                    </span>
                    <span class="btn-loader d-none">
                        <span class="spinner-border spinner-border-sm"></span>
                        Connexion en cours…
                    </span>
                </button>

            </form>

            
            <p class="card-footer-text">
                <i class="fas fa-lock-keyhole me-1 opacity-50"></i>
                © <?php echo e(date('Y')); ?> BTL Bank — Accès réservé au personnel autorisé
            </p>

        </div>
    </div>

</div>


<style>
/* ═══════════════════════════════════════════
   BASE
═══════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body { font-family: 'DM Sans', sans-serif; }

/* ═══════════════════════════════════════════
   PAGE SHELL
═══════════════════════════════════════════ */
.login-page {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    overflow: hidden;
}

/* Background image */
.bg-layer {
    position: absolute; inset: 0;
    background-image: url('<?php echo e(asset('images/background.jpg')); ?>');
    background-size: cover;
    background-position: center;
    filter: blur(10px) brightness(0.55) saturate(1.1);
    transform: scale(1.08);
    z-index: 0;
}

/* Dark gradient overlay */
.bg-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(135deg,
        rgba(5, 30, 15, 0.55) 0%,
        rgba(10, 77, 43, 0.35) 50%,
        rgba(5, 20, 10, 0.6) 100%);
    z-index: 1;
}

/* Floating orbs */
.orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.18;
    z-index: 2;
    pointer-events: none;
    animation: floatOrb 8s ease-in-out infinite;
}
.orb-1 { width: 420px; height: 420px; background: #0a4d2b; top: -100px; left: -120px; animation-delay: 0s; }
.orb-2 { width: 300px; height: 300px; background: #148a48; bottom: -80px; right: -80px; animation-delay: 3s; }
.orb-3 { width: 200px; height: 200px; background: #7ecba0; top: 40%; left: 60%; animation-delay: 5s; }

@keyframes floatOrb {
    0%, 100% { transform: translateY(0px) scale(1); }
    50%       { transform: translateY(-30px) scale(1.05); }
}

/* ═══════════════════════════════════════════
   CARD WRAPPER (entry animation)
═══════════════════════════════════════════ */
.card-wrap {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 480px;
    animation: slideUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(40px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0)   scale(1); }
}

/* ═══════════════════════════════════════════
   CARD
═══════════════════════════════════════════ */
.login-card {
    background: rgba(255, 255, 255, 0.97);
    border-radius: 28px;
    overflow: hidden;
    box-shadow:
        0 32px 64px -16px rgba(0, 20, 10, 0.45),
        0 0 0 1px rgba(255,255,255,0.6) inset,
        0 1px 0 rgba(255,255,255,0.9) inset;
    padding: 0 2.8rem 2.4rem;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}
.login-card:hover {
    box-shadow:
        0 40px 80px -20px rgba(10, 77, 43, 0.4),
        0 0 0 1px rgba(255,255,255,0.6) inset;
    transform: translateY(-2px);
}

/* Top accent bar */
.card-top-bar {
    margin: 0 -2.8rem 2rem;
    height: 5px;
    background: linear-gradient(90deg, #0a4d2b 0%, #1aad5a 50%, #0a4d2b 100%);
    background-size: 200% 100%;
    animation: shimmerBar 3s linear infinite;
}
@keyframes shimmerBar {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* ═══════════════════════════════════════════
   CARD HEADER
═══════════════════════════════════════════ */
.card-header-section {
    text-align: center;
    padding-bottom: 1.8rem;
}

.logo-ring {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.25rem;
    background: #f0f9f4;
    border-radius: 18px;
    border: 1.5px solid rgba(10, 77, 43, 0.1);
    margin-bottom: 1.2rem;
    box-shadow: 0 4px 12px rgba(10, 77, 43, 0.08);
}

.divider-line {
    width: 48px;
    height: 2px;
    background: linear-gradient(90deg, transparent, #0a4d2b, transparent);
    margin: 0 auto 1rem;
    border-radius: 2px;
}

.card-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.85rem;
    font-weight: 700;
    color: #0a4d2b;
    letter-spacing: -0.02em;
    line-height: 1.1;
    margin-bottom: 0.4rem;
}

.card-subtitle {
    font-size: 0.88rem;
    color: #64748b;
    font-weight: 400;
    letter-spacing: 0.02em;
}
.card-subtitle i { color: #0a4d2b; opacity: 0.6; }

/* ═══════════════════════════════════════════
   ERROR ALERT
═══════════════════════════════════════════ */
.error-alert {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    background: #fff5f5;
    border: 1px solid #fecaca;
    border-left: 4px solid #ef4444;
    border-radius: 14px;
    padding: 0.9rem 1rem;
    margin-bottom: 1.5rem;
    font-size: 0.85rem;
    color: #b91c1c;
    position: relative;
}
.error-alert i { margin-top: 2px; flex-shrink: 0; }
.error-alert ul { list-style: none; padding: 0; margin: 0; }
.error-alert .close-btn {
    position: absolute; top: 0.5rem; right: 0.75rem;
    background: none; border: none; cursor: pointer;
    font-size: 1.1rem; color: #b91c1c; line-height: 1; opacity: 0.7;
}

/* ═══════════════════════════════════════════
   FORM FIELDS
═══════════════════════════════════════════ */
.field-group {
    margin-bottom: 1.3rem;
}

.field-group label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.45rem;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.field-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.field-icon {
    position: absolute;
    left: 1rem;
    color: #0a4d2b;
    font-size: 0.95rem;
    opacity: 0.45;
    z-index: 2;
    pointer-events: none;
}

.field-input {
    width: 100%;
    padding: 0.85rem 3rem 0.85rem 2.6rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 14px;
    font-size: 0.97rem;
    font-family: 'DM Sans', sans-serif;
    font-weight: 400;
    color: #111827;
    background: #f9fafb;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    outline: none;
}

.field-input::placeholder {
    color: #9ca3af;
    font-style: italic;
    font-weight: 300;
}

.field-input:focus {
    border-color: #0a4d2b;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(10, 77, 43, 0.09);
}

.field-input.is-invalid {
    border-color: #ef4444;
    background: #fff5f5;
}
.field-input.is-invalid:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

.field-error {
    display: block;
    font-size: 0.8rem;
    color: #ef4444;
    margin-top: 0.35rem;
}

/* Eye toggle */
.eye-btn {
    position: absolute;
    right: 0.9rem;
    background: none;
    border: none;
    cursor: pointer;
    color: #9ca3af;
    font-size: 1rem;
    padding: 0.25rem;
    transition: color 0.2s;
    z-index: 2;
}
.eye-btn:hover { color: #0a4d2b; }

/* ═══════════════════════════════════════════
   ROW OPTIONS (remember + forgot)
═══════════════════════════════════════════ */
.row-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0.3rem 0 1.8rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* Custom checkbox */
.remember-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.88rem;
    color: #4b5563;
    cursor: pointer;
    user-select: none;
}
.remember-label input[type="checkbox"] { display: none; }
.check-box {
    width: 18px; height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    background: white;
    flex-shrink: 0;
}
.remember-label input:checked + .check-box {
    background: #0a4d2b;
    border-color: #0a4d2b;
}
.remember-label input:checked + .check-box::after {
    content: '';
    width: 5px; height: 9px;
    border: 2px solid white;
    border-top: none; border-left: none;
    transform: rotate(45deg) translateY(-1px);
}

.forgot-link {
    font-size: 0.88rem;
    font-weight: 600;
    color: #0a4d2b;
    text-decoration: none;
    position: relative;
}
.forgot-link::after {
    content: '';
    position: absolute;
    bottom: -1px; left: 0;
    width: 0; height: 1.5px;
    background: #0a4d2b;
    transition: width 0.25s ease;
}
.forgot-link:hover::after { width: 100%; }

/* ═══════════════════════════════════════════
   SUBMIT BUTTON
═══════════════════════════════════════════ */
.submit-btn {
    width: 100%;
    padding: 0.9rem 1.5rem;
    border: none;
    border-radius: 16px;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    font-size: 1rem;
    font-weight: 600;
    color: white;
    background: linear-gradient(135deg, #0a4d2b 0%, #0f6a3a 60%, #148a48 100%);
    background-size: 200% 100%;
    background-position: 0% 0;
    box-shadow: 0 8px 24px -6px rgba(10, 77, 43, 0.45);
    transition: all 0.35s ease;
    position: relative;
    overflow: hidden;
    letter-spacing: 0.02em;
}

.submit-btn::before {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 60%);
    opacity: 0;
    transition: opacity 0.3s;
}

.submit-btn:hover {
    background-position: 100% 0;
    transform: translateY(-2px);
    box-shadow: 0 14px 32px -8px rgba(10, 77, 43, 0.5);
}
.submit-btn:hover::before { opacity: 1; }
.submit-btn:active { transform: translateY(0); }

.btn-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px; height: 26px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    font-size: 0.75rem;
    transition: transform 0.25s ease;
}
.submit-btn:hover .btn-icon { transform: translateX(3px); }

.btn-loader {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.submit-btn.loading .btn-inner  { display: none !important; }
.submit-btn.loading .btn-loader { display: flex !important; }

/* ═══════════════════════════════════════════
   FOOTER
═══════════════════════════════════════════ */
.card-footer-text {
    text-align: center;
    font-size: 0.78rem;
    color: #9ca3af;
    margin-top: 2rem;
    letter-spacing: 0.01em;
}

/* ═══════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════ */
@media (max-width: 520px) {
    .login-card { padding: 0 1.5rem 2rem; }
    .card-title { font-size: 1.55rem; }
    .orb-1, .orb-2, .orb-3 { display: none; }
}
</style>

<script>
function togglePassword() {
    const inp  = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        inp.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

document.getElementById('loginForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.classList.add('loading');
    btn.disabled = true;
});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\eya saidi\Desktop\btl-swift-platform\btl-swift-platform-main\btl-swift-platform-main\resources\views/auth/login.blade.php ENDPATH**/ ?>