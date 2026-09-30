<?php

namespace eirworks\carol\Console\Commands;

use eirworks\carol\Models\SearchItem;
use Illuminate\Console\Command;

class Crawl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl data from news, youtube';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        SearchItem::query()->truncate();

        $this->call('crawl:rss');
        $this->call('crawl:youtube');

        return self::SUCCESS;
    }
}
