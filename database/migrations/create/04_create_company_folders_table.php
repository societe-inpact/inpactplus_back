<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_folders', function (Blueprint $table) {
            $table->id();
            $table->string('folder_number');
            $table->string('folder_name');
            $table->string('siret', 14);
            $table->string('siren', 9);
            $table->string('notes', 255)->nullable();
            $table->string('telephone', 10)->nullable();

            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('interface_id')->constrained('interfaces')->cascadeOnDelete();
            $table->foreignId('referent_id')->nullable()->constrained('users')->cascadeOnDelete();
        });

        DB::table('company_folders')->insert([
            [
                'id' => 1,
                'folder_number' => '2376284',
                'folder_name' => 'Bron',
                'siret' => '78234',
                'siren' => '782348754',
                'telephone' => '0123456789',
                'notes' => '',
                'company_id' => 3,
                'interface_id' => 1,
                'referent_id' => 1,
            ],
            [
                'id' => 2,
                'folder_number' => '854306',
                'folder_name' => 'Bourgoin-Jallieu',
                'siret' => '17869',
                'siren' => '178698689',
                'telephone' => '0123456789',
                'notes' => '',
                'company_id' => 3,
                'interface_id' => 1,
                'referent_id' => 1,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_entities');
    }
};
