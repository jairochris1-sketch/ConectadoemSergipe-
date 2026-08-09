<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedPostReport extends Model
{
    protected $fillable = ['feed_post_id', 'reporter_user_id', 'reason', 'details', 'status', 'reviewed_by', 'reviewed_at'];
    protected function casts(): array { return ['reviewed_at' => 'datetime']; }
    public function post() { return $this->belongsTo(FeedPost::class, 'feed_post_id'); }
    public function reporter() { return $this->belongsTo(User::class, 'reporter_user_id'); }
}
