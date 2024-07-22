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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        DB::table('modules')->insert([
            [
                'id' => 1,
                'name' => 'convert',
            ],
            [
                'id' => 2,
                'name' => 'statistics',
            ],
            [
                'id' => 3,
                'name' => 'history',
            ],
            [
                'id' => 4,
                'name' => 'employee_management',
            ],
            [
                'id' => 5,
                'name' => 'admin_panel',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
