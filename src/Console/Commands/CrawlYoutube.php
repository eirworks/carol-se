<?php

namespace eirworks\carol\Console\Commands;

use eirworks\carol\Console\Concerns\MarksUnsafeItems;
use eirworks\carol\Lib\RSSParser;
use eirworks\carol\Models\SearchItem;
use eirworks\carol\Support\Sources;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CrawlYoutube extends Command
{
    use MarksUnsafeItems;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:youtube';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl selected youtube channels';

    public function __construct(
        protected RSSParser $rssParser,
        protected Sources $sources,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $youtubeChannels = $this->sources->youtube();

        foreach ($youtubeChannels as $channel) {
            $this->crawlChannel($channel);
        }
    }

    /**
     * Crawl a single YouTube channel feed.
     *
     * @param  array<string, mixed>  $channel
     */
    protected function crawlChannel(array $channel): void
    {
        $channelUrl = 'https://www.youtube.com/feeds/videos.xml?channel_id=' . $channel['channel'];

        $this->line("Channel: {$channel['name']}");
        $this->line("|- Channel ID: {$channel['channel']}");
        $this->line("|- Channel URL: {$channelUrl}");

        $cacheKey = 'youtube_' . md5($channel['channel']);
        $content = Cache::get($cacheKey);

        if (! $content) {
            $this->warn('|- Content not available in cache');
            $this->line('|- Crawling RSS...');

            try {
                $content = Http::get($channelUrl)->throw()->body();
            } catch (\Exception $e) {
                $this->error("|- Failed to crawl channel feed: {$e->getMessage()}");

                return;
            }

            if (trim($content) === '') {
                $this->error('|- Received an empty feed, skipping.');

                return;
            }

            Cache::put($cacheKey, $content, now()->addHours((int) config('carol.cache.ttl', 6)));
        } else {
            $this->line('|- Content retrieved from cache');
        }

        try {
            $searchItems = $this->rssParser->rssToSearchItem($content);
        } catch (\Exception $e) {
            // A previously cached error page would otherwise keep failing forever.
            Cache::forget($cacheKey);

            $this->error("|- Unable to process result: {$e->getMessage()}");

            return;
        }

        foreach ($searchItems as $result) {
            $result['created_at'] = now()->toDateTimeString();
            $result['updated_at'] = now()->toDateTimeString();
            $result['unsafe'] = $this->isUnsafe();
            $result['video'] = $result['image'];
            $result['topic'] = $channel['topic'];
            $result['has_image'] = ! empty($result['image']);
            $result['has_video'] = ! empty($result['video']);

            SearchItem::query()->insert($result);
        }
    }
}
