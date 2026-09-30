# Carol search engine core

The search engine core extracted from the older laravel application: an RSS/Atom crawler,
its feed parsing library, the `search_items` database layer and the feed source
resources.

## Installation

This package is not published on Packagist, so Composer has to be pointed at
its source. Add a repository entry to the host application's `composer.json`,
then require the package normally.

### From the Git repository

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/eirworks/carol-se" }
    ],
    "require": {
        "eirworks/carol": "dev-main"
    }
}
```

```bash
composer update eirworks/carol
```

Pin a tag or commit instead of `dev-main` (`"eirworks/carol": "^1.0"`) once the
repository is tagged. For a private repository, add the credentials Composer
should use:

```bash
composer config --global gitlab-token.gitlab.com <token>   # GitLab
composer config --global http-basic.github.com <user> <token>  # GitHub over HTTPS
```

### From a local checkout

When the package lives next to the host application, use a path repository
instead — changes are picked up without re-installing, and symlinking keeps the
package editable:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../packages/carol",
            "options": { "symlink": true }
        }
    ],
    "require": {
        "eirworks/carol": "*@dev"
    }
}
```

```bash
composer update eirworks/carol
```

The `@dev` flag is required because a path repository exposes the package as a
dev version, which the default `minimum-stability: stable` would otherwise
reject.

Laravel discovers `eirworks\carol\CarolServiceProvider` automatically in both
cases.

## Publishing (optional)

```bash
php artisan vendor:publish --tag=carol-config      # config/carol.php
php artisan vendor:publish --tag=carol-resources   # resources/search/*.php
php artisan vendor:publish --tag=carol-migrations  # database/migrations
```

A host application can also drop its own `resources/search/rss.php` and
`resources/search/youtube.php`; `Sources` prefers those automatically.

## Usage

```bash
php artisan crawl          # truncate + crawl RSS and YouTube
php artisan crawl:rss
php artisan crawl:youtube
```

Parsing on its own:

```php
use eirworks\carol\Lib\RSSParser;

$items = (new RSSParser())->rssToSearchItem($xml);
```

Querying crawled items:

```php
use eirworks\carol\Models\SearchItem;

$articles = SearchItem::where('topic', 'world')->latest()->get();
```

## Tests

```bash
composer install
composer test
```

The suite covers the parser and the package configuration/source resolution.
