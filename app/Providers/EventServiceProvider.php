<?php

namespace App\Providers;

use App\Events\NewEvent;
use App\Events\NewNotification;
use App\Listeners\NewListener;
use App\Listeners\NotificationListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        NewEvent::class => [
            NewListener::class,
            NotificationListener::class

        ],
        NewNotification::class => [
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
