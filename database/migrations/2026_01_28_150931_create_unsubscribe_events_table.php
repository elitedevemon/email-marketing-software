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
    Schema::create('unsubscribe_events', function (Blueprint $table) {
      $table->id();
      $table->string('email', 191);
      $table->unsignedBigInteger('client_id')->nullable();
      $table->uuid('outbound_uuid')->nullable();
      $table->string('ip', 45)->nullable();
      $table->text('user_agent')->nullable();
      $table->timestamps();

      $table->index(['email']);
      $table->index(['client_id']);
      $table->index(['outbound_uuid']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('unsubscribe_events');
  }
};
