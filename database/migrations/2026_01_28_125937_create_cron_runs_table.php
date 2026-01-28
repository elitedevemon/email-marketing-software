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
    Schema::create('cron_runs', function (Blueprint $table) {
      $table->id();
      $table->string('status', 20)->index(); // ok|fail|skipped
      $table->unsignedInteger('duration_ms')->default(0);
      $table->string('ip', 64)->nullable();
      $table->string('user_agent', 255)->nullable();
      $table->longText('output')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('cron_runs');
  }
};
