<?php

namespace eirworks\carol\Support;

use RuntimeException;

class Sources
{
    /**
     * @param  array<string, string>  $paths  Map of source name to bundled file path.
     * @param  string|null  $hostSearchPath  Directory where a host application may override sources.
     */
    public function __construct(
        protected array $paths = [],
        protected ?string $hostSearchPath = null,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rss(): array
    {
        return $this->load('rss');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function youtube(): array
    {
        return $this->load('youtube');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function load(string $name): array
    {
        $path = $this->resolve($name);

        if (! is_file($path)) {
            throw new RuntimeException("Carol source file [{$name}] not found at [{$path}].");
        }

        $sources = require $path;

        return is_array($sources) ? $sources : [];
    }

    /**
     * Resolve a source file, preferring a host-provided override.
     */
    protected function resolve(string $name): string
    {
        if ($this->hostSearchPath !== null) {
            $candidate = rtrim($this->hostSearchPath, '/\\') . DIRECTORY_SEPARATOR . $name . '.php';

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        if (! isset($this->paths[$name])) {
            throw new RuntimeException("Carol source [{$name}] is not configured.");
        }

        return $this->paths[$name];
    }
}
