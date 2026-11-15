<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfilCommu extends Model
{
    use HasFactory;
    protected $fillable = ['likes','user_id'];

    protected $table = 'profil_commus';

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class, 'profil_commus_id');
    }

    public function likers()
    {
        return $this->belongsToMany(User::class, 'profil_commu_likes', 'profil_commu_id', 'user_id');
    }

    /**
     * Vérifie si un utilisateur donné (ou l'utilisateur authentifié) a liké ce post.
     * @param int|null $userId L'ID de l'utilisateur à vérifier. Si null, utilise l'utilisateur authentifié.
     * @return bool
     */
    public function isLikedByUser(?int $userId = null): bool
    {
        if (is_null($userId) && Auth::check()) {
            $userId = Auth::id();
        }

        // Si aucun utilisateur authentifié ou ID fourni, il ne peut pas avoir été liké
        if (is_null($userId)) {
            return false;
        }

        // Vérifie si une entrée existe dans la table pivot pour cet utilisateur et ce post
        return $this->likers()->where('user_id', $userId)->exists();
    }

}
