<?php 
namespace Sreedev\Slack;

class Slack
{
    private $api_endpoint = "https://slack.com/api";
    private $request_successful = false;
    private $last_error         = '';
    private $api_token = '';
    private $last_response      = array();
    private $last_request       = array();
    private $verify_ssl = false;

    const TIMEOUT = 10;
    
    /**
     * __construct
     *
     * @param  mixed $api_token
     * @return void
     */
    public function __construct($api_token)
    {
        $this->api_token = $api_token;
    }

    
    /**
     * Chat
     *
     * @return Chat class instance
     */
    public function Chat($method, $data)
    {
        return new \Sreedev\Slack\Api\Chat($this, $method, $data);
    }
    
    /**
     * Calls
     *
     * @param  mixed $method
     * @param  mixed $data
     * @return void
     */
    public function Calls($method, $data)
    {
        return new \Sreedev\Slack\Api\Calls($this, $method, $data);
    }
    
    /**
     * Conversations
     *
     * @param  mixed $method
     * @param  mixed $data
     * @return void
     */
    public function Conversations($method, $data)
    {
        return new \Sreedev\Slack\Api\Conversations($this, $method, $data);
    }
   

     /**
     * Performs the underlying HTTP request. Not very exciting.
     *
     * @param  string $http_verb The HTTP verb to use: get, post, put, patch, delete
     * @param  string $method    The API method to be called
     * @param  array  $args      Assoc array of parameters to be passed
     * @param int     $timeout
     *
     * @return array|false Assoc array of decoded result
     */
    public function makeRequest($http_verb, $method, $args = array(), $timeout = self::TIMEOUT)
    {
        $url = $this->api_endpoint . '/' . $method;

        $response = $this->prepareStateForRequest($http_verb, $method, $url, $timeout);

        $httpHeader = array(
            'Content-type: application/json',
            'Authorization: Bearer '. $this->api_token
        );


        if (isset($args["language"])) {
            $httpHeader[] = "Accept-Language: " . $args["language"];
        }

        if ($http_verb === 'put') {
            $httpHeader[] = 'Allow: PUT, PATCH, POST';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Sreedev/laravel-slack (github.com/rsreedevan/laravel-slack)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        switch ($http_verb) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                $this->attachRequestPayload($ch, $args);
                break;

            case 'GET':
                $query = http_build_query($args, '', '&');
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $query);
                break;

            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;

            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                $this->attachRequestPayload($ch, $args);
                break;

            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                $this->attachRequestPayload($ch, $args);
                break;
        }

        $responseContent     = curl_exec($ch);
        $response['headers'] = curl_getinfo($ch);
        $response            = $this->setResponseState($response, $responseContent, $ch);
        $formattedResponse   = $this->formatResponse($response);

        curl_close($ch);

        $isSuccess = $this->determineSuccess($response, $formattedResponse, $timeout);

