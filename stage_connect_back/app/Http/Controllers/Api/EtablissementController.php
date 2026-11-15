<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EtablissementController extends Controller
{

    /**
     
     * @return \Illuminate\Http\JsonResponse
     *  @param  \Illuminate\Http\Request  $request
     */
    // GET /api/etablissements
    public function index()
    {
        $etablissements = Etablissement::all();
        return response()->json($etablissements);
    }

    

    // GET /api/etablissements/{id}
    public function show($id)
    {
        $etablissement = Etablissement::find($id);

        if (!$etablissement) {
            return response()->json(['message' => 'Etablissement non trouvé'], 404);
        }
        
        return response()->json($etablissement, 200);
    }

    // PUT /api/etablissements/{id}
    public function update(Request $request, $id)
    {
        $etablissement = Etablissement::find($id);


        
        $validator = Validator::make($request->all(), [
            'nom_etablissement' => 'sometimes|string|max:255',
            
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $etablissement->update($request->all());

        return response()->json(['message' => 'Etablissement mis à jour avec succès', 'etablissement' => $etablissement], 200);
    }

    // DELETE /api/etablissements/{id}
    public function destroy($id)
    {
        $etablissement = Etablissement::find($id);

        if (!$etablissement) {
            return response()->json(['message' => 'Etablissement non trouvé'], 404);
        }

        if ($etablissement->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $etablissement->delete();

        return response()->json(['message' => 'Etablissement supprimé avec succès'], 200);
    }

    
}
