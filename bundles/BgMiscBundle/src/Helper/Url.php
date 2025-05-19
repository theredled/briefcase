<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 18/01/2022
 * Time: 12:39
 */

namespace Bg\MiscBundle\Helper;


class Url
{
    static public function changeUrlParams(string $uri, array $newVars, array $removeVars = []): string
    {
        $parts = parse_url($uri);
        $parts['query'] = isset($parts['query']) ? $parts['query'] : '';
        parse_str($parts['query'], $vars);
        $vars = array_merge($vars, $newVars);
        foreach ($removeVars as $value) {
            unset($vars[$value]);
        }
        $newUri = self::buildUrl($parts, $vars);
        return $newUri;
    }

    static public function buildUrl($parts, $vars = null)
    {
        $scheme   = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host     = isset($parts['host']) ? $parts['host'] : '';
        $port     = isset($parts['port']) ? ':' . $parts['port'] : '';
        $user     = isset($parts['user']) ? $parts['user'] : '';
        $pass     = isset($parts['pass']) ? ':' . $parts['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parts['path']) ? $parts['path'] : '';
        if ($vars)
            $query = '?'.http_build_query($vars);
        elseif ($vars === [])
            $query = '';
        else
            $query    = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
