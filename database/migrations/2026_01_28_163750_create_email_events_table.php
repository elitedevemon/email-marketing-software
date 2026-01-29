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
    Schema::create('email_events', function (Blueprint $table) {
      $table->id();
      $table->uuid('outbound_uuid')->nullable();
      $table->unsignedBigInteger('client_id')->nullable();
      $table->unsignedBigInteger('sender_id')->nullable();
      $table->string('type', 20); // open|click (future: reply|bounce)
      $table->timestamp('occurred_at')->useCurrent();
      $table->string('ip', 45)->nullable();
      $table->text('user_agent')->nullable();
      $table->json('meta_json')->nullable();
      $table->timestamps();

      $table->index(['type', 'occurred_at']);
      $table->index(['outbound_uuid']);
      $table->index(['client_id']);
      $table->index(['sender_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('email_events');
  }
};
