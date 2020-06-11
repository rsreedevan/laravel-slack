<?php
namespace Sreedev\Slack\Tests;

use Sreedev\Slack\SlackServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            SlackServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
      // perform environment setup
    }
}