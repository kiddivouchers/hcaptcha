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

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

return function (string $secretKey, \Closure $responseFactory): Client {
    $httpClient = new MockHttpClient(function (string $method, string $uri, array $options) use ($responseFactory) {
        $response = $responseFactory($method, $uri, $options['body']);
        \assert($response instanceof ResponseInterface);

        $headers = [];

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $headers[] = sprintf('%s: %s', $name, $value);
            }
        }

        return new MockResponse((string) $response->getBody(), [
            'http_code' => $response->getStatusCode(),
            'response_headers' => $headers,
        ]);
    });

    return Client::create($secretKey, new Psr18Client($httpClient));
};
