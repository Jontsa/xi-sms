<?php

namespace Xi\Sms\Tests\Gateway;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Xi\Sms\Gateway\SmskaufenGateway;
use Xi\Sms\SmsMessage;
use Xi\Sms\SmsService;

class SmskaufenGatewayTest extends \PHPUnit_Framework_TestCase
{

    use HttpRequestGatewayTestTrait;

    /**
     * Content for exactly 1 message.
     * @var string
     */
    private $message_160 = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At v';

    /**
     * Content for 4 SMS messages.
     * @var string
     */
    private $message_591 = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';

    private function getMockedGateway(Client $client, $gateway, $https = true)
    {
        $SmskaufenGateway = new SmskaufenGateway([
            'username' => 'XXX',
            'password' => 'YYY',
            'gateway' => $gateway,
            'https' => $https
        ]);
        $SmskaufenGateway->setClient($client);
        return $SmskaufenGateway;
    }

    /**
     * @test
     */
    public function sendMass2()
    {
        $client = $this->createMockClient();
        $gateway = $this->getMockedGateway($client, 13);

        $msg = new SmsMessage('Hi', '00491234', ['00491111', '015111111', '0170111111']);
        $this->setExpectedException('Xi\Sms\RuntimeException');
        $gateway->send($msg);
    }

    /**
     * @test
     */
    public function sendMass1()
    {
        $response = new Response(400);
        $client = $this->createMockClient($historyContainer, $response);
        $gateway = $this->getMockedGateway($client, 4);

        $msg = new SmsMessage('Hi', '00491234', ['00491111', '015111111', '0170111111']);
        $response = $gateway->send($msg); // Should not throw any exception
        $this->assertFalse($response);
    }

    /**
     * @test
     */
    public function sendMaxi3()
    {
        $response = new Response(400);
        $client = $this->createMockClient($historyContainer, $response);
        $gateway = $this->getMockedGateway($client, 4);

        // Exactly 160 characters
        $msg = new SmsMessage($this->message_160, '00491234', '00491111');
        $gateway->send($msg); // Should not throw any exception
    }

    /**
     * @test
     */
    public function sendMaxi2()
    {
        $response = new Response(400);
        $client = $this->createMockClient($historyContainer, $response);
        $gateway = $this->getMockedGateway($client, 4);

        $msg = new SmsMessage($this->message_591, '00491234', '00491111');
        $gateway->send($msg); // Should not throw any exception
    }

    /**
     * @test
     */
    public function sendMaxi1()
    {
        $client = $this->createMockClient();
        $gateway = $this->getMockedGateway($client, 13);

        $msg = new SmsMessage($this->message_591, '00491234', '00491111');
        $this->setExpectedException('Xi\Sms\RuntimeException');
        $gateway->send($msg); // Should not throw any exception
    }

    /**
     * @test
     */
    public function sendFail2()
    {
        $response = new Response(200, [], '121');
        $client = $this->createMockClient($historyContainer, $response);
        $gateway = $this->getMockedGateway($client, 4);

        $msg = new SmsMessage('Hi', '00491234', '00491111');
        $success = $gateway->send($msg);
        $this->assertFalse($success);
    }

    /**
     * @test
     */
    public function sendFail1()
    {
        $response = new Response(404);
        $client = $this->createMockClient($historyContainer, $response);
        $gateway = $this->getMockedGateway($client, 4);

        $msg = new SmsMessage('Hi', '00491234', '00491111');
        $success = $gateway->send($msg);
        $this->assertFalse($success);
    }

    /**
     * @test
     */
    public function sendSuccess()
    {
        $response = new Response(200, [], '100');
        $client = $this->createMockClient($historyContainer, $response);
        $gateway = $this->getMockedGateway($client, 4);

        $msg = new SmsMessage('Hi', '00491234', '00491111');
        $success = $gateway->send($msg);
        $this->assertTrue($success);
    }

    /**
     * @test
     */
    public function urlMassDispatch()
    {
        $response = new Response(200, [], '100');
        $client = $this->createMockClient($historyContainer, $response);
        $gateway = $this->getMockedGateway($client, 4, false);

        $msg = new SmsMessage('Hi', '00491234', ['00491111', '015111111', '0170111111']);
        $success = $gateway->send($msg);
        $this->assertTrue($success);

        $this->assertCount(1, $historyContainer);
        $request = $historyContainer[0]['request'];
        $uri = $request->getUri();
        parse_str($uri->getQuery(), $query);
        $this->assertTrue($request->hasHeader('Content-type'));
        $this->assertContains('application/x-www-form-urlencoded', $request->getHeader('Content-type'));
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('www.smskaufen.com', $uri->getHost());
        $this->assertSame('/sms/gateway/sms.php', $uri->getPath());
        $this->assertSame('XXX', $query['id']);
        $this->assertSame('YYY', $query['pw']);
        $this->assertSame('4', $query['type']);
        $this->assertSame('Hi', $query['text']);
        $this->assertSame('00491111;015111111;0170111111', $query['empfaenger']);
        $this->assertSame('00491234', $query['absender']);
        $this->assertSame('1', $query['massen']);
        $this->assertLessThan(time(), $query['termin']);
    }

    /**
     * @test
     */
    public function urlNormalDispatch()
    {
        $response = new Response(200, [], '100');
        $client = $this->createMockClient($historyContainer, $response);
        $gateway = $this->getMockedGateway($client, 4);

        $msg = new SmsMessage('Hi', '00491234', '00491111');
        $success = $gateway->send($msg);
        $this->assertTrue($success);

        $this->assertCount(1, $historyContainer);
        $request = $historyContainer[0]['request'];
        $uri = $request->getUri();
        parse_str($uri->getQuery(), $query);
        $this->assertTrue($request->hasHeader('Content-type'));
        $this->assertContains('application/x-www-form-urlencoded', $request->getHeader('Content-type'));
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('www.smskaufen.com', $uri->getHost());
        $this->assertSame('/sms/gateway/sms.php', $uri->getPath());
        $this->assertSame('XXX', $query['id']);
        $this->assertSame('YYY', $query['pw']);
        $this->assertSame('4', $query['type']);
        $this->assertSame('Hi', $query['text']);
        $this->assertSame('00491111', $query['empfaenger']);
        $this->assertSame('00491234', $query['absender']);
        $this->assertNotContains('massen', $query);
        $this->assertNotContains('termin', $query);
    }
}