        return is_array($formattedResponse) ? $formattedResponse : $isSuccess;
    }

    /**
     * @param string  $http_verb
     * @param string  $method
     * @param string  $url
     * @param integer $timeout
     *
     * @return array
     */
    private function prepareStateForRequest($http_verb, $method, $url, $timeout)
    {
        $this->last_error = '';

        $this->request_successful = false;

        $this->last_response = array(
            'headers'     => null, // array of details from curl_getinfo()
            'httpHeaders' => null, // array of HTTP headers
            'body'        => null // content of the response
        );

        $this->last_request = array(
            'method'  => $http_verb,
            'path'    => $method,
            'url'     => $url,
            'body'    => '',
            'timeout' => $timeout,
        );

        return $this->last_response;
    }

    /**
     * Get the HTTP headers as an array of header-name => header-value pairs.
     *
     * The "Link" header is parsed into an associative array based on the
     * rel names it contains. The original value is available under
     * the "_raw" key.
     *
     * @param string $headersAsString
     *
     * @return array
     */
    private function getHeadersAsArray($headersAsString)
    {
        $headers = array();

        foreach (explode("\r\n", $headersAsString) as $i => $line) {
            if (preg_match('/HTTP\/[1-2]/', substr($line, 0, 7)) === 1) { // http code
                continue;
            }

            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            list($key, $value) = explode(': ', $line);

            if ($key == 'Link') {
                $value = array_merge(
                    array('_raw' => $value),
                    $this->getLinkHeaderAsArray($value)
                );
            }

            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * Extract all rel => URL pairs from the provided Link header value
     *
     * Mailchimp only implements the URI reference and relation type from
     * RFC 5988, so the value of the header is something like this:
     *
     * 'https://us13.api.mailchimp.com/schema/3.0/Lists/Instance.json; rel="describedBy",
     * <https://us13.admin.mailchimp.com/lists/members/?id=XXXX>; rel="dashboard"'
     *
     * @param string $linkHeaderAsString
     *
     * @return array
     */
    private function getLinkHeaderAsArray($linkHeaderAsString)
    {
        $urls = array();

        if (preg_match_all('/<(.*?)>\s*;\s*rel="(.*?)"\s*/', $linkHeaderAsString, $matches)) {
            foreach ($matches[2] as $i => $relName) {
                $urls[$relName] = $matches[1][$i];
            }
        }

        return $urls;
    }

    /**
     * Encode the data and attach it to the request
     *
     * @param   resource $ch   cURL session handle, used by reference
     * @param   array    $data Assoc array of data to attach
     */
    private function attachRequestPayload(&$ch, $data)
    {
        $encoded                    = json_encode($data);
        $this->last_request['body'] = $encoded;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
    }

    /**
     * Decode the response and format any error messages for debugging
     *
     * @param array $response The response from the curl request
     *
     * @return array|false    The JSON decoded into an array
     */
    private function formatResponse($response)
    {
        $this->last_response = $response;

        if (!empty($response['body'])) {
            return json_decode($response['body'], true);
        }

        return false;
    }

    /**
     * Do post-request formatting and setting state from the response
     *
     * @param array    $response        The response from the curl request
     * @param string   $responseContent The body of the response from the curl request
     * @param resource $ch              The curl resource
     *
     * @return array    The modified response
     */
    private function setResponseState($response, $responseContent, $ch)
    {
        if ($responseContent === false) {
            $this->last_error = curl_error($ch);
        } else {

            $headerSize = $response['headers']['header_size'];

            $response['httpHeaders'] = $this->getHeadersAsArray(substr($responseContent, 0, $headerSize));
            $response['body']        = substr($responseContent, $headerSize);

            if (isset($response['headers']['request_header'])) {
                $this->last_request['headers'] = $response['headers']['request_header'];
            }
        }

        return $response;
    }

    /**
     * Check if the response was successful or a failure. If it failed, store the error.
     *
     * @param array       $response          The response from the curl request
     * @param array|false $formattedResponse The response body payload from the curl request
     * @param int         $timeout           The timeout supplied to the curl request.
     *
     * @return bool     If the request was successful
     */
    private function determineSuccess($response, $formattedResponse, $timeout)
    {
        $status = $this->findHTTPStatus($response, $formattedResponse);

        if ($status >= 200 && $status <= 299) {
            $this->request_successful = true;
            return true;
        }

        if (isset($formattedResponse['detail'])) {
            $this->last_error = sprintf('%d: %s', $formattedResponse['status'], $formattedResponse['detail']);
            return false;
        }

        if ($timeout > 0 && $response['headers'] && $response['headers']['total_time'] >= $timeout) {
            $this->last_error = sprintf('Request timed out after %f seconds.', $response['headers']['total_time']);
            return false;
        }

        $this->last_error = 'Unknown error, call getLastResponse() to find out what happened.';
        return false;
    }

    /**
     * Find the HTTP status code from the headers or API response body
     *
     * @param array       $response          The response from the curl request
     * @param array|false $formattedResponse The response body payload from the curl request
     *
     * @return int  HTTP status code
     */
    private function findHTTPStatus($response, $formattedResponse)
    {
        if (!empty($response['headers']) && isset($response['headers']['http_code'])) {
            return (int)$response['headers']['http_code'];
        }

        if (!empty($response['body']) && isset($formattedResponse['status'])) {
            return (int)$formattedResponse['status'];
        }

        return 418;
    }
}