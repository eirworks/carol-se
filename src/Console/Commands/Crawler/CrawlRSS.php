<?php

namespace eirworks\carol\Console\Commands\Crawler;

use eirworks\carol\Console\Concerns\MarksUnsafeItems;
use eirworks\carol\Lib\RSSParser;
use eirworks\carol\Models\SearchItem;
use eirworks\carol\Support\Sources;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CrawlRSS extends Command
{
    use MarksUnsafeItems;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:rss';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl RSS from sources';

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
        $sources = $this->sources->rss();

        foreach ($sources as $source) {
            $md5SourceUrl = md5($source['url']);

            $this->line("Source: {$source['name']}");
            $this->line("Topic: {$source['topic']}");
            $this->line("|- Source URL: {$source['url']}");
            $this->line("|- Source URL Hash: {$md5SourceUrl}");

            $content = Cache::get('rss_' . $md5SourceUrl);

            if (! $content) {
                $this->warn('|- Content not available in cache');

                $this->line("|- Crawling RSS from {$source['url']}");
                try {
                    $content = Http::get($source['url'])->throw()->body();
                } catch (\Exception $e) {
                    $this->error("|- Failed to crawl RSS from {$source['url']}: " . $e->getMessage());
                    continue;
                }

                if (trim($content) === '') {
                    $this->error("|- Received an empty feed from {$source['url']}, skipping.");
                    continue;
                }

                Cache::put('rss_' . $md5SourceUrl, $content, $this->cacheTtl());
            } else {
                $this->line('|- Content retrieved from cache');
            }

            $this->line("|- Storing content from $md5SourceUrl");

            try {
                $searchResults = $this->rssParser->rssToSearchItem($content);

                foreach ($searchResults as $result) {
                    $result['topic'] = $source['topic'];
                    $result['created_at'] = now()->toDateTimeString();
                    $result['updated_at'] = now()->toDateTimeString();
                    $result['unsafe'] = $this->isUnsafe();
                    $result['has_image'] = ! empty($result['image']);

                    SearchItem::query()->insert($result);
                }
            } catch (\Exception $e) {
                // A previously cached error page would otherwise keep failing forever.
                Cache::forget('rss_' . $md5SourceUrl);

                $this->error("|- Unable to process result: {$e->getMessage()}");
            }
        }
    }

    /**
     * Cache duration for a downloaded feed.
     */
    protected function cacheTtl(): \DateTimeInterface
    {
        return now()->addHours((int) config('carol.cache.ttl', 6));
    }
}
