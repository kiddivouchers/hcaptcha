<?php

/*
 * This file is part of the hCaptcha API Client package.
 *
 * (c) Wider Plan <development@widerplan.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace WiderPlan\Hcaptcha;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Create;
use Http\Discovery\Psr18Client;
use Psr\Http\Message\RequestInterface;

return function (string $secretKey, \Closure $responseFactory): Client {
    $handler = function (RequestInterface $request) use ($responseFactory) {
        $response = $responseFactory(
            $request->getMethod(),
            (string) $request->getUri(),
            (string) $request->getBody(),
        );

        return Create::promiseFor($response);
    };

    return Client::create(
        $secretKey,
        new Psr18Client(new Guzzle(['handler' => HandlerStack::create($handler)])),
    );
};
