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
    if (!Schema::hasTable('sequence_enrollments')) {
      return;
    }

    Schema::table('sequence_enrollments', function (Blueprint $table) {
      if (!Schema::hasColumn('sequence_enrollments', 'stop_reason')) {
        $table->string('stop_reason', 191)->nullable()->after('stopped_at');
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
