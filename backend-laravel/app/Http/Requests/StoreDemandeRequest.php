<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeRequest extends FormRequest
{
    public function authorize()
    {
        return true; // L'authentification est gérée par le middleware
    }

    public function rules()
    {
        return [
            'demandeur_nom' => 'required|string|max:255',
            'demandeur_prenom' => 'required|string|max:255',
            'demandeur_adresse' => 'required|string|max:255',
            'demandeur_relation' => 'nullable|string|max:100',
            'demandeur_contact' => 'required|string|max:20',
            'personne_nom' => 'required|string|max:255',
            'personne_prenom' => 'required|string|max:255',
            'personne_numero_acte' => 'nullable|string|max:50',
            'personne_lieu_naissance' => 'required|string|max:255',
            'personne_date_naissance' => 'required|date|before:today',
            'type_acte' => 'required|string|in:naissance,mariage,deces,divorce',
            'service' => 'required|string|in:standard,express',
        ];
    }

    public function messages()
    {
        return [
            'demandeur_nom.required' => 'Le nom du demandeur est obligatoire.',
            'demandeur_prenom.required' => 'Le prénom du demandeur est obligatoire.',
            'demandeur_adresse.required' => 'L\'adresse du demandeur est obligatoire.',
            'demandeur_contact.required' => 'Le contact du demandeur est obligatoire.',
            'personne_nom.required' => 'Le nom de la personne concernée est obligatoire.',
            'personne_prenom.required' => 'Le prénom de la personne concernée est obligatoire.',
            'personne_lieu_naissance.required' => 'Le lieu de naissance est obligatoire.',
            'personne_date_naissance.required' => 'La date de naissance est obligatoire.',
            'personne_date_naissance.before' => 'La date de naissance doit être dans le passé.',
            'type_acte.required' => 'Le type d\'acte est obligatoire.',
            'type_acte.in' => 'Le type d\'acte sélectionné est invalide.',
            'service.required' => 'Le service est obligatoire.',
            'service.in' => 'Le service sélectionné est invalide.',
        ];
    }
}