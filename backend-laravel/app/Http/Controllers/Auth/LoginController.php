<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Valide les données soumises dans le formulaire.
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required'    => 'Le champ Email ou Contact est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);
    }

    /**
     * Tente de connecter l'utilisateur en vérifiant l'email OU le contact.
     */
    protected function attemptLogin(Request $request)
    {
        $login = $request->input('login');
        $password = $request->input('password');

        // Déterminer la colonne de la BDD : email ou contact
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'contact';

        // Tenter la connexion avec Auth::attempt
        return Auth::attempt(
            [$field => $login, 'password' => $password],
            $request->boolean('remember')
        );
    }

    /**
     * Nom du champ du formulaire pour la gestion du rate limiting (anti-brute force).
     */
    public function username()
    {
        return 'login';
    }

    /**
     * Redirection après connexion réussie, selon le rôle de l'utilisateur.
     * Remplace l'ancienne propriété $redirectTo = '/home' (supprimée).
     */
    protected function authenticated(Request $request, $user)
    {
        return redirect()->route(match ($user->role) {
            'super_admin' => 'super-admin.dashboard',
            'admin'       => 'admin.dashboard',
            default       => 'dashboard',
        });
    }
}