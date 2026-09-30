<?php

use eirworks\carol\Support\Sources;

it('loads the bundled rss and youtube source lists', function () {
    $config = require dirname(__DIR__, 2) . '/config/carol.php';

    $sources = new Sources($config['sources']);

    expect($sources->rss())->toBeArray()->not->toBeEmpty()
        ->and($sources->rss()[0])->toHaveKeys(['name', 'url', 'topic'])
        ->and($sources->youtube())->toBeArray()->not->toBeEmpty()
        ->and($sources->youtube()[0])->toHaveKeys(['name', 'channel', 'topic']);
});

it('prefers host provided source files over bundled ones', function () {
    $directory = sys_get_temp_dir() . '/carol-sources-' . uniqid();
    mkdir($directory);

    file_put_contents($directory . '/rss.php', "<?php return [['name' => 'Host']];");

    try {
        $sources = new Sources(['rss' => __DIR__ . '/missing.php'], $directory);

        expect($sources->rss())->toBe([['name' => 'Host']]);
    } finally {
        unlink($directory . '/rss.php');
        rmdir($directory);
    }
});

it('throws when a source is not configured', function () {
    (new Sources())->rss();
})->throws(RuntimeException::class);

it('throws when a configured source file is missing', function () {
    (new Sources(['rss' => __DIR__ . '/missing.php']))->rss();
})->throws(RuntimeException::class);
