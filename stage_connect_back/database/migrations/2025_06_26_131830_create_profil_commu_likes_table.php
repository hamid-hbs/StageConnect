<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profil_commu_likes', function (Blueprint $table) {
            // Clé étrangère pour l'utilisateur qui like
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Assurez-vous que votre table 'users' est correcte

            // Clé étrangère pour le post ProfilCommu liké
            $table->foreignId('profil_commu_id')->constrained('profil_commus')->onDelete('cascade');

            // Clé primaire composée pour garantir qu'un utilisateur ne peut liker un post qu'une seule fois
            $table->primary(['user_id', 'profil_commu_id']);

            $table->timestamps(); // Pour enregistrer la date et l'heure du like
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_commu_likes');
    }
};