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
        Schema::create('interface_mapping', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_number');
            $table->integer('rubric');
            $table->integer('value');
            $table->integer('start_date')->nullable();
            $table->integer('end_date')->nullable();
            $table->integer('hj')->nullable();
            $table->integer('percentage_tp')->nullable();
            $table->integer('period')->nullable();
            $table->string('separator_type');
            $table->string('extension');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interface_mapping');
    }
};
