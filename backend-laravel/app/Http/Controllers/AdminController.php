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
     * Afficher la liste des administrateurs
     */
    public function index()
    {
        $admins = User::whereIn('role', ['admin', 'super_admin'])
            ->orderBy('role', 'desc')
            ->get();
        
        return view('super-admin.admins.index', compact('admins'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('super-admin.admins-create');
    }

    /**
     * Enregistrer un nouvel administrateur
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'contact' => 'required|string|max:20',
            'role' => ['required', Rule::in(['admin', 'super_admin'])],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact' => $request->contact,
            'is_admin' => true,
            'role' => $request->role,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('super-admin.admins')
            ->with('success', 'Administrateur créé avec succès.');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $admin)
    {
        // Empêcher la modification d'un autre super admin
        if ($admin->role === 'super_admin' && $admin->id !== auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier un autre super administrateur.');
        }
        
        return view('super-admin.admins-edit', compact('admin'));
    }

    /**
     * Mettre à jour un administrateur
     */
    public function update(Request $request, User $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:20',
            'role' => ['required', Rule::in(['admin', 'super_admin'])],
        ]);

        // Empêcher la modification d'un autre super admin
        if ($admin->role === 'super_admin' && $admin->id !== auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier un autre super administrateur.');
        }

        $admin->update([
            'name' => $request->name,
            'contact' => $request->contact,
            'role' => $request->role,
        ]);

        return redirect()->route('super-admin.admins')
            ->with('success', 'Administrateur modifié avec succès.');
    }

    /**
     * Supprimer un administrateur
     */
    public function destroy(User $admin)
    {
        // Empêcher de se supprimer soi-même
        if ($admin->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Empêcher de supprimer un super admin
        if ($admin->role === 'super_admin') {
            return back()->with('error', 'Vous ne pouvez pas supprimer un super administrateur.');
        }

        $admin->delete();

        return redirect()->route('super-admin.admins')
            ->with('success', 'Administrateur supprimé avec succès.');
    }
}