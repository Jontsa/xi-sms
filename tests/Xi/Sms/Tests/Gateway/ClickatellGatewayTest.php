<?php

namespace Xi\Sms\Tests\Gateway;

use GuzzleHttp\Psr7\Response;
use Xi\Sms\Gateway\ClickatellGateway;
use Xi\Sms\SmsMessage;

class ClickatellGatewayTest extends \PHPUnit_Framework_TestCase
{

    use HttpRequestGatewayTestTrait;

    /**
     * @test
     */
    public function sendsRequest()
    {
        $client = $this->createMockClient($historyContainer);

        $gateway = new ClickatellGateway('lussavain', 'lussuta', 'tussia', 'http://api.dr-kobros.com');
        $gateway->setClient($client);

        $message = new SmsMessage(
            'Pekkis tassa lussuttaa.',
            '358503028030',
            '358503028030'
        );

        $ret = $gateway->send($message);
        $this->assertTrue($ret);

        $this->assertCount(1, $historyContainer);
        $transaction = $historyContainer[0];
        $expectedUri = 'http://api.dr-kobros.com/http/sendmsg?api_id=lussavain&user=lussuta&password=' .
                       'tussia&to=358503028030&text=Pekkis+tassa+lussuttaa.&from=358503028030';
        $this->assertSame('POST', $transaction['request']->getMethod());
        $this->assertSame($expectedUri, (string) $transaction['request']->getUri());
    }

    /**
     * @test
     */
    public function sendsMultipleRequests()
    {
        $client = $this->createMockClient($historyContainer, array(
            new Response(200),
            new Response(200)
        ));

        $gateway = new ClickatellGateway('lussavain', 'lussuta', 'tussia', 'http://api.dr-kobros.com');
        $gateway->setClient($client);

        $message = new SmsMessage(
            'Pekkis tassa lussuttaa.',
            '358503028030',
            array('358503028030', '358441234567')
        );

        $ret = $gateway->send($message);
        $this->assertTrue($ret);

        $this->assertCount(2, $historyContainer);
        $transaction = $historyContainer[0];
        $expectedUri = 'http://api.dr-kobros.com/http/sendmsg?api_id=lussavain&user=lussuta&password=' .
                       'tussia&to=358503028030&text=Pekkis+tassa+lussuttaa.&from=358503028030';
        $this->assertSame($expectedUri, (string) $transaction['request']->getUri());

        $transaction = $historyContainer[1];
        $expectedUri = 'http://api.dr-kobros.com/http/sendmsg?api_id=lussavain&user=lussuta&password=' .
                       'tussia&to=358441234567&text=Pekkis+tassa+lussuttaa.&from=358503028030';
        $this->assertSame($expectedUri, (string) $transaction['request']->getUri());
    }

}
