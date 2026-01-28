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
    Schema::create('sequence_steps', function (Blueprint $table) {
      $table->id();
      $table->foreignId('sequence_id')->constrained('sequences')->cascadeOnDelete();
      $table->unsignedInteger('step_order');
      $table->unsignedInteger('delay_days')->default(0);
      $table->string('subject', 190);
      $table->longText('body_html')->nullable();
      $table->longText('body_text')->nullable();
      $table->boolean('is_active')->default(true)->index();
      $table->timestamps();

      $table->unique(['sequence_id', 'step_order']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('sequence_steps');
  }
};
