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
            $table->foreignId('interface_software_id')->nullable()->constrained('interface_software')->cascadeOnDelete();
        });
        
        DB::table('interfaces')->insert([
            [
                'id' => 1,
                'name' => 'Marathon',
                'interface_software_id'=> null,
            ],
            [
                'id' => 2,
                'name' => 'RHIS',
                'interface_software_id'=> 1,
            ],
            [
                'id' => 3,
                'name' => 'SIRH',
                'interface_software_id'=> null,
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
