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
    Schema::create('senders', function (Blueprint $table) {
      $table->id();

      $table->string('name', 120);
      $table->string('from_name', 120);
      $table->string('from_email', 190)->unique();
      $table->boolean('is_active')->default(true)->index();

      $table->unsignedInteger('daily_limit')->default(50);
      $table->unsignedInteger('sent_today')->default(0);
      $table->date('sent_today_date')->nullable()->index();
      $table->time('window_start')->default('09:00');
      $table->time('window_end')->default('18:00');
      $table->string('timezone', 64)->default('Asia/Dhaka');
      $table->unsignedInteger('jitter_min_seconds')->default(30);
      $table->unsignedInteger('jitter_max_seconds')->default(180);

      $table->string('smtp_host', 190);
      $table->unsignedInteger('smtp_port')->default(587);
      $table->string('smtp_encryption', 10)->default('tls'); // none|ssl|tls
      $table->string('smtp_username', 190);
      $table->text('smtp_password'); // encrypted cast; may exceed 255

      $table->string('imap_host', 190)->nullable();
      $table->unsignedInteger('imap_port')->nullable();
      $table->string('imap_encryption', 10)->nullable(); // none|ssl|tls
      $table->string('imap_username', 190)->nullable();
      $table->text('imap_password')->nullable(); // encrypted cast

      $table->timestamp('last_sent_at')->nullable()->index();

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('senders');
  }
};
