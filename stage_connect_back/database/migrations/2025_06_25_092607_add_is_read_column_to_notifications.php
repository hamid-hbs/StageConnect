<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Ajoutez cette ligne pour le DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Ajoute la colonne 'is_read' de type booléen, non nulle par défaut et initialisée à false
            $table->boolean('is_read')->default(false);
            
            // OPTIONNEL : Mettre à jour les notifications existantes si nécessaire
                      // et celles qui n'en avaient pas comme non lues (is_read = false), vous pouvez faire ceci :
            

                    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });
    }
};