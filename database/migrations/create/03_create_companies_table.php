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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description', 120);
            $table->foreignId('referent_id')->constrained('users')->cascadeOnDelete();
        });
        DB::table('companies')->insert([
            [
                'id' => 1,
                'name' => 'McDonalds',
                'description' => 'Description du groupe McDonalds',
                'referent_id' => 1
            ],
            [
                'id' => 2,
                'name' => 'Burger King',
                'description' => 'Description du groupe Burger King',
                'referent_id' => 1
            ],
            [
                'id' => 3,
                'name' => 'VaPiano',
                'description' => 'Description du groupe VaPiano',
                'referent_id' => 1
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
