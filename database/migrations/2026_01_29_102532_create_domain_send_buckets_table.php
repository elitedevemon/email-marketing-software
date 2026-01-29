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
    Schema::create('domain_send_buckets', function (Blueprint $table) {
      $table->id();
      $table->string('domain', 191);
      $table->dateTime('bucket_at'); // startOfMinute (UTC)
      $table->unsignedInteger('sent_count')->default(0);
      $table->timestamps();

      $table->unique(['domain', 'bucket_at']);
      $table->index(['bucket_at']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('domain_send_buckets');
  }
};
