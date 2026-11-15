<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\Annee; // Ajouté pour les opérations de pivot
use App\Models\Etablissement;
use App\Models\FilAnnee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FiliereController extends Controller
{
    /**

     * @return \Illuminate\Http\JsonResponse
     *  @param  \Illuminate\Http\Request  $request
     */
    public function index()
    {
        $filieres = Filiere::all();
        return response()->json($filieres, 200);
    }
}
    