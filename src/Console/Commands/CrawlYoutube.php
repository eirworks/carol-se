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

        $channelHash = md5($channel['channel']);
        $content = Cache::get('youtube_' . $channelHash);

        if (! $content) {
            $this->warn('|- Content not available in cache');
            $this->line('|- Crawling RSS...');
            $content = Http::get($channelUrl)->body();
            Cache::put('youtube_' . $channelHash, $content, now()->addHours((int) config('carol.cache.ttl', 6)));
        } else {
            $this->line('|- Content retrieved from cache');
        }

        $searchItems = $this->rssParser->rssToSearchItem($content);

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
