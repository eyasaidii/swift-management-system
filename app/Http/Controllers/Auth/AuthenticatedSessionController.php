<?php 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View; // Important !

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        
        // Rediriger selon le rôle
        return $this->redirectToRoleDashboard($user);
    }

    /**
     * Redirection selon le rôle
     */
    protected function redirectToRoleDashboard($user): RedirectResponse
    {
        $primaryRole = $user->getRoleNames()->first();

        return match($primaryRole) {
            'super-admin' => redirect()->route('admin.dashboard'),
            'swift-manager' => redirect()->route('international-admin.dashboard'),
            'swift-operator' => redirect()->route('international-user.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            'international-admin' => redirect()->route('international-admin.dashboard'),
            'international-user' => redirect()->route('international-user.dashboard'),
            'backoffice' => redirect()->route('backoffice.dashboard'),
            'monetique' => redirect()->route('monetique.dashboard'),
            'chef-agence' => redirect()->route('chef-agence.dashboard'),
            'chargee' => redirect()->route('chargee.dashboard'),
            'compliance-officer' => redirect()->route('compliance.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}