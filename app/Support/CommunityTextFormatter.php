<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class CommunityTextFormatter
{
    public function format(string $text, Collection $mentionUsers): HtmlString
    {
        $pattern = '/\[([^\]\r\n]{1,120})\]\(((?:https?:\/\/|\/)[^\s)]+)\)|\*\*([^*\r\n]+)\*\*|(?<!\*)\*([^*\r\n]+)\*(?!\*)|(?<![\pL\pN._])@([a-z0-9._]{3,30})/iu';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL);

        $html = '';
        $cursor = 0;

        foreach ($matches as $match) {
            [$token, $offset] = $match[0];
            $html .= e(substr($text, $cursor, $offset - $cursor));

            if ($match[1][0] !== null) {
                $label = $match[1][0];
                $url = $match[2][0];
                $isRelativeInternal = str_starts_with($url, '/') && ! str_starts_with($url, '//');
                $isValidAbsolute = filter_var($url, FILTER_VALIDATE_URL)
                    && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);

                if (! $isRelativeInternal && ! $isValidAbsolute) {
                    $html .= e($token);
                } else {
                    $href = $isRelativeInternal ? url($url) : $url;
                    $appHost = mb_strtolower((string) parse_url(url('/'), PHP_URL_HOST));
                    $linkHost = mb_strtolower((string) parse_url($href, PHP_URL_HOST));
                    $isInternal = $isRelativeInternal || ($appHost !== '' && $appHost === $linkHost);
                    $attributes = $isInternal
                        ? ' data-community-modal-link'
                        : ' target="_blank" rel="noopener noreferrer"';
                    $html .= '<a href="'.e($href).'" class="community-inline-link"'.$attributes.'>'.e($label).'</a>';
                }
            } elseif ($match[3][0] !== null) {
                $html .= '<strong>'.e($match[3][0]).'</strong>';
            } elseif ($match[4][0] !== null) {
                $html .= '<em>'.e($match[4][0]).'</em>';
            } else {
                $username = mb_strtolower($match[5][0]);
                $mentionedUser = $mentionUsers->get($username);
                $html .= $mentionedUser
                    ? '<a href="'.e(route('profile.show', $mentionedUser->username)).'" class="community-user-link">@'.e($match[5][0]).'</a>'
                    : e($token);
            }

            $cursor = $offset + strlen($token);
        }

        $html .= e(substr($text, $cursor));

        return new HtmlString($html);
    }
}
