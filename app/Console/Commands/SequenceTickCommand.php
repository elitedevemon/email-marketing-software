<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SequenceEngine;

class SequenceTickCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'sequence:tick {--limit=200} {--dry-run}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Queue due sequence emails (idempotent) and dispatch send jobs.';

  /**
   * Execute the console command.
   */
  public function handle(SequenceEngine $engine): int
  {
    $limit = (int) $this->option('limit');
    $dry = (bool) $this->option('dry-run');

    $m = $engine->queueDueEnrollments($limit, $dry);

    $this->line(sprintf(
      'sequence:tick ok | due=%d queued=%d pending=%d skipped=%d',
      $m['due'],
      $m['queued'],
      $m['pending'],
      $m['skipped'],
    ));

    return self::SUCCESS;
  }
}
