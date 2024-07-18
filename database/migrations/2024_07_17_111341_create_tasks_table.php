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
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('tk_id');
            $table->string('tk_subject');
            $table->text('tk_description');
            $table->date('tk_start_date');
            $table->date('tk_due_date');
            $table->enum('tk_status', ['new', 'incomplete','complete']);
            $table->enum('tk_priority', ['high', 'medium', 'low']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
