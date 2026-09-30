<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Source lists
    |--------------------------------------------------------------------------
    |
    | Absolute paths to the PHP files returning the RSS and YouTube source
    | lists. By default the bundled resources are used. A host application
    | may publish the resources (tag: carol-resources) and/or point these
    | paths anywhere it likes.
    |
    */

    'sources' => [
        'rss' => __DIR__ . '/../resources/search/rss.php',
        'youtube' => __DIR__ . '/../resources/search/youtube.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Crawl cache
    |--------------------------------------------------------------------------
    |
    | Number of hours a downloaded feed is cached before being fetched again.
    |
    */

    'cache' => [
        'ttl' => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Crawl flags
    |--------------------------------------------------------------------------
    |
    | Percentage chance (0-100) that a crawled item is marked as unsafe.
    |
    */

    'crawl' => [
        'unsafe_percentage' => 5,
    ],

];
