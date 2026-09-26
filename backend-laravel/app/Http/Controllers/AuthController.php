<?php

namespace App\Http\Controllers;

use App\Models\Citoyen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * INSCRIPTION d'un citoyen + envoi de l'email de vérification
     */
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
            'email_verification_token' => Str::random(64),
        ]);

        $emailEnvoye = $this->envoyerEmailVerification($citoyen);

        // ⚠️ Le token de vérification n'est JAMAIS renvoyé dans la réponse
        return response()->json([
            'message' => $emailEnvoye
                ? 'Inscription réussie. Un email de confirmation vous a été envoyé.'
                : 'Inscription réussie, mais l\'email de confirmation n\'a pas pu être envoyé. Utilisez « Renvoyer l\'email » depuis la page de connexion.',
            'email_verification_requise' => true,
            'email_envoye' => $emailEnvoye,
            'utilisateur' => $this->formaterCitoyen($citoyen),
        ], 201);
    }

    /**
     * VÉRIFICATION de l'email (route publique, appelée par la page React)
     */
    public function verifierEmail(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $citoyen = Citoyen::where('email_verification_token', $request->token)->first();

        if (!$citoyen) {
            return response()->json(['message' => 'Lien de vérification invalide ou expiré.'], 404);
        }

        // Lien cliqué deux fois (ex. React.StrictMode) : on répond 200
        if ($citoyen->email_verified_at) {
            return response()->json(['message' => 'Cet email a déjà été vérifié.'], 200);
        }

        // On garde le token : un 2e appel tombera sur le test "déjà vérifié" ci-dessus
        $citoyen->forceFill(['email_verified_at' => now()])->save();

        return response()->json(['message' => 'Email vérifié avec succès.'], 200);
    }

    /**
     * RENVOYER l'email de vérification (route publique)
     */
    public function renvoyerVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $citoyen = Citoyen::where('email', $request->email)
            ->whereNull('email_verified_at')
            ->first();

        if ($citoyen) {
            $citoyen->forceFill(['email_verification_token' => Str::random(64)])->save();
            $this->envoyerEmailVerification($citoyen);
        }

        // Même réponse dans tous les cas : on ne révèle pas quels emails existent
        return response()->json([
            'message' => 'Si le compte existe et n\'est pas encore vérifié, un email a été envoyé.',
        ]);
    }

    /**
     * CONNEXION
     */
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

        if (!$citoyen->email_verified_at) {
            return response()->json([
                'message' => 'Veuillez vérifier votre adresse email avant de vous connecter.',
                'email_non_verifie' => true,
            ], 403);
        }

        if (!$citoyen->actif) {
            return response()->json([
                'message' => 'Votre compte a été désactivé. Contactez l\'administration.',
            ], 403);
        }

        $token = $citoyen->createToken('token_citoyen')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'token' => $token,
            'utilisateur' => $this->formaterCitoyen($citoyen),
        ]);
    }

    /**
     * CHANGER LE MOT DE PASSE (route protégée par auth:sanctum)
     */
    public function changerMotDePasse(Request $request)
    {
        try {
            $request->validate([
                'ancienMotDePasse' => 'required|string',
                'nouveauMotDePasse' => 'required|string|min:6|confirmed',
            ], [
                'ancienMotDePasse.required' => 'L\'ancien mot de passe est obligatoire.',
                'nouveauMotDePasse.required' => 'Le nouveau mot de passe est obligatoire.',
                'nouveauMotDePasse.min' => 'Le nouveau mot de passe doit contenir au moins 6 caractères.',
                'nouveauMotDePasse.confirmed' => 'Les mots de passe ne correspondent pas.',
            ]);

            $citoyen = $request->user();

            if (!$citoyen) {
                return response()->json(['message' => 'Citoyen non authentifié.'], 401);
            }

            if (!Hash::check($request->ancienMotDePasse, $citoyen->password)) {
                return response()->json(['message' => 'L\'ancien mot de passe est incorrect.'], 422);
            }

            $citoyen->password = Hash::make($request->nouveauMotDePasse);
            $citoyen->save();

            return response()->json(['message' => 'Mot de passe changé avec succès !'], 200);

        } catch (ValidationException $e) {
            $premiereErreur = collect($e->errors())->flatten()->first() ?? 'Erreur de validation.';
            return response()->json(['message' => $premiereErreur], 422);

        } catch (\Exception $e) {
            Log::error('Erreur changerMotDePasse: ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')');
            return response()->json(['message' => 'Une erreur est survenue.'], 500);
        }
    }

    /**
     * PROFIL du citoyen connecté
     */
    public function profil(Request $request)
    {
        return response()->json([
            'utilisateur' => $this->formaterCitoyen($request->user()),
        ]);
    }

    /**
     * DÉCONNEXION
     */
    public function deconnecter(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès.'], 200);
    }

    // ═══════════════════════════════════════════════════════════
    // MÉTHODES PRIVÉES
    // ═══════════════════════════════════════════════════════════

    /**
     * Envoie l'email de vérification. Retourne true si l'envoi a réussi.
     */
    private function envoyerEmailVerification(Citoyen $citoyen): bool
    {
        $front = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
        $lienVerification = $front . '/verifier-email?token=' . $citoyen->email_verification_token;

        try {
            Mail::send('verification-citoyen', [
                'citoyen' => $citoyen,
                'lienVerification' => $lienVerification,
            ], function ($message) use ($citoyen) {
                $message->to($citoyen->email)
                    ->subject('Confirmation de votre adresse e-mail');
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Erreur d'envoi mail à {$citoyen->email} : " . $e->getMessage());
            return false;
        }
    }

    private function formaterCitoyen(Citoyen $citoyen): array
    {
        return [
            'id' => $citoyen->id_citoyens,
            'nom' => $citoyen->nom,
            'prenom' => $citoyen->prenom,
            'email' => $citoyen->email,
            'adresse' => $citoyen->adresse,
            'contact' => $citoyen->contact,
            'type' => 'citoyen',
        ];
    }

    private function enregistrerImageCin($base64, $prefixe)
    {
        if (!is_string($base64) || !preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            return null;
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];

        if (!in_array($extension, ['jpg', 'png', 'webp'], true)) {
            return null;
        }

        $donneesImage = base64_decode(substr($base64, strpos($base64, ',') + 1), true);

        if ($donneesImage === false) {
            return null;
        }

        $nomFichier = $prefixe . '_' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put('cin/' . $nomFichier, $donneesImage);

        return 'cin/' . $nomFichier;
    }
}
