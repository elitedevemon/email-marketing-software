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
    Schema::create('email_outbounds', function (Blueprint $table) {
      $table->id();
      $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
      $table->foreignId('sender_id')->nullable()->constrained('senders')->nullOnDelete();
      $table->foreignId('sequence_enrollment_id')->constrained('sequence_enrollments')->cascadeOnDelete();
      $table->foreignId('sequence_step_id')->constrained('sequence_steps')->cascadeOnDelete();
      $table->string('subject', 190);
      $table->longText('body_html')->nullable();
      $table->longText('body_text')->nullable();
      $table->string('status', 20)->default('pending')->index(); // pending|queued|sending|sent|failed|cancelled
      $table->timestamp('scheduled_at')->nullable()->index();
      $table->timestamp('queued_at')->nullable()->index();
      $table->timestamp('sent_at')->nullable()->index();
      $table->unsignedInteger('attempts')->default(0);
      $table->string('last_error', 1000)->nullable();
      $table->timestamps();

      $table->unique(['sequence_enrollment_id', 'sequence_step_id'], 'uniq_outbound_enrollment_step');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('email_outbounds');
  }
};
