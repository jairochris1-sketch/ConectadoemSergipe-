<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedPostImage extends Model
{
    protected $fillable = ['feed_post_id', 'path', 'file_hash', 'position', 'moderation_status', 'moderation_labels'];
    protected function casts(): array { return ['moderation_labels' => 'array']; }
    public function post() { return $this->belongsTo(FeedPost::class, 'feed_post_id'); }
}
