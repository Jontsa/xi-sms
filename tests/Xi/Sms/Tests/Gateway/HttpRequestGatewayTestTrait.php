<?php

namespace Xi\Sms\Tests\Gateway;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;

trait HttpRequestGatewayTestTrait
{

    /**
     * Creates Guzzle client with mock handler.
     * @param  array   &$historyContainer Pointer to variable where request history can be stored
     * @param  mixed   $response          Response object or array of responses for requests, defaults to one HTTP 200 response
     * @return Client
     */
    public function createMockClient(&$historyContainer = array(), $response = null)
    {
        if ($response instanceof Response) {
            $response = array($response);
        } elseif (is_array($response) === false || empty($response)) {
            $response = array(new Response(200));
        }

        // Create a mock and queue one response
        $mock = new MockHandler($response);

        // History container can be used to inspect sent request
        $historyContainer = array();
        $history = Middleware::history($historyContainer);

        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new Client(array(
            'handler' => $stack,
            RequestOptions::HTTP_ERRORS => false
        ));

        return $client;
    }

}
