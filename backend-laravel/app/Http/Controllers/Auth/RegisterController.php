<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Citoyen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Afficher la page d'inscription
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Traiter l'inscription
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'contact'   => 'required|string|max:20',
            'role'      => 'required|in:citoyen,admin,super_admin',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        // Créer l'utilisateur
        $user = User::create([
            'name'     => $request->name,
            'prenom'   => $request->prenom,
            'email'    => $request->email,
            'contact'  => $request->contact,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        // ✅ Redirection selon le rôle
        if ($user->role === 'super_admin') {
            return redirect()->route('super-admin.dashboard');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
    }
}