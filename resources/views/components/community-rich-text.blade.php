@props(['text', 'mentionUsers' => collect()])

{!! app(\App\Support\CommunityTextFormatter::class)->format((string) $text, $mentionUsers) !!}
