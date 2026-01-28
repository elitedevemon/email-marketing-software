<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('clients', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->unique();

      $table->string('business_name', 160);
      $table->string('contact_name', 160)->nullable();
      $table->string('email', 190)->unique();
      $table->string('website_url', 255)->nullable();

      $table->string('city', 120)->nullable()->index();
      $table->string('country', 120)->nullable();

      $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
      $table->string('status', 30)->default('prospect')->index();

      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('clients');
  }
};
