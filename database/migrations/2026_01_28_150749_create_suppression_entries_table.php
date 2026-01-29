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
    Schema::create('suppression_entries', function (Blueprint $table) {
      $table->id();
      $table->string('email', 191)->unique();
      $table->string('reason', 191)->nullable();
      $table->string('source', 50)->default('manual'); // manual|unsubscribe|bounce|admin|import
      $table->unsignedBigInteger('client_id')->nullable();
      $table->json('meta_json')->nullable();
      $table->timestamps();

      $table->index(['source']);
      $table->index(['client_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('suppression_entries');
  }
};
