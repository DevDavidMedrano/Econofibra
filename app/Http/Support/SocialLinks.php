<?php

declare(strict_types=1);

namespace App\Http\Support;

/**
 * Keeps the social networks that can be shown: https addresses whose host has a domain.
 */
final class SocialLinks
{
    private const HOST_WITH_DOMAIN = '/^([a-z0-9-]+\.)+[a-z][a-z0-9-]+$/i';

    /**
     * @param  array<string, mixed>  $urls
     * @return array<string, string>
     */
    public static function configured(array $urls): array
    {
        return array_filter($urls, self::isConfigured(...));
    }

    private static function isConfigured(mixed $url): bool
    {
        if (! is_string($url) || ! str_starts_with($url, 'https://')) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && preg_match(self::HOST_WITH_DOMAIN, $host) === 1;
    }
}
