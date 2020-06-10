<?php 
namespace Sreedev\Slack\Api;

class Calls extends \Sreedev\Slack\Api\Api
{    
    /**
     * functionSet
     *
     * @var string
     */
    private $functionSet = "calls";
    
    /**
     * __construct
     *
     * @param  mixed $Slack
     * @param  mixed $method
     * @param  mixed $data
     * @return void
     */
    function __construct(\Sreedev\Slack\Slack $Slack, $method, $data)
    {
        parent::__construct($Slack, $method, $data, $this->functionSet);
    }
}