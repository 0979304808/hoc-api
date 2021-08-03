<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    protected $table = 'posts';
    protected $fillable = [
        'user_id',
        'title',
        'image',
        'description'
    ];
    public function user() {
        return $this->belongsToMany(User::class);
    }
}
