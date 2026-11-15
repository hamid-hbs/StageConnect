<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Offre;
use App\Models\Entreprise;
use App\Models\Etablissement; 
use App\Models\Candidature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Assurez-vous que Carbon est bien importé

class OffreController extends Controller
{
    /**
     * Récupère les statistiques des offres pour l'entreprise authentifiée.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        $user = Auth::user();
        $totalOffres = 0;
        $activeOffres = 0;
        $expiredOffres = 0;
        $pendingOffres = 0; // Pour les entreprises, les offres 'en attente' de publication (variable non utilisée mais conservée)

        if ($user->role === 'entreprise' && $user->entreprise) {
            $entrepriseId = $user->entreprise->id;
            $totalOffres = Offre::where('entreprise_id', $entrepriseId)->count();
            $activeOffres = Offre::where('entreprise_id', $entrepriseId)
                                 ->where('statut', 'active')
                                 ->where('date_expiration', '>=', now())
                                 ->count();
            // Les offres expirées sont celles dont la date d'expiration est passée OU dont le statut est 'expiree'
            $expiredOffres = Offre::where('entreprise_id', $entrepriseId)
                                 ->where(function ($query) {
                                     $query->where('date_expiration', '<', now())
                                           ->orWhere('statut', 'expiree');
                                 })
                                 ->count();

            return response()->json([
                'total_offres' => $totalOffres,
                'active_offres' => $activeOffres,
                'expired_offres' => $expiredOffres,
                'message' => 'Statistiques des offres pour votre entreprise.',
            ], 200);

        }
        // Si l'utilisateur n'est pas une entreprise ou n'a pas de profil entreprise
        return response()->json(['message' => 'Non autorisé. Seules les entreprises peuvent voir leurs statistiques.'], 403);
    }

    /**
     * Liste les offres de l'entreprise authentifiée.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function mesOffres()
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est bien une entreprise
        if ($user->role !== 'entreprise') {
            return response()->json(['message' => 'Non autorisé. Seules les entreprises peuvent voir leurs propres offres.'], 403);
        }

        $entreprise = $user->entreprise;

        if (!$entreprise) {
            return response()->json(['message' => 'Profil entreprise non complet.'], 400);
        }
        $today = Carbon::today();

        // Met à jour le statut des offres expirées pour cette entreprise
        Offre::where('entreprise_id', $entreprise->id) // S'assurer de ne mettre à jour que les offres de cette entreprise
             ->where('date_expiration', '<', $today)
             ->where('statut', '!=', 'expiree') 
             ->update(['statut'=>'expiree']);

        // Récupère toutes les offres de cette entreprise
        $offres = Offre::where('entreprise_id', $entreprise->id)
                       ->with('domaine')->get();

        return response()->json($offres, 200);
    }
   
    /**
     * Liste les offres en fonction du rôle de l'utilisateur ou toutes les offres si non authentifié.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today(); // Correction : Définir $today ici

        // Met à jour le statut des offres expirées globalement avant de les lister
        Offre::whereDate('date_expiration', '<=', $today) // Compare seulement la partie date
             ->where('statut', '!=', 'expiree') 
             ->update(['statut'=>'expiree']);

        if ($user) {
            // Correction : Initialiser $query avant les conditions de rôle
            $query = Offre::with('entreprise', 'domaine');

            // Si l'utilisateur est un étudiant, filtrer les offres en fonction des partenariats (logique à compléter si nécessaire)
            if ($user->role === 'etudiant' && $user->etudiant) {
                $etablissementId = $user->etudiant->etablissement_id;
                // Logique de filtrage des offres pour les étudiants.
                // Si l'objectif est de montrer toutes les offres actives aux étudiants,
                // la ligne suivante est suffisante après l'initialisation de $query.
                // Si un filtrage par partenariat est nécessaire, il faudrait une relation
                // entre Offre/Entreprise et Etablissement.
                $offres = $query->where('statut', 'active')->get();
            } elseif ($user->role === 'entreprise' && $user->entreprise) {
                // Si l'utilisateur est une entreprise, lister seulement ses offres
                $offres = $query->where('entreprise_id', $user->entreprise->id)->get();
            } else {
                // Si l'utilisateur est authentifié mais n'a pas un rôle pertinent ou un profil complet
                return response()->json(['message' => 'Non autorisé à lister les offres ou profil non pertinent.'], 403);
            }

            return response()->json($offres, 200);
        } else {
            // Si aucun utilisateur n'est authentifié, lister toutes les offres actives
            $offres = Offre::with('entreprise','domaine')->where('statut', 'active')->get();
            return response()->json($offres, 200);
        }
    }

    /**
     * Affiche les détails d'une offre spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        // 1. Récupérer l'offre spécifique
        $offre = Offre::with('entreprise','domaine')->find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre non trouvée.'], 404);
        }

        // 2. Récupérer le nombre de candidatures pour cette offre
        $nombreCandidatures = Candidature::where('offre_id', $offre->id)->count();

        // 3. Initialiser hasApplied à false par défaut
        $hasApplied = false;

        // Vérifier si un utilisateur est authentifié
        if (Auth::check()) {
            $user = Auth::user();

            // Vérifier si l'utilisateur est un étudiant et a un profil d'étudiant
            if ($user->role === 'etudiant' && $user->etudiant) {
                // Correction : Vérifier si l'étudiant a déjà postulé à cette offre spécifique
                $hasApplied = Candidature::where('etudiant_id', $user->etudiant->id)
                                         ->where('offre_id', $offre->id)
                                         ->exists();
            }
        }

        return response()->json([
            'offre' => $offre, // L'objet offre complet
            'nombre_candidatures' => $nombreCandidatures, // Le compte que vous avez récupéré
            'has_applied' => $hasApplied,
            'message' => 'Détails de l\'offre et nombre de candidatures récupérés avec succès.'
        ]);
    }

    /**
     * Crée une nouvelle offre d'emploi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'entreprise' || !$user->entreprise) {
            return response()->json(['message' => 'Seules les entreprises peuvent créer des offres.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:191',
            'description' => 'required|string',
            'domaine_id' => 'required|exists:domaines,id',
            'adresse' => 'required|string|max:191',
            // Correction : date_expiration doit être après aujourd'hui
            'date_expiration' => 'required|date|after:today', 
            'duree_en_semaines' => 'nullable|integer|min:1',
            // Correction : date_debut doit être après ou égal à aujourd'hui
            'date_debut' => 'required|date|after_or_equal:today', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $offre = Offre::create([
            'entreprise_id' => $user->entreprise->id,
            'titre' => $request->titre,
            'description' => $request->description,
            'domaine_id' => $request->domaine_id,
            'adresse' => $request->adresse,
            'date_expiration' => $request->date_expiration,
            'duree_en_semaines' => $request->duree_en_semaines,
            'date_debut' => $request->date_debut,
            'statut' => 'active', // Les nouvelles offres sont actives par défaut
        ]);

        return response()->json(['message' => 'Offre créée avec succès.', 'data' => $offre], 201);
    }

    /**
     * Met à jour une offre existante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $offre = Offre::find($id);
        if (!$offre) {
            return response()->json(['message' => 'Offre non trouvée.'], 404); // Correction: 404 si non trouvé
        }

        // Correction : Vérifier si l'utilisateur est une entreprise ET si l'offre lui appartient
        if ($user->role !== 'entreprise' || !$user->entreprise || $offre->entreprise_id !== $user->entreprise->id) {
            return response()->json(['message' => 'Non autorisé à modifier cette offre.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'statut' => 'sometimes|required|in:active,expiree',
            // Ajoutez d'autres champs ici si vous voulez permettre leur modification via cette méthode
            // 'titre' => 'sometimes|string|max:191',
            // 'description' => 'sometimes|string',
            // ...
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $offre->update($request->all());

        return response()->json($offre, 200);
    }

    /**
     * Supprime une offre spécifique.
     *
     * @param  \App\Models\Offre  $offre
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Offre $offre)
    {
        $user = Auth::user();

        // Vérification stricte : rôle entreprise, profil entreprise, et l'offre appartient à cette entreprise
        if ($user->role !== 'entreprise' || !$user->entreprise || $offre->entreprise_id !== $user->entreprise->id) {
            return response()->json(['message' => 'Non autorisé à supprimer cette offre.'], 403);
        }

        $offre->delete();

        return response()->json(['message' => 'Offre supprimée avec succès.'], 204);
    }




// OFFFLINE


    public function index_offline(Request $request)
    {
        
        $today = Carbon::today(); // Correction : Définir $today ici

        // Met à jour le statut des offres expirées globalement avant de les lister
        Offre::whereDate('date_expiration', '<=', $today) // Compare seulement la partie date
             ->where('statut', '!=', 'expiree') 
             ->update(['statut'=>'expiree']);

        
            $offres = Offre::with('entreprise','domaine')->where('statut', 'active')->get();
            return response()->json($offres, 200);
        
    }


    
    /**
     * Affiche les détails d'une offre spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show_offline(int $id)
    {
        // 1. Récupérer l'offre spécifique
        $offre = Offre::with('entreprise','domaine')->find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre non trouvée.'], 404);
        }

        // 2. Récupérer le nombre de candidatures pour cette offre
        $nombreCandidatures = Candidature::where('offre_id', $offre->id)->count();

        // 3. Initialiser hasApplied à false par défaut
        $hasApplied = false;

        // Vérifier si un utilisateur est authentifié
        
        

        return response()->json([
            'offre' => $offre, // L'objet offre complet
            'nombre_candidatures' => $nombreCandidatures, // Le compte que vous avez récupéré
            'has_applied' => $hasApplied,
            'message' => 'Détails de l\'offre et nombre de candidatures récupérés avec succès.'
        ]);
    }

}
