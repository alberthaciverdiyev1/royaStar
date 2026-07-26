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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->index();
            $table->foreignId('teacher_id')->nullable()->index();
            $table->foreignId('family_id')->nullable()->index();
            $table->foreignId('student_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('start_date')->comment('Subscription start date');
            $table->date('expires_at')->comment('Subscription end date');
            $table->string('status')->index();
            $table->index('start_date');
            $table->index('expires_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
