<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppUninstalledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reason, $client;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reason, $client_name)
    {
        $this->client = $client_name;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.app-uninstalled', [
            'reason' => $this->reason,
            'client' => $this->client
        ])->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    }

    
}
