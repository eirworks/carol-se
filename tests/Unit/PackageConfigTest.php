<?php

it('exposes the expected configuration defaults', function () {
    $config = require dirname(__DIR__, 2) . '/config/carol.php';

    expect($config)->toHaveKeys(['sources', 'cache', 'crawl'])
        ->and($config['sources'])->toHaveKeys(['rss', 'youtube'])
        ->and($config['sources']['rss'])->toBeFile()
        ->and($config['sources']['youtube'])->toBeFile()
        ->and($config['cache'])->toHaveKey('ttl')
        ->and($config['crawl'])->toHaveKey('unsafe_percentage');
});

it('exposes an autoloadable service provider and crawler commands', function () {
    expect(class_exists(\eirworks\carol\CarolServiceProvider::class))->toBeTrue()
        ->and(class_exists(\eirworks\carol\Lib\RSSParser::class))->toBeTrue()
        ->and(class_exists(\eirworks\carol\Models\SearchItem::class))->toBeTrue()
        ->and(class_exists(\eirworks\carol\Support\Sources::class))->toBeTrue()
        ->and(class_exists(\eirworks\carol\Console\Commands\Crawl::class))->toBeTrue()
        ->and(class_exists(\eirworks\carol\Console\Commands\Crawler\CrawlRSS::class))->toBeTrue()
        ->and(class_exists(\eirworks\carol\Console\Commands\CrawlYoutube::class))->toBeTrue();
});

it('declares laravel auto discovery for the service provider', function () {
    $composer = json_decode(file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true);

    expect($composer['name'])->toBe('eirworks/carol')
        ->and($composer['extra']['laravel']['providers'] ?? [])
        ->toContain('eirworks\\carol\\CarolServiceProvider');
});
