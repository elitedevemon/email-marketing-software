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
    if (!Schema::hasTable('email_outbounds')) {
      return;
    }

    Schema::table('email_outbounds', function (Blueprint $table) {
      if (!Schema::hasColumn('email_outbounds', 'skipped_at')) {
        $table->timestamp('skipped_at')->nullable()->after('sent_at');
      }
      if (!Schema::hasColumn('email_outbounds', 'skip_reason')) {
        $table->string('skip_reason', 191)->nullable()->after('skipped_at');
      }
      if (!Schema::hasColumn('email_outbounds', 'rendered_html')) {
        $table->longText('rendered_html')->nullable()->after('skip_reason');
      }
      if (!Schema::hasColumn('email_outbounds', 'rendered_text')) {
        $table->longText('rendered_text')->nullable()->after('rendered_html');
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
