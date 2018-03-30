<?php

/**
 * This file is part of the Xi SMS package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Sms\Gateway;

use GuzzleHttp\RequestOptions;
use Xi\Sms\SmsMessage;

/**
 * Infobip gateway
 */
class InfobipGateway extends BaseHttpRequestGateway
{

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $endpoint = 'https://api.infobip.com';

    public function __construct(
        $user,
        $password
    ) {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @see GatewayInterface::send
     */
    public function send(SmsMessage $message)
    {
        $headers = $this->createAuthorizationHeaders();

        $data = [
            'from' => $message->getFrom(),
            'to' => $message->getTo(),
            'text' => $message->getBody()
        ];

        $endpoint = $this->endpoint . '/sms/1/text/single';

        $response = $this->getClient()->post(
            $endpoint,
            [
                RequestOptions::HEADERS => $headers,
                RequestOptions::JSON => $data
            ]
        );

        return ($response->getStatusCode() == 200);
    }

    /**
     * @todo Infobip recommends API key authentication
     * @return array
     */
    private function createAuthorizationHeaders()
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->user . ':' . $this->password)
        ];
    }
}
