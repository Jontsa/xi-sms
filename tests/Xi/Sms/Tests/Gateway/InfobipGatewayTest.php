<?php

namespace Xi\Sms\Tests\Gateway;

use Xi\Sms\Gateway\InfobipGateway;
use Xi\Sms\SmsMessage;

class InfobipGatewayTest extends \PHPUnit_Framework_TestCase
{

    use HttpRequestGatewayTestTrait;

    /**
     * @test
     */
    public function sendsCorrectlyFormattedXmlToRightPlace()
    {
        $client = $this->createMockClient($historyContainer);

        $gateway = new InfobipGateway('lussuta', 'tussia', 'http://dr-kobros.com/api');
        $gateway->setClient($client);

        $message = new SmsMessage(
            'Tehdaan sovinto, pojat.',
            'Losoposki',
            '358503028030'
        );

        $message->addTo('358407682810');

        $ret = $gateway->send($message);
        $this->assertTrue($ret);

        $this->assertCount(1, $historyContainer);
        $transaction = $historyContainer[0];
        $expectedUri = 'http://dr-kobros.com/api/v3/sendsms/xml';
        $xml =
            "XML=<SMS><authentication><username>lussuta</username><password>tussia</password>" .
            "</authentication><message>" .
            "<sender>Losoposki</sender><datacoding>3</datacoding><text>Tehdaan sovinto, pojat.</text></message>" .
            "<recipients><gsm>358503028030</gsm><gsm>358407682810</gsm></recipients></SMS>\n";

        $this->assertSame('POST', $transaction['request']->getMethod());
        $this->assertSame($expectedUri, (string) $transaction['request']->getUri());
        $this->assertSame($xml, (string) $transaction['request']->getBody());
    }
}
