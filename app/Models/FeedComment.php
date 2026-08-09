<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedComment extends Model
{
    protected $fillable = ['feed_post_id', 'user_id', 'body', 'status'];
    public function post() { return $this->belongsTo(FeedPost::class, 'feed_post_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
