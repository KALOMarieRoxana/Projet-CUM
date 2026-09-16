<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * LISTE DES ADMINISTRATEURS
     * ═══════════════════════════════════════════════════════════
     */
    public function index()
    {
        $admins = User::whereIn('role', ['admin', 'super_admin'])
            ->orderBy('role', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('super-admin.admins.index', compact('admins'));
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * FORMULAIRE DE CRÉATION (page complète)
     * ═══════════════════════════════════════════════════════════
     */
    public function create()
    {
        return view('super-admin.admins.create');
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * ENREGISTRER UN NOUVEAU ADMIN
     * ═══════════════════════════════════════════════════════════
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'contact'  => 'nullable|string|max:20',
            'role'     => ['required', Rule::in(['admin', 'super_admin'])],
        ], [
            'email.unique'     => 'Cet email est déjà utilisé.',
            'password.min'     => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'contact'           => $request->contact,
            'is_admin'          => true,
            'role'              => $request->role,
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('super-admin.admins.index')
            ->with('success', 'Administrateur créé avec succès.');
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * FORMULAIRE D'ÉDITION
     * ═══════════════════════════════════════════════════════════
     */
    public function edit($id)
    {
        $admin = User::findOrFail($id);

        // Empêcher la modification d'un autre super admin
        if ($admin->role === 'super_admin' && $admin->id !== auth()->id()) {
            return redirect()
                ->route('super-admin.admins.index')
                ->with('error', 'Vous ne pouvez pas modifier un autre super administrateur.');
        }

        return view('super-admin.admins.edit', compact('admin'));
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * METTRE À JOUR UN ADMIN
     * ═══════════════════════════════════════════════════════════
     */
    public function update(Request $request, $id)
    {
        $admin = User::findOrFail($id);

        // Empêcher la modification d'un autre super admin
        if ($admin->role === 'super_admin' && $admin->id !== auth()->id()) {
            return redirect()
                ->route('super-admin.admins.index')
                ->with('error', 'Vous ne pouvez pas modifier un autre super administrateur.');
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($admin->id)],
            'contact'  => 'nullable|string|max:20',
            'role'     => ['required', Rule::in(['admin', 'super_admin'])],
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'email.unique' => 'Cet email est déjà utilisé par un autre utilisateur.',
        ]);

        $data = [
            'name'    => $request->name,
            'email'   => $request->email,
            'contact' => $request->contact,
            'role'    => $request->role,
        ];

        // ✅ Mot de passe optionnel
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return redirect()
            ->route('super-admin.admins.index')
            ->with('success', 'Administrateur modifié avec succès.');
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * SUPPRIMER UN ADMIN
     * ═══════════════════════════════════════════════════════════
     */
    public function destroy($id)
    {
        $admin = User::findOrFail($id);

        // Empêcher de se supprimer soi-même
        if ($admin->id === auth()->id()) {
            return redirect()
                ->route('super-admin.admins.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Empêcher de supprimer un super admin
        if ($admin->role === 'super_admin') {
            return redirect()
                ->route('super-admin.admins.index')
                ->with('error', 'Vous ne pouvez pas supprimer un super administrateur.');
        }

        $admin->delete();

        return redirect()
            ->route('super-admin.admins.index')
            ->with('success', 'Administrateur supprimé avec succès.');
    }
}