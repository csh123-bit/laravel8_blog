<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Posts extends Model{
    protected $guarded=[];

    public function comments(){
        return $this->hasMany('App\Comments','on_post');
    }

    public function author(){
        return $this ->belongsTo('App\User', 'author_id');
    }
}

