<?php

namespace LookstarKernel\Application\Tenant\System\Feedback;

use LookstarKernel\Application\Tenant\System\Feedback\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Mail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $feedback;
    public $user;
    public $score;
    public $text;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('上帝来信啦')->view('emails.feedback.feedback');
    }
}
