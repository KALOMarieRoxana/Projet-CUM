<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    // Mettre à jour les informations du compte (nom, email, contact)
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'contact' => ['required', 'string', 'max:20'],
        ]);

        $user->update($data);

        return back()->with('success', 'Vos informations ont été mises à jour avec succès.');
    }

    // Changer le mot de passe
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'mot_de_passe_actuel' => ['required', 'string'],
            'mot_de_passe'        => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->mot_de_passe_actuel, $user->password)) {
            return back()->withErrors(['mot_de_passe_actuel' => 'Le mot de passe actuel est incorrect.'])
                ->withInput()
                ->with('ouvrir_modal', 'password');
        }

        $user->update([
            'password' => Hash::make($request->mot_de_passe),
        ]);

        return back()->with('success', 'Votre mot de passe a été modifié avec succès.');
    }
}