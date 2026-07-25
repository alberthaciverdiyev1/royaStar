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
            $table->json('question')->comment('Question of test');
            $table->json('variant_a')->nullable()->comment('Variant A of question');
            $table->json('variant_b')->nullable()->comment('Variant B of question');
            $table->json('variant_c')->nullable()->comment('Variant C of question');
            $table->json('variant_d')->nullable()->comment('Variant D of question');
            $table->json('variant_e')->nullable()->comment('Variant E of question');
            $table->string('right_answer')->nullable()->comment('Right answer of question');
            $table->json('open_answer')->nullable()->comment('Answer of open question');
            $table->string('type')->comment('Type of question')->index();
            $table->json('explanation')->nullable()->comment('Explanation of question');
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
