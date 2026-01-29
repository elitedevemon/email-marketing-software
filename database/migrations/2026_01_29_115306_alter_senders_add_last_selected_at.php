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
    if (!Schema::hasTable('senders'))
      return;
    Schema::table('senders', function (Blueprint $table) {
      if (!Schema::hasColumn('senders', 'last_selected_at')) {
        $table->timestamp('last_selected_at')->nullable()->after('updated_at');
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
