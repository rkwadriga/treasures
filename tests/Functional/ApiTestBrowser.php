<?php

namespace App\Tests\Functional;

use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\KernelBrowser;

final class ApiTestBrowser extends KernelBrowser
{
    public function patch(string $url, $options = []): static
    {
        return parent::patch($url, HttpOptions::create($options)
            ->withHeader('Content-Type', 'application/merge-patch+json'));
    }
}