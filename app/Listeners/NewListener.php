<?php

namespace App\Listeners;

use App\Events\NewEvent;
use App\Mail\NewMail;
use Illuminate\Support\Facades\Mail;

class NewListener
{

    public function handle(NewEvent $event)
    {
        Mail::to($event->user->email)->send(new NewMail($event->user));
    }
}
