<?php

namespace eirworks\carol\Console\Concerns;

trait MarksUnsafeItems
{
    /**
     * Decide whether a crawled item should be flagged as unsafe.
     */
    protected function isUnsafe(): bool
    {
        $percentage = (int) config('carol.crawl.unsafe_percentage', 5);

        return $percentage > 0 && random_int(1, 100) <= $percentage;
    }
}
