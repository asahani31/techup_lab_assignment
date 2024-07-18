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
        Schema::create('notes', function (Blueprint $table) {
            $table->bigIncrements('nt_id');
            $table->unsignedBigInteger('task_id');
            $table->foreign('task_id')->references('tk_id')->on('tasks');
            $table->string('nt_subject');
            $table->text('nt_attachment');
            $table->text('nt_note');
            $table->timestamps();
        });
    }

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
        });
        Schema::dropIfExists('notes');
    }
};
