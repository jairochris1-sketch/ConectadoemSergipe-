<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $contact) {}

    public function build(): self
    {
        return $this
            ->subject('Contato pelo site: '.$this->contact['subject'])
            ->replyTo($this->contact['email'], $this->contact['name'])
            ->view('emails.contact.message');
    }
}
