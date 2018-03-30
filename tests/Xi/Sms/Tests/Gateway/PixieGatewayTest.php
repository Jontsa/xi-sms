<?php

namespace Xi\Sms\Tests\Gateway;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Xi\Sms\SmsMessage;
use Xi\Sms\Gateway\PixieGateway;

class PixieGatewayTest extends \PHPUnit_Framework_TestCase
{

    use HttpRequestGatewayTestTrait;

    private function getMockSuccess()
    {
        $response = new Response(
            200,
            array(),
            '<?xml version="1.0" encoding = "ISO-8859-1" ?>'.
                '<response code="0"><cost>50</cost>'.
            '</response>'
        );
        return $response;
    }

    private function getMockFailure($message, $code)
    {
        $response = new Response(
            200,
            array(),
            '<?xml version="1.0" encoding = "ISO-8859-1" ?>'.
                '<response code="'.$code.'" description="'.$message.'">'.
            '</response>'
        );
        return $response;
    }

    private function getMockInvalidResponse()
    {
        $response = new Response(
            200,
            array(),
            '<?not_valid_xml<'
        );
        return $response;
    }

    private function getMockedGateway(Client $client)
    {
        $gateway = new PixieGateway(10203005, "DuY7ye99");
        $gateway->setClient($client);
        return $gateway;
    }

    /**
     * @test
     */
    public function sendIgnoresExceptions()
    {
        $response = $this->getMockFailure('Some kind of failure', 123);
        $client = $this->createMockClient($historyContainer, $response);
        $gateway = $this->getMockedGateway($client);

        $message = new SmsMessage(
            'Hello world',
            'Santa Claus',
            array(12345678)
        );

        $result = $gateway->send($message);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function sendsCorrectRequest()
    {
        $response = $this->getMockSuccess();
        $client = $this->createMockClient($historyContainer, $response);
        $gateway = $this->getMockedGateway($client);

        $message = new SmsMessage(
            'Rea i morgon. Välkommen',
            'Butiken',
            array(4670234567,463849235)
        );

        $gateway->sendOrThrowException($message);

        $this->assertCount(1, $historyContainer);
        $transaction = $historyContainer[0];
        $expectedUri = 'http://smsserver.pixie.se/sendsms?account=10203005&signature='.
                       '2a6044ac52c48a4531ad5bc2022d3069&receivers=4670234567,463849235'.
                       '&sender=Butiken&message=Rea%20i%20morgon.%20V%C3%A4lkommen';

        $this->assertSame('GET', $transaction['request']->getMethod());
        $this->assertSame($expectedUri, (string) $transaction['request']->getUri());
    }

    /**
     * @test
     */
    public function throwsRuntimeExceptionOnError()
    {
        $response = $this->getMockFailure("Too long sender name", 402);
        $client = $this->createMockClient($historyContainer, $response);
        $gateway = $this->getMockedGateway($client);

        $message = new SmsMessage(
            'Nice message',
            'Very long sender name',
            array(12345678)
        );

        $this->setExpectedException('\Xi\Sms\RuntimeException');

        $gateway->sendOrThrowException($message);

        $this->assertCount(1, $historyContainer);
        $transaction = $historyContainer[0];
        $this->assertSame('GET', $transaction['request']->getMethod());
    }

    /**
     * @test
     */
    public function throwsRuntimeExceptionWithInvalidServerResponse()
    {
        $response = $this->getMockInvalidResponse();
        $client = $this->createMockClient($historyContainer, $response);
        $gateway = $this->getMockedGateway($client);

        $message = new SmsMessage(
            'Nice message',
            'Me',
            array(12345678)
        );

        $this->setExpectedException('Xi\Sms\RuntimeException');

        $gateway->sendOrThrowException($message);

        $this->assertCount(1, $historyContainer);
        $transaction = $historyContainer[0];
        $this->assertSame('GET', $transaction['request']->getMethod());
    }
}
