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
      if (!Schema::hasColumn('email_outbounds', 'jitter_applied_at')) {
        $table->timestamp('jitter_applied_at')->nullable()->after('scheduled_at');
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
