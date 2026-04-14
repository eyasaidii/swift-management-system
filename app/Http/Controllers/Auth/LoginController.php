<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validation
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Tentative d'authentification
        if (Auth::attempt([
            'email' => strtolower($request->email),
            'password' => $request->password
        ])) {

            $request->session()->regenerate();

            $user = Auth::user();

            // 🔥 Redirection selon rôle
            switch ($user->role) {

                case 'super-admin':
                case 'admin':
                    return redirect('/admin/dashboard');

                case 'swift-manager':
                case 'international-admin':
                    return redirect('/international-admin/dashboard');

                case 'swift-operator':
                case 'international-user':
                    return redirect('/international-user/dashboard');

                case 'backoffice':
                    return redirect('/backoffice/dashboard');

                case 'monetique':
                    return redirect('/monetique/dashboard');

                case 'chef-agence':
                    return redirect('/chef-agence/dashboard');

                case 'chargee':
                    return redirect('/chargee/dashboard');

                case 'compliance-officer':
                    return redirect('/compliance/dashboard');

                default:
                    return redirect('/dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
