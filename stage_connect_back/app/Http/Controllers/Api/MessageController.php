<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * 
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        
        $participantIds = Message::selectRaw('DISTINCT CASE
                                        WHEN expediteur_id = ? THEN destinataire_id
                                        ELSE expediteur_id
                                    END as participant_id', [$user->id])
                               ->where(function (Builder $query) use ($user) {
                                   $query->where('expediteur_id', $user->id)
                                         ->orWhere('destinataire_id', $user->id);
                               })
                               ->pluck('participant_id');

        
        $participants = User::whereIn('id', $participantIds)
                            ->select('id', 'name','photo','role') 
                            ->with([
                                // Relations pour l'étudiant
                                'etudiant', // La relation directe vers le modèle Etudiant
                                'etudiant.filiere', // Relation de l'étudiant vers sa filière
                                'etudiant.etablissement', // Relation de l'étudiant vers son établissement
                                'etudiant.annee', // Relation de l'étudiant vers son année via la table pivot filannee

                                // Relations pour l'entreprise (assumons que c'est le modèle Etablissement)
                                'entreprise.domaine', // La relation directe vers le modèle Etablissement (utilisé pour 'entreprise')
                                'entreprise.ville', // Relation de l'établissement vers sa ville
                                // NOTE : La relation 'domaine' n'est pas définie dans votre fichier Etablissement.php fourni.
                                // Si 'entreprise->domaine' est nécessaire, vous devez ajouter :
                                // 'etablissement.domaine', // Assurez-vous que cette relation existe dans App\Models\Etablissement
                            ])
                            ->get()
                            ->keyBy('id');

        $conversations = [];
        foreach ($participantIds as $participantId) {
            $participant = $participants->get($participantId);

            if ($participant) {
                // Récupérer le dernier message de la conversation
                $lastMessage = Message::where(function (Builder $query) use ($user, $participantId) {
                                    $query->where('expediteur_id', $user->id)
                                          ->where('destinataire_id', $participantId);
                                })
                                ->orWhere(function (Builder $query) use ($user, $participantId) {
                                    $query->where('expediteur_id', $participantId)
                                          ->where('destinataire_id', $user->id);
                                })
                                ->latest()
                                ->first();

                // Récupérer le nombre de messages non lus envoyés par l'autre participant
                $unreadCount = Message::where('expediteur_id', $participantId)
                                      ->where('destinataire_id', $user->id)
                                      ->where('lu', false)
                                      ->count();


                $participantData = [
                'id'          => $participant->id,
                'name'        => $participant->name,
                'role'        => $participant->role,
                'photo' => $participant->photo ? url(Storage::url($participant->photo)) : null,


            ];

            if ($participant->role === 'etudiant' && $participant->etudiant) {
                
                $participantData['name'] = $participant->name;
                
                // Champs spécifiques à l'étudiant
                $participantData['matricule'] = $participant->etudiant->matricule;
                $participantData['projets'] = $participant->etudiant->projets;
                $participantData['competences'] = $participant->etudiant->competences; 
                $participantData['parcours'] = $participant->etudiant->parcours;
                
                
                $participantData['filiere'] = $participant->etudiant->filiere ? $participant->etudiant->filiere->toArray() : null;
                $participantData['etablissement'] = $participant->etudiant->etablissement ? $participant->etudiant->etablissement->toArray() : null;
                
                // Accès à l'année via filannee
                $participantData['annee_id'] = $participant->etudiant->annee_id ?? null;
                $participantData['annee_libelle'] = ($participant->etudiant->annee) 
                                                        ? $participant->etudiant->annee->libannee 
                                                        : null;
                $participantData['filiere_annee_complete'] = (
                    $participant->etudiant->filiere && 
                    $participant->etudiant->annee
                ) ? $participant->etudiant->filiere->libfil . ' - ' . $participant->etudiant->annee->libannee : null;

            } elseif ($participant->role === 'entreprise' && $participant->entreprise) { // J'assume 'etablissement' est votre modèle pour les entreprises
                // Redéfinir le 'name' de l'utilisateur par le nom de l'établissement
                $participantData['name'] = $participant->entreprise->nom_entreprise ?? $participant->name;

                // Champs spécifiques à l'entreprise
                $participantData['email_entreprise'] = $participant->entreprise->email_entreprise; 
                $participantData['siteweb'] = $participant->entreprise->siteweb;
                $participantData['adresse'] = $participant->entreprise->adresse; 
                
                // Objets liés
               
                $participantData['domaine'] = $participant->entreprise->domaine ? $participant->entreprise->domaine->toArray() : null;
                $participantData['ville'] = $participant->entreprise->ville ? $participant->entreprise->ville->toArray() : null;
            }

                $conversations[] = [
                    'participant'=> $participantData,
                    'last_message' => $lastMessage ? [
                        'id' => $lastMessage->id,
                        'contenu' => $lastMessage->contenu,
                        'created_at' => $lastMessage->created_at,
                        'is_my_message' => ($lastMessage->expediteur_id === $user->id),
                        'lu' => (bool) $lastMessage->lu,
                        'expediteur_id' => $lastMessage->expediteur_id,
                    ] : null,
                    'unread_count' => $unreadCount,


                ];

            }
        }

        // Trier les conversations par le plus récent message
        usort($conversations, function ($a, $b) {
            $dateA = $a['last_message'] ? $a['last_message']['created_at'] : null;
            $dateB = $b['last_message'] ? $b['last_message']['created_at'] : null;

            if ($dateA === null && $dateB === null) return 0;
            if ($dateA === null) return 1;
            if ($dateB === null) return -1;

            return strtotime($dateB) <=> strtotime($dateA);
        });

        return response()->json($conversations, 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'destinataire_id' => 'required|exists:users,id',
            'contenu' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->destinataire_id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous envoyer de message à vous-même.'], 400);
        }

        $message = Message::create([
            'expediteur_id' => $user->id,
            'destinataire_id' => $request->destinataire_id,
            'contenu' => $request->contenu,
            'lu' => false,
        ]);

        $message->load('expediteur', 'destinataire');

        return response()->json(['message' => 'Message envoyé avec succès', 'data' => $message], 201);
    }

    public function getConversation($id)
    {
        $currentUser = Auth::user();

        if (!$currentUser) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if ($currentUser->id == $id) {
            return response()->json(['error' => 'Cannot retrieve conversation with self.'], 400);
        }

        $otherUser = User::find($id);
        if (!$otherUser) {
            return response()->json(['error' => 'Other user not found.'], 404);
        }

        $messages = Message::where(function ($query) use ($currentUser, $id) {
                    $query->where('expediteur_id', $currentUser->id)
                          ->where('destinataire_id', $id);
                })
                ->orWhere(function ($query) use ($currentUser, $id) {
                    $query->where('expediteur_id', $id)
                          ->where('destinataire_id', $currentUser->id);
                })
                ->with(['expediteur', 'destinataire'])
                ->orderBy('created_at', 'asc')
                ->get();

        return response()->json($messages);
    }

    public function markAsRead(Message $message)
    {
        $user = Auth::user();

        if ($message->destinataire_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé. Vous ne pouvez pas marquer ce message comme lu.'], 403);
        }

        if (!$message->lu) {
            $message->update(['lu' => true]);
        }

        return response()->json(['message' => 'Message marqué comme lu avec succès.', 'data' => $message], 200);
    }

    public function destroy(Message $message)
    {
        $user = Auth::user();

        if ($message->expediteur_id !== $user->id && $message->destinataire_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé à supprimer ce message.'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message supprimé avec succès'], 204);
    }

    /**
     * Récupère le profil d'un utilisateur.
     * Utilisé quand il n'y a pas encore de messages pour déduire l'autre user.
     * Les champs 'photo' et 'is_online' sont retirés d'ici aussi pour la cohérence.
     *
     * @param  int  $id L'ID de l'utilisateur dont on veut le profil.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserProfile($id)
    {
        $user = User::select('id', 'name')->find($id); // Ne sélectionne que l'ID et le nom

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            // 'photo' et 'is_online' sont retirés ici
        ]);
    }
}