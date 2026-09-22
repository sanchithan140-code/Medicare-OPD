<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HospitalMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $mailSubject,
        public readonly string $heading,
        public readonly array $lines,
        public readonly ?string $actionText = null,
        public readonly ?string $actionUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        $paragraphs = [];
        $details = [];
        $greeting = null;
        $code = null;

        foreach ($this->lines as $line) {
            if ($greeting === null && str_starts_with($line, 'Hello')) {
                $greeting = $line;
            } elseif (preg_match('/^Your one-time password is (\d+)\.$/', $line, $match)) {
                $code = $match[1];
            } elseif (preg_match('/^(Reference|Patient ID|Doctor|Specialty|Date and time|Status|Current status|Test|Laboratory order|Invoice|Payment reference|Payment|Amount|Method):\s*(.*)$/u', $line, $match)) {
                $details[] = ['label' => $match[1], 'value' => $match[2]];
            } else {
                $paragraphs[] = $line;
            }
        }

        return new Content(
            view: 'emails.hospital-message',
            text: 'emails.hospital-message-text',
            with: compact('greeting', 'paragraphs', 'details', 'code'),
        );
    }
}
