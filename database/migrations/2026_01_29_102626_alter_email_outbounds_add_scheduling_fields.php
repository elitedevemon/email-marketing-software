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
      if (!Schema::hasColumn('email_outbounds', 'send_at')) {
        $table->timestamp('send_at')->nullable()->after('queued_at');
      }
      if (!Schema::hasColumn('email_outbounds', 'dispatched_at')) {
        $table->timestamp('dispatched_at')->nullable()->after('send_at');
      }
      if (!Schema::hasColumn('email_outbounds', 'delivery_domain')) {
        $table->string('delivery_domain', 191)->nullable()->after('dispatched_at');
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
