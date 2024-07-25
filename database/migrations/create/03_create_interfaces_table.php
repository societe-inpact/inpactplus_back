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
        Schema::create('interfaces', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
        });
        DB::table('interfaces')->insert([
            [
                'id' => 1,
                'name' => 'Marathon',
            ],
            [
                'id' => 2,
                'name' => 'RHIS',
            ],
            [
                'id' => 3,
                'name' => 'SIRH',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interfaces');
    }
};
