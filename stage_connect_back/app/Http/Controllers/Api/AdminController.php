<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Filiere;
use App\Models\Etudiant; 
use App\Models\Entreprise; 
use App\Models\Etablissement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
  

    /**
     * Affiche la liste de tous les utilisateurs avec leurs profils associés.
     * Inclut spécifiquement les étudiants et les entreprises via leurs relations User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexUsers()
    {
        
        $users = User::with(['etudiant', 'entreprise'])->get();
        return response()->json($users, 200);
    }

    public function countByUsers()
    {
        $etudiantCount = 0;
        $entrepriseCount = 0;

        $etudiantCount = Etudiant::count(); 
        $entrepriseCount = Entreprise::count();
        
        
        return response()->json([
        'etudiantCount' => $etudiantCount,
        'entrepriseCount' => $entrepriseCount,
        ]);
    }

    /**
     * Affiche les détails d'un utilisateur spécifique.
     * Permet de visualiser les profils d'étudiants ou d'entreprises.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showUser($id)
    {
        // Charge les relations pour afficher le profil complet.
        // Etablissement est retiré.
        $user = User::with(['etudiant', 'entreprise','etudiant.etablissement','etudiant.filiere','etudiant.annee','entreprise.domaine','entreprise.ville'])->find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        return response()->json($user, 200);
    }

    /**
     * Met à jour le rôle d'un utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserRole(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role' => ['required', 'string', Rule::in([ 'etudiant', 'entreprise'])], // Roles autorisés
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        // Empêcher un admin de changer son propre rôle via cette route pour éviter de se bloquer.
        if ($user->id === auth()->id() && $request->role !== 'admin') {
            return response()->json(['message' => 'Vous ne pouvez pas changer votre propre rôle depuis cette interface.'], 403);
        }

        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'Rôle utilisateur mis à jour avec succès.', 'user' => $user], 200);
    }

    /**
     * Active le compte d'un utilisateur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function activateUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        // Empêcher un admin d'activer/désactiver son propre compte pour éviter de se bloquer.
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Vous ne pouvez pas activer/désactiver votre propre compte administrateur.'], 403);
        }

        $user->is_active = true;
        $user->save();

        return response()->json(['message' => 'Compte utilisateur activé avec succès.', 'user' => $user], 200);
    }

    /**
     * Désactive le compte d'un utilisateur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deactivateUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        // Empêcher un admin d'activer/désactiver son propre compte pour éviter de se bloquer.
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Vous ne pouvez pas activer/désactiver votre propre compte administrateur.'], 403);
        }

        $user->is_active = false;
        $user->save();

        return response()->json(['message' => 'Compte utilisateur désactivé avec succès.', 'user' => $user], 200);
    }

    /**
     * Supprime un utilisateur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        // Empêcher un admin de se supprimer lui-même
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte administrateur.'], 403);
        }

        // Gérer la suppression des profils associés (Etudiant, Entreprise)
        // en utilisant les relations pour une suppression en cascade si non géré par la base de données.
        // Par exemple: $user->etudiant()->delete(); $user->entreprise()->delete();
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès.'], 204);
    }

    
    /**
     * Affiche la liste de toutes les filières.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexFilieres()
    {
        $filieres = Filiere::all(); // Vous pouvez charger des relations ici si nécessaire (ex: domaine)
        return response()->json($filieres, 200);
    }

    /**
     * Crée une nouvelle filière.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeFiliere(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'libfil' => 'required|string|max:255|unique:filieres',
            // Ajoutez d'autres règles de validation si Filiere a plus de champs (ex: 'domaine_id')
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $filiere = Filiere::create($request->all());

        return response()->json(['message' => 'Filière créée avec succès.', 'filiere' => $filiere], 201);
    }

    /**
     * Met à jour une filière existante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateFiliere(Request $request, $id)
    {
        $filiere = Filiere::find($id);

        if (!$filiere) {
            return response()->json(['message' => 'Filière non trouvée.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'libfil' => ['sometimes', 'string', 'max:255', Rule::unique('filieres')->ignore($filiere->id)],
            // Ajoutez d'autres règles de validation si Filiere a plus de champs
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $filiere->update($request->all());

        return response()->json(['message' => 'Filière mise à jour avec succès.', 'filiere' => $filiere], 200);
    }

    /**
     * Supprime une filière.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyFiliere($id)
    {
        $filiere = Filiere::find($id);

        if (!$filiere) {
            return response()->json(['message' => 'Filière non trouvée.'], 404);
        }

        // Gérer les dépendances (ex: étudiants liés à cette filière)
        // Vous pouvez empêcher la suppression si des étudiants sont liés, ou les délier/supprimer en cascade
        $filiere->delete();

        return response()->json(['message' => 'Filière supprimée avec succès.'], 204);
    }

   /**
     * Affiche la liste de tous les établissements.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexEtablissements()
    {
        // Retourne tous les champs existants du modèle Etablissement
        $etablissements = Etablissement::all();
        return response()->json($etablissements, 200);
    }

    /**
     * Crée un nouvel établissement avec le nom principal.
     * Les autres champs du modèle Etablissement seront null ou à définir par défaut.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeEtablissement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom_etablissement' => 'required|string|max:255|unique:etablissements',
            // Les autres champs (siteweb, adresse, ville_id, etc.) ne sont pas requis ici pour la gestion simplifiée
            // et peuvent être nullables dans la base de données ou définis par d'autres processus.
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Seul le nom est utilisé pour la création simplifiée
        $etablissement = Etablissement::create([
            'nom_etablissement' => $request->nom_etablissement,
            // Les autres champs sont soit null, soit définis par un défaut dans la migration
            // ou nécessitent une logique supplémentaire si l'admin doit les renseigner plus tard.
            // Ex: 'user_id' => null, 'ville_id' => null, etc.
        ]);

        return response()->json(['message' => 'Établissement créé avec succès.', 'etablissement' => $etablissement], 201);
    }

    /**
     * Met à jour un établissement existant, principalement son nom.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEtablissement(Request $request, $id)
    {
        $etablissement = Etablissement::find($id);

        if (!$etablissement) {
            return response()->json(['message' => 'Établissement non trouvé.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom_etablissement' => ['sometimes', 'string', 'max:255', Rule::unique('etablissements')->ignore($etablissement->id)],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

      
        if ($request->has('nom_etablissement')) {
            $etablissement->nom_etablissement = $request->nom_etablissement;
            $etablissement->save();
        }

        return response()->json(['message' => 'Établissement mis à jour avec succès.', 'etablissement' => $etablissement], 200);
    }

    /**
     * Supprime un établissement.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyEtablissement($id)
    {
        $etablissement = Etablissement::find($id);

        if (!$etablissement) {
            return response()->json(['message' => 'Établissement non trouvé.'], 404);
        }

        $etablissement->delete();

        return response()->json(['message' => 'Établissement supprimé avec succès.'], 204);
    }
}