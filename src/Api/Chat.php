<?php 

namespace Sreedev\Slack\Api;

class Chat extends \Sreedev\Slack\Api\Api
{    

    private $functionSet = "chat";

    /**
     * __construct
     *
     * @param  mixed $Slack
     * @return void
     */
    public function __construct( \Sreedev\Slack\Slack $Slack, $method, $data)
    {
        parent::__construct($Slack, $method, $data, $this->functionSet);
    }   
    

}