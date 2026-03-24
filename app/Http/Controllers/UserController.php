<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Constructeur - Protection par authentification
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher la liste des utilisateurs
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $search = $request->input('search');
        $role = $request->input('role');

        $query = User::with('roles');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role && $role !== 'all') {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        $availableRoles = $this->getAvailableRoles();
        $stats = $this->getUserStatistics();

        return view('admin.users.index', compact(
            'users', 
            'stats', 
            'availableRoles', 
            'search', 
            'role'
        ));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $this->authorize('create', User::class);
        
        $availableRoles = $this->getAvailableRoles();
        
        return view('admin.users.create', compact('availableRoles'));
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telephone' => 'nullable|string|max:20',
            'role' => 'required|string|in:' . implode(',', array_keys($this->getAvailableRoles())),
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Afficher un utilisateur
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        
        $user->load('roles');
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Afficher le formulaire de modification
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        
        $availableRoles = $this->getAvailableRoles();
        $currentRole = $user->getRoleNames()->first();
        
        // ✅ Utilisation du fichier modifier.blade.php
        return view('admin.users.modifier', compact('user', 'availableRoles', 'currentRole'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
            'role' => 'required|string|in:' . implode(',', array_keys($this->getAvailableRoles())),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'telephone' => $request->telephone,
        ]);

        $oldRole = $user->getRoleNames()->first();
        if ($oldRole !== $request->role) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($user->email === 'admin@btl.ma') {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer l\'administrateur principal.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->authorize('resetPassword', $user);

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()
            ->with('success', 'Mot de passe réinitialisé avec succès.');
    }

    /**
     * Exporter les utilisateurs
     */
    public function export(Request $request)
    {
        $this->authorize('export', User::class);

        $users = User::with('roles')->get();
        
        $data = $users->map(function ($user) {
            return [
                'Nom' => $user->name,
                'Email' => $user->email,
                'Téléphone' => $user->telephone ?? 'N/A',
                'Rôle' => $user->getRoleNames()->first(),
                'Date création' => $user->created_at->format('d/m/Y'),
            ];
        });

        $filename = "utilisateurs_btl_" . date('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Récupérer les rôles disponibles
     */
    private function getAvailableRoles(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return [];
        }
        
        $allRoles = User::getBankRoles();
        
        if ($user->hasRole('admin')) {
            return $allRoles;
        }
        
        if ($user->hasRole('chef-agence')) {
            return array_filter($allRoles, function ($key) {
                return in_array($key, ['chargee', 'backoffice']);
            }, ARRAY_FILTER_USE_KEY);
        }
        
        return [];
    }

    /**
     * Statistiques utilisateurs
     */
    private function getUserStatistics(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return [
                'total' => 0,
                'by_role' => [],
                'last_update' => now()->format('d/m/Y H:i'),
            ];
        }
        
        $total = User::count();
        
        $byRole = [];
        foreach (array_keys(User::getBankRoles()) as $role) {
            $count = User::byRole($role)->count();
            if ($count > 0) {
                $byRole[$role] = $count;
            }
        }

        return [
            'total' => $total,
            'by_role' => $byRole,
            'last_update' => now()->format('d/m/Y H:i'),
        ];
    }
}