<?php

namespace Xi\Sms\Tests\Gateway;

use GuzzleHttp\Psr7\Response;
use Xi\Sms\Gateway\InfobipGateway;
use Xi\Sms\SmsMessage;

class InfobipGatewayTest extends \PHPUnit_Framework_TestCase
{

    use HttpRequestGatewayTestTrait;

    /**
     * @test
     */
    public function sendsSingleMessage()
    {
        $client = $this->createMockClient($historyContainer);

        $gateway = new InfobipGateway('lussuta', 'tussia');
        $gateway->setClient($client);

        $message = new SmsMessage(
            'Tehdaan sovinto, pojat.',
            'Losoposki',
            '358503028030'
        );

        $ret = $gateway->send($message);
        $this->assertTrue($ret);

        $this->assertCount(1, $historyContainer);
        $transaction = $historyContainer[0];
        $expectedUri = 'https://api.infobip.com/sms/1/text/single';
        $this->assertSame('POST', $transaction['request']->getMethod());
        $this->assertSame($expectedUri, (string) $transaction['request']->getUri());

        $requestBody = (string) $transaction['request']->getBody();
        $requestJson = json_decode($requestBody, true);

        $this->assertArrayHasKey('from', $requestJson);
        $this->assertArrayHasKey('to', $requestJson);
        $this->assertArrayHasKey('text', $requestJson);
        $this->assertEquals('Losoposki', $requestJson['from']);
        $this->assertContains('358503028030', $requestJson['to']);
        $this->assertEquals('Tehdaan sovinto, pojat.', $requestJson['text']);
    }

    /**
     * @test
     */
    public function sendsMultipleMessages()
    {
        $client = $this->createMockClient($historyContainer);

        $gateway = new InfobipGateway('lussuta', 'tussia');
        $gateway->setClient($client);

        $message = new SmsMessage(
            'Tehdaan sovinto, pojat.',
            'Losoposki',
            ['358503028030', '358441234567']
        );

        $ret = $gateway->send($message);
        $this->assertTrue($ret);

        $this->assertCount(1, $historyContainer);
        $transaction = $historyContainer[0];

        $requestBody = (string) $transaction['request']->getBody();
        $requestJson = json_decode($requestBody, true);

        $this->assertArrayHasKey('to', $requestJson);
        $this->assertContains('358503028030', $requestJson['to']);
        $this->assertContains('358441234567', $requestJson['to']);
    }

    /**
     * @test
     */
    public function httpErrorResultsInFailure()
    {
        $client = $this->createMockClient($historyContainer, new Response(404));

        $gateway = new InfobipGateway('lussuta', 'tussia');
        $gateway->setClient($client);

        $message = new SmsMessage(
            'Tehdaan sovinto, pojat.',
            'Losoposki',
            '358503028030'
        );

        $ret = $gateway->send($message);
        $this->assertFalse($ret);
    }
}
