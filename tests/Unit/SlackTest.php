<?php 
namespace Sreedev\MailChimp\Tests\Unit;

use Sreedev\Slack\Facades\Slack;
use Sreedev\Slack\Tests\TestCase;

class SlackTest extends TestCase
{
    public function testSlackConfig()
    {
        $this->expectException('\Exception');
        Slack::chat('postMessage',
        [
            'channel'=>'@me',
            'text'=>'Test Message'
        ]);
    }
}