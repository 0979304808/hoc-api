<?php

namespace App\Http\Controllers;

use App\Events\NewEvent;
use App\Mail\WelcomToMaill;
use App\Posts;
use App\User;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function test()
    {
        DB::transaction(function () {
            $post = Posts::create([
                'user_id' => 1 ,
                'title' => 'bai viet moi cua toi',
                'image' => 'abc',
            ]);
            event(new NewEvent($post));
            User::create([
                'name' => 'abc',
                'email' => 'gsadsad@gmail',
            ]);
            Posts::created();
        },5);
    }

}
