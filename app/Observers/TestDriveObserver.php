<?php

namespace App\Observers;

use App\Mail\NewTestDriveMail;
use App\Models\TestDrive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TestDriveObserver
{
    public function created(TestDrive $testDrive): void
    {
        try {
            Mail::to('info@tgworld.com')->send(new NewTestDriveMail($testDrive));
        } catch (\Throwable $e) {
            Log::error('Failed to send new test drive email: '.$e->getMessage());
        }
    }
}
