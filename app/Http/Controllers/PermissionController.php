<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super-admin']);
    }

    public function index()
    {
        $permissions = Permission::with('roles')->paginate(20);
        $roles = Role::all();
        
        return view('admin.permissions.index', compact('permissions', 'roles'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name',
            'guard_name' => 'sometimes|string|in:web,api',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', '✅ Permission créée avec succès.');
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        $roles = Role::all();
        
        return view('admin.permissions.edit', compact('permission', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $permission->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', '✅ Permission mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        
        $criticalPermissions = [
            'view-dashboard', 'view-users', 'create-users', 'edit-users', 'delete-users',
            'view-swift-messages', 'create-swift-messages', 'view-roles', 'view-permissions'
        ];
        
        if (in_array($permission->name, $criticalPermissions)) {
            return redirect()->back()
                ->with('error', '❌ Cette permission système ne peut pas être supprimée.');
        }

        if ($permission->roles()->count() > 0) {
            return redirect()->back()
                ->with('error', '❌ Cette permission est assignée à des rôles. Retirez-la d\'abord.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', '✅ Permission supprimée avec succès.');
    }

    /**
     * ✅ SOLUTION CORRIGÉE - Accepte les IDs
     */
    public function assignToRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $role = Role::findById($request->role_id);
        $permissionIds = $request->permissions;
        
        // ✅ Récupérer les objets Permission par leurs IDs
        $permissions = Permission::whereIn('id', $permissionIds)->get();
        
        // ✅ Synchroniser avec les objets Permission
        $role->syncPermissions($permissions);
        
        $count = count($permissionIds);

        return redirect()->route('admin.permissions.index')
            ->with('success', "✅ {$count} permissions assignées au rôle '{$role->name}' avec succès.");
    }

    /**
     * ✅ API - Retourne les IDs des permissions d'un rôle
     */
    public function getRolePermissions(Request $request)
    {
        $roleId = $request->get('role_id');
        
        if (!$roleId) {
            return response()->json(['error' => 'Role ID requis'], 400);
        }
        
        $role = Role::findById($roleId);
        
        // ✅ Retourner les IDs
        $permissionIds = $role->permissions()->pluck('id')->toArray();
        
        return response()->json($permissionIds);
    }

    public function search(Request $request)
    {
        $search = $request->get('q', '');
        
        $permissions = Permission::where('name', 'like', "%{$search}%")
            ->limit(20)
            ->get(['id', 'name as text']);
        
        return response()->json($permissions);
    }

    public function duplicate($id)
    {
        $permission = Permission::findOrFail($id);
        
        $newName = $permission->name . '_copy';
        $counter = 1;
        
        while (Permission::where('name', $newName)->exists()) {
            $newName = $permission->name . '_copy' . $counter;
            $counter++;
        }
        
        Permission::create([
            'name' => $newName,
            'guard_name' => $permission->guard_name,
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', "✅ Permission dupliquée avec succès. Nouveau nom: {$newName}");
    }

    public function export()
    {
        $permissions = Permission::with('roles')->get();
        
        $filename = "permissions_" . date('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($permissions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Permission', 'Guard', 'Rôles', 'Date création']);
            
            foreach ($permissions as $perm) {
                $roles = $perm->roles->pluck('name')->implode(', ');
                
                fputcsv($file, [
                    $perm->id,
                    $perm->name,
                    $perm->guard_name,
                    $roles,
                    $perm->created_at->format('d/m/Y H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function syncFromConfig()
    {
        $defaultPermissions = [
            'view-dashboard', 'view-users', 'create-users', 'edit-users', 'delete-users',
            'view-roles', 'create-roles', 'edit-roles', 'delete-roles',
            'view-permissions', 'create-permissions', 'edit-permissions', 'delete-permissions',
            'view-swift-messages', 'create-swift-messages', 'edit-swift-messages', 
            'delete-swift-messages', 'import-swift-messages', 'export-swift-messages',
        ];
        
        $created = 0;
        foreach ($defaultPermissions as $permName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
            if ($permission->wasRecentlyCreated) {
                $created++;
            }
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', "✅ Synchronisation terminée. {$created} nouvelles permissions créées.");
    }
}