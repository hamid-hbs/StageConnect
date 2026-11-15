<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Etablissement extends Model
{
    use HasFactory;
    protected $fillable = [
        
        'nom_etablissement',
        
        
    ];
    protected $table = 'etablissements';

    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function etudiants(){
        return $this->hasMany(Etudiant::class);
    }

    
}
