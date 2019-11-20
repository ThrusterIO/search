<?php

declare(strict_types=1);

namespace Thruster\Search;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Class ClientFactory
 *
 * @package Thruster\Search
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class ClientFactory
{
    /**
     * @param  string|array          $hosts
     * @param  LoggerInterface|null  $logger
     *
     * @return Client
     */
    public static function build($hosts, LoggerInterface $logger = null): Client
    {
        if (is_string($hosts)) {
            $hosts = preg_split('/,\s?/', $hosts);
        } elseif (false === is_array($hosts)) {
            throw new InvalidArgumentException(
                'Hosts should be comma separated string or array received: ' . gettype($hosts)
            );
        }

        $clientBuilder = ClientBuilder::create()
                                      ->setHosts($hosts);

        if (null !== $logger) {
            $clientBuilder->setLogger($logger);
        }

        return $clientBuilder->build();
    }
}
