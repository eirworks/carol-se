<?php

use eirworks\carol\Lib\RSSParser;

it('parses rss 2.0 items into search items', function () {
    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
  <channel>
    <title>Example feed</title>
    <item>
      <title>First story</title>
      <link>https://example.com/first</link>
      <description><![CDATA[<p>Breaking news. Read Full Article at RT.com</p>]]></description>
      <media:content url="https://example.com/first.jpg" />
    </item>
    <item>
      <title>Second story</title>
      <link>https://example.com/second</link>
      <description>Continue reading...Plain text</description>
    </item>
  </channel>
</rss>
XML;

    $items = (new RSSParser())->rssToSearchItem($xml);

    expect($items)->toHaveCount(2)
        ->and($items[0]['title'])->toBe('First story')
        ->and($items[0]['url'])->toBe('https://example.com/first')
        ->and($items[0]['content'])->toBe('Breaking news. ')
        ->and($items[0]['image'])->toBe('https://example.com/first.jpg')
        ->and($items[1]['title'])->toBe('Second story')
        ->and($items[1]['content'])->toBe('Plain text')
        ->and($items[1])->not->toHaveKey('image');
});

it('parses atom entries with media groups', function () {
    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
  <entry>
    <title>Atom title</title>
    <link href="https://example.com/atom" />
    <media:group>
      <media:title>Media title</media:title>
      <media:description>Media description &amp; more</media:description>
      <media:thumbnail url="https://example.com/thumb.jpg" />
    </media:group>
  </entry>
</feed>
XML;

    $items = (new RSSParser())->rssToSearchItem($xml);

    expect($items)->toHaveCount(1)
        ->and($items[0]['title'])->toBe('Media title')
        ->and($items[0]['content'])->toBe('Media description & more')
        ->and($items[0]['image'])->toBe('https://example.com/thumb.jpg')
        ->and($items[0]['url'])->toBe('https://example.com/atom');
});

it('falls back to the entry link when no media group is present', function () {
    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <entry>
    <title>No media</title>
    <link href="https://example.com/plain" />
    <summary>Summary text</summary>
  </entry>
</feed>
XML;

    $items = (new RSSParser())->rssToSearchItem($xml);

    expect($items)->toHaveCount(1)
        ->and($items[0]['url'])->toBe('https://example.com/plain')
        ->and($items[0])->not->toHaveKey('image');
});
