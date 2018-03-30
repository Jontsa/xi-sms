<?php

namespace Xi\Sms\Tests\Gateway\Legacy;

use Xi\Sms\Gateway\Legacy\MessageBirdGateway;
use Xi\Sms\SmsMessage;
use Xi\Sms\Tests\Gateway\HttpRequestGatewayTestTrait;

class LegacyMessageBirdGatewayTest extends \PHPUnit_Framework_TestCase
{

    use HttpRequestGatewayTestTrait;

    /**
     * @test
     */
    public function sends()
    {
        $client = $this->createMockClient($historyContainer);

        $browser = $this->getMockBuilder('Buzz\Browser')
            ->disableOriginalConstructor()
            ->getMock();

        $gateway = new MessageBirdGateway('username', 'password');
        $gateway->setClient($client);

        $message = new SmsMessage(
            'Tenhunen lipaisee',
            'Tietoisku',
            '3581234567'
        );

        $ret = $gateway->send($message);
        $this->assertTrue($ret);

        $this->assertCount(1, $historyContainer);
        $transaction = $historyContainer[0];
        $expectedUri = 'https://api.messagebird.com/xml/sms?gateway=1&username=username&password=password&originator=Tietoisku&recipients=3581234567&type=normal&message=Tenhunen+lipaisee';

        $this->assertSame('POST', $transaction['request']->getMethod());
        $this->assertSame($expectedUri, (string) $transaction['request']->getUri());
    }
}
