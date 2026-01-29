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
    Schema::create('outbound_links', function (Blueprint $table) {
      $table->id();
      $table->uuid('outbound_uuid');
      $table->string('hash', 32);
      $table->text('url');
      $table->timestamps();

      $table->unique(['outbound_uuid', 'hash']);
      $table->index(['hash']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('outbound_links');
  }
};
