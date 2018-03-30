<?php

namespace Xi\Sms\Tests\Gateway;

class BaseHttpRequestGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getClientShouldCreateDefaultClient()
    {
        $adapter = $this
            ->getMockBuilder('Xi\Sms\Gateway\BaseHttpRequestGateway')
            ->getMockForAbstractClass();

        $client = $adapter->getClient();
        $this->assertInstanceOf('GuzzleHttp\Client', $client);
    }

    /**
     * @test
     */
    public function getClientShouldObeySetter()
    {
        $adapter = $this
            ->getMockBuilder('Xi\Sms\Gateway\BaseHttpRequestGateway')
            ->getMockForAbstractClass();

        $client = $this->getMockBuilder('GuzzleHttp\ClientInterface')->disableOriginalConstructor()->getMock();

        $adapter->setClient($client);

        $this->assertSame($client, $adapter->getClient());
    }
}
