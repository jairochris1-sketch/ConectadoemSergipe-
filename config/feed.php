<?php

return [
    // staff_only agora; altere para eligible_profiles quando a comunidade for aberta aos usuários.
    'publishing_mode' => env('FEED_PUBLISHING_MODE', 'staff_only'),
    'image_moderation_driver' => env('FEED_IMAGE_MODERATION_DRIVER', 'manual'),
    'posts_per_hour' => (int) env('FEED_POSTS_PER_HOUR', 5),
    'images_per_post' => (int) env('FEED_IMAGES_PER_POST', 4),
    'video_max_kb' => (int) env('FEED_VIDEO_MAX_KB', 51200),
    'video_max_seconds' => (int) env('FEED_VIDEO_MAX_SECONDS', 60),
    'recommendations_enabled' => (bool) env('FEED_RECOMMENDATIONS_ENABLED', true),
    'recommended_ads_per_page' => (int) env('FEED_RECOMMENDED_ADS_PER_PAGE', 4),
    'ad_impressions_per_day' => (int) env('FEED_AD_IMPRESSIONS_PER_DAY', 3),
    'dismissal_days' => (int) env('FEED_AD_DISMISSAL_DAYS', 90),
    'guest_event_retention_days' => (int) env('FEED_GUEST_EVENT_RETENTION_DAYS', 30),
    'user_event_retention_days' => (int) env('FEED_USER_EVENT_RETENTION_DAYS', 90),
];
