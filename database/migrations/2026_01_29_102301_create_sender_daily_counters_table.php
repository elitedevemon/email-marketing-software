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
    Schema::create('sender_daily_counters', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('sender_id');
      $table->date('date');
      $table->unsignedInteger('sent_count')->default(0);
      $table->timestamp('last_sent_at')->nullable();
      $table->timestamps();

      $table->unique(['sender_id', 'date']);
      $table->index(['date']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('sender_daily_counters');
  }
};
