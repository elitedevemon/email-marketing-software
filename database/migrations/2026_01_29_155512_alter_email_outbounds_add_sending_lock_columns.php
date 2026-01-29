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
    if (!Schema::hasTable('email_outbounds'))
      return;

    Schema::table('email_outbounds', function (Blueprint $table) {
      if (!Schema::hasColumn('email_outbounds', 'sending_started_at')) {
        $table->timestamp('sending_started_at')->nullable()->after('status');
      }
      if (!Schema::hasColumn('email_outbounds', 'sending_lock_key')) {
        $table->string('sending_lock_key', 64)->nullable()->after('sending_started_at');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    //
  }
};
