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
    Schema::create('email_send_logs', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('email_outbound_id')->nullable();
      $table->uuid('outbound_uuid')->nullable();
      $table->unsignedBigInteger('client_id')->nullable();
      $table->unsignedBigInteger('sender_id')->nullable();

      $table->string('to_email', 191)->nullable();
      $table->string('subject', 255)->nullable();

      $table->string('status', 20); // success|failed|skipped|retrying
      $table->unsignedTinyInteger('attempt')->default(1);
      $table->integer('duration_ms')->nullable();
      $table->string('error_class', 191)->nullable();
      $table->text('error_message')->nullable();
      $table->json('meta_json')->nullable();

      $table->timestamp('created_at')->useCurrent();
      $table->timestamp('updated_at')->nullable();

      $table->index(['created_at']);
      $table->index(['status']);
      $table->index(['sender_id']);
      $table->index(['client_id']);
      $table->index(['outbound_uuid']);
      $table->index(['to_email']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('email_send_logs');
  }
};
