<?php

namespace App\Mail;

use App\Models\TestDrive;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class NewTestDriveMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TestDrive $testDrive) {}

    public function envelope(): Envelope
    {
        $replyTo = filled($this->testDrive->email)
            ? [new Address($this->testDrive->email, $this->testDrive->customer_name)]
            : [];

        return new Envelope(
            subject: 'New Test Drive #'.$this->testDrive->id.' — '.$this->testDrive->car_name,
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        $photoUrl = $this->testDrive->photo
            ? Storage::disk('public')->url($this->testDrive->photo)
            : null;

        return new Content(
            view: 'emails.new-test-drive',
            with: [
                'logoUrl' => rtrim(config('app.media_url'), '/').'/logo-dark.jpeg',
                'photoUrl' => $photoUrl,
                'dashboardUrl' => rtrim(config('app.url'), '/').'/admin/test-drives/'.$this->testDrive->id,
            ],
        );
    }
}
