<?php

namespace Sreedev\Slack\Api;

class Api 
{    
    /**
     * Slack
     *
     * @var mixed
     */
    private $Slack;
    
    /**
     * endPoints
     *
     * @var mixed
     */
    private $endPoints;
    
    /**
     * functionSet
     *
     * @var mixed
     */
    private  $functionSet;
    
    /**
     * __construct
     *
     * @param  mixed $Slack
     * @param  mixed $method
     * @param  mixed $data
     * @return void
     */
    public function __construct(\Sreedev\Slack\Slack $Slack, $method, $data, $functionSet)
    {
        $this->Slack = $Slack;
        $this->setfunctionSet($functionSet);
        $json = file_get_contents(__DIR__. '/Slack.json');
        $this->endPoints = json_decode($json, true);
        return $this->sendRequest($method, $data);
    }
    
    /**
     * sendRequest
     *
     * @param  mixed $method
     * @param  mixed $data
     * @return void
     */
    private function sendRequest($method, $data)
    {
        $request = $this->endPoints[$this->functionSet.$method];
        return $this->execute(
            $request['method'], 
            $request['path'], 
            $data
        );
    }
     
    
    /**
     * execute
     *
     * @param  mixed $http_method
     * @param  mixed $method
     * @param  mixed $data
     * @return void
     */
    public function execute($http_method, $method, $data)
    {
        return $this->Slack->makeRequest($http_method, $method, $data);
    }
    
    /**
     * setfunctionSet
     *
     * @param  mixed $functionSet
     * @return void
     */
    private function setfunctionSet($functionSet)
    {
        $this->functionSet = $functionSet.".";
    }
}