<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Citoyen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    public function inscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'contact' => 'required|string|max:20',
            'relation' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:citoyens,email',
            'mot_de_passe' => 'required|string|min:8',
            'mot_de_passe_confirmation' => 'required|string|same:mot_de_passe',
            'cin_recto_base64' => 'required|string',
            'cin_verso_base64' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Données invalides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $cheminRecto = $this->enregistrerImageCin($request->cin_recto_base64, 'cin_recto');
        $cheminVerso = $this->enregistrerImageCin($request->cin_verso_base64, 'cin_verso');

        if (!$cheminRecto || !$cheminVerso) {
            return response()->json(['message' => 'Format d\'image CIN non reconnu.'], 400);
        }

        $citoyen = Citoyen::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'adresse' => $request->adresse,
            'contact' => $request->contact,
            'relation' => $request->relation,
            'email' => $request->email,
            'password' => Hash::make($request->mot_de_passe),
            'cin_recto' => $cheminRecto,
            'cin_verso' => $cheminVerso,
        ]);

        $token = $citoyen->createToken('token_citoyen')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie.',
            'token' => $token,
            'utilisateur' => [
                'id' => $citoyen->id_citoyens,
                'nom' => $citoyen->nom,
                'prenom' => $citoyen->prenom,
                'email' => $citoyen->email,
                'adresse' => $citoyen->adresse,
                'contact' => $citoyen->contact,
                'type' => 'citoyen',
            ],
        ], 201);
    }

    public function connexion(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mot_de_passe' => 'required|string',
        ]);

        $citoyen = Citoyen::where('email', $request->email)->first();

        if (!$citoyen || !Hash::check($request->mot_de_passe, $citoyen->getAuthPassword())) {
            return response()->json(['message' => 'Email ou mot de passe incorrect.'], 401);
        }

        $token = $citoyen->createToken('token_citoyen')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'token' => $token,
            'utilisateur' => [
                'id' => $citoyen->id_citoyens,
                'nom' => $citoyen->nom,
                'prenom' => $citoyen->prenom,
                'email' => $citoyen->email,
                'adresse' => $citoyen->adresse,
                'contact' => $citoyen->contact,
                'type' => 'citoyen',
            ],
        ]);
    }

     public function changerMotDePasse(Request $request)
    {
        try {
            // Validation des données
            $request->validate([
                'ancienMotDePasse' => 'required|string',
                'nouveauMotDePasse' => 'required|string|min:6|confirmed',
            ], [
                'ancienMotDePasse.required' => 'L\'ancien mot de passe est obligatoire.',
                'nouveauMotDePasse.required' => 'Le nouveau mot de passe est obligatoire.',
                'nouveauMotDePasse.min' => 'Le nouveau mot de passe doit contenir au moins 6 caractères.',
                'nouveauMotDePasse.confirmed' => 'Les mots de passe ne correspondent pas.',
            ]);

             // Récupérer l'utilisateur connecté
            $utilisateur = Auth::user();

            // Vérifier l'ancien mot de passe
            if (!Hash::check($request->ancienMotDePasse, $utilisateur->mot_de_passe)) {
                return response()->json([
                    'message' => 'L\'ancien mot de passe est incorrect.'
                ], 401);
            }
             // Mettre à jour le mot de passe
            $utilisateur->mot_de_passe = Hash::make($request->nouveauMotDePasse);
            $utilisateur->save();

            return response()->json([
                'message' => 'Mot de passe changé avec succès !'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du changement de mot de passe.'
            ], 500);
        }
    }

    public function profil(Request $request)
    {
        $utilisateur = $request->user();

        return response()->json([
            'utilisateur' => [
                'id' => $utilisateur->id_citoyens,
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'adresse' => $utilisateur->adresse,
                'contact' => $utilisateur->contact,
                'type' => 'citoyen',
            ],
        ]);
    }
   
     /**
     * Déconnecter l'utilisateur
     */
    public function deconnecter(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Déconnecté avec succès.'
        ], 200);
    }

    private function enregistrerImageCin($base64, $prefixe)
    {
        if (!is_string($base64) || !preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            return null;
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $donneesImage = base64_decode(substr($base64, strpos($base64, ',') + 1));

        if ($donneesImage === false) {
            return null;
        }

        $nomFichier = $prefixe . '_' . Str::uuid() . '.' . $extension;
        \Storage::disk('public')->put('cin/' . $nomFichier, $donneesImage);

        return 'cin/' . $nomFichier;
    }
}
