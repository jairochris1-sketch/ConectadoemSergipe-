<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedPollVote extends Model
{
    protected $fillable = ['feed_post_id', 'feed_poll_option_id', 'user_id'];
    public function post() { return $this->belongsTo(FeedPost::class, 'feed_post_id'); }
    public function option() { return $this->belongsTo(FeedPollOption::class, 'feed_poll_option_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
