<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Add this line to use DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // IMPORTANT: You need to specify ALL existing ENUM values
            // plus the new one. The order can matter if you have
            // default values or specific logic based on order.
           $table->enum('statut', ['en_attente', 'acceptee', 'refusee','expiree','confirmee','desistement_demande'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // If rolling back, revert to the previous ENUM values.
            // Be careful if you have 'expiree' data already, it might cause issues.
            // You might need to convert/delete 'expiree' statuses first.
            $table->enum('statut', ['en_attente', 'acceptee', 'refusee','expiree','confirmee','desistement_demande'])->change();
        });
    }
};