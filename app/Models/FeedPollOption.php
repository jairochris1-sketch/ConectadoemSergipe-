<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedPollOption extends Model
{
    protected $fillable = ['feed_post_id', 'label', 'position'];
    public function post() { return $this->belongsTo(FeedPost::class, 'feed_post_id'); }
    public function votes() { return $this->hasMany(FeedPollVote::class); }
}
