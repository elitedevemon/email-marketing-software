<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('sequence_enrollments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
      $table->foreignId('sequence_id')->constrained('sequences')->cascadeOnDelete();
      $table->string('status', 20)->default('active')->index(); // active|stopped|completed
      $table->unsignedInteger('current_step_order')->default(1);
      $table->timestamp('next_run_at')->nullable()->index();
      $table->timestamp('started_at')->nullable();
      $table->timestamp('stopped_at')->nullable();
      $table->string('stop_reason', 80)->nullable();
      $table->timestamps();

      $table->unique(['client_id', 'sequence_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('sequence_enrollments');
  }
};
