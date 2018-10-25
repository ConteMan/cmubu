<?php
/**
 * Date: 2018/10/24
 * Time: 22:14
 */

return [
    'username'     => env('CMUBU_USERNAME', ''),
    'password'     => env('CMUBU_PASSWORD', ''),
    'cache_prefix' => env('CMUBU_CACHE_PREFIX', 'cmubu_cache:'),
    'redis_config' => env('CMUBU_REDIS_CONFIG', 'default'),
];