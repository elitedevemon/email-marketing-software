<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sequence;
use App\Models\SequenceStep;

class DefaultSequenceSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $seq = Sequence::query()->firstOrCreate(
      ['key' => 'default_outreach'],
      ['name' => 'Default Outreach', 'description' => 'Baseline 4-step outreach sequence', 'is_active' => true],
    );

    $steps = [
      ['step_order' => 1, 'delay_days' => 0, 'subject' => 'Quick proposal idea for {{business_name}}', 'body_text' => "Hi {{business_name}},\n\nI noticed your business in {{city}}. I can share a quick proposal idea.\n\n— {{sender_name}}"],
      ['step_order' => 2, 'delay_days' => 2, 'subject' => 'Following up (2 mins)', 'body_text' => "Just following up—want me to send a short breakdown?\n\n— {{sender_name}}"],
      ['step_order' => 3, 'delay_days' => 3, 'subject' => 'One more idea for improvement', 'body_text' => "Here’s another improvement idea tailored for {{business_name}}.\n\n— {{sender_name}}"],
      ['step_order' => 4, 'delay_days' => 3, 'subject' => 'Last follow-up', 'body_text' => "Final follow-up. If not relevant, I won’t email again.\n\n— {{sender_name}}"],
    ];

    foreach ($steps as $s) {
      SequenceStep::query()->updateOrCreate(
        ['sequence_id' => $seq->id, 'step_order' => $s['step_order']],
        [
          'delay_days' => $s['delay_days'],
          'subject' => $s['subject'],
          'body_text' => $s['body_text'],
          'body_html' => null,
          'is_active' => true,
        ],
      );
    }
  }
}
