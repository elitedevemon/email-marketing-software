<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OutboundMailable extends Mailable
{
  use Queueable, SerializesModels;

  /**
   * Create a new message instance.
   */
  public function __construct(
    public string $subject,
    public string $html = '',
    public string $text = '',
  ) {
    //
  }

  public function build()
  {
    $m = $this->subject($this->subject);

    if ($this->html !== '') {
      $m->html($this->html);
    }

    if ($this->text !== '') {
      $m->text('mail.raw-text', ['text' => $this->text]);
    }
    return $m;
  }
}
