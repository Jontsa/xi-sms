<?php

/**
 * This file is part of the Xi SMS package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Sms\Gateway;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

/**
 * Convenience class for http sending gateways
 */
abstract class BaseHttpRequestGateway Implements GatewayInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = new Client([
                RequestOptions::HTTP_ERRORS => false
            ]);
        }
        return $this->client;
    }
}
