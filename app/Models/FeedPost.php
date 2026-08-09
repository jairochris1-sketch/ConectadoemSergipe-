<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedPost extends Model
{
    protected $fillable = ['user_id', 'body', 'video_path', 'video_url', 'video_duration_seconds', 'city', 'type', 'title', 'notice_level', 'topic', 'text_alignment', 'is_pinned', 'pinned_at', 'expires_at', 'poll_ends_at', 'content_hash', 'status', 'moderation_reason', 'reviewed_by', 'reviewed_at', 'published_at'];

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean', 'pinned_at' => 'datetime', 'expires_at' => 'datetime', 'reviewed_at' => 'datetime', 'published_at' => 'datetime', 'poll_ends_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function images() { return $this->hasMany(FeedPostImage::class)->orderBy('position'); }
    public function likes() { return $this->belongsToMany(User::class, 'feed_post_likes')->withTimestamps(); }
    public function comments() { return $this->hasMany(FeedComment::class); }
    public function reports() { return $this->hasMany(FeedPostReport::class); }
    public function pollOptions() { return $this->hasMany(FeedPollOption::class)->orderBy('position'); }
    public function pollVotes() { return $this->hasMany(FeedPollVote::class); }
}
