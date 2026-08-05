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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('question')->comment('Question content-block array');
            $table->text('variant_a')->nullable()->comment('Variant A content-block array');
            $table->text('variant_b')->nullable()->comment('Variant B content-block array');
            $table->text('variant_c')->nullable()->comment('Variant C content-block array');
            $table->text('variant_d')->nullable()->comment('Variant D content-block array');
            $table->text('variant_e')->nullable()->comment('Variant E content-block array');
            $table->string('right_answer')->nullable()->comment('Right answer of question');
            $table->text('open_answer')->nullable()->comment('Answer of open question');
            $table->string('type')->comment('Type of question')->index();
            $table->text('explanation')->nullable()->comment('Explanation content-block array');
            $table->integer('difficulty_level')->comment('Difficulty of question')->index();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
