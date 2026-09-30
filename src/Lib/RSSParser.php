<?php

namespace eirworks\carol\Lib;

use SimpleXMLElement;

class RSSParser
{
    /**
     * Parse an RSS 2.0 or Atom feed into a list of search items.
     *
     * @return array<int, array<string, string>>
     */
    public function rssToSearchItem(string $rssContent): array
    {
        $xml = new SimpleXMLElement($rssContent);

        if ($xml->getName() === 'feed') {
            return $this->parseAtomEntry($xml);
        }

        return $this->parseRssItem($xml);
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function parseRssItem(SimpleXMLElement $xml): array
    {
        $searchResults = [];

        foreach ($xml->channel->item as $item) {
            $result = [
                'title' => (string) $item->title,
                'url' => (string) $item->link,
                'content' => $this->cleanContent((string) $item->description),
            ];

            $mediaContent = $item->children('media', true)->content;
            if ($mediaContent) {
                $result['image'] = (string) $mediaContent->attributes()['url'];
            }

            $searchResults[] = $result;
        }

        return $searchResults;
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function parseAtomEntry(SimpleXMLElement $xml): array
    {
        $searchResults = [];

        foreach ($xml->entry as $item) {
            $result = [];

            $mediaGroup = $item->children('media', true)->group;
            if ($mediaGroup) {
                $mediaTitle = (string) $mediaGroup->title;

                if ($mediaTitle !== '') {
                    $result['title'] = $mediaTitle;
                }

                $result['content'] = (string) $mediaGroup->description;
                $result['image'] = (string) $mediaGroup->thumbnail->attributes()['url'];
            }

            $result['url'] = (string) $item->link->attributes()['href'];

            $searchResults[] = $result;
        }

        return $searchResults;
    }

    /**
     * Remove markup and common feed noise from an item description.
     */
    protected function cleanContent(string $content): string
    {
        $content = strip_tags($content);
        $content = str_replace('Continue reading...', '', $content);
        $content = str_replace('Read more', '', $content);
        $content = str_replace('Read Full Article at RT.com', '', $content);

        $htmlEntities = [
            '&nbsp;',
            '&gt;',
            '&lt;',
        ];

        foreach ($htmlEntities as $entity) {
            $content = str_replace($entity, '', $content);
        }

        return $content;
    }
}
