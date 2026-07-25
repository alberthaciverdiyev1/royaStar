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
        Schema::create('subscription_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->nullOnDelete();
            $table->foreignId('new_subscription_plan_id')->constrained('subscription_plans')->nullOnDelete();
            $table->foreignId('old_subscription_plan_id')->constrained('subscription_plans')->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('family_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date')->comment('Subscription start date');
            $table->date('expires_at')->comment('Subscription end date');
            $table->decimal('price', 10, 2)->comment('Subscription price');
            $table->string('action')->index()->comment('Subscription action');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_histories');
    }
};
