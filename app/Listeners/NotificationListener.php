<?php

namespace App\Listeners;

use App\Notifications\NotificationAuth;
use Illuminate\Support\Facades\Notification;

class NotificationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Notification::send($event->user, new NotificationAuth($event->user));

    }
}
