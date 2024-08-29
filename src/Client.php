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

use Http\Discovery\Psr18Client;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * hCaptcha client built using a PSR-18 HTTP client.
 *
 * @copyright 2024 Wider Plan
 * @license   MIT
 */
final class Client implements ClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        #[\SensitiveParameter()]
        private readonly string $secretKey,
    ) {}

    /**
     * @param (HttpClientInterface&RequestFactoryInterface&StreamFactoryInterface)|null $httpClient
     */
    public static function create(
        #[\SensitiveParameter()]
        string $secretKey,
        object|null $httpClient = null,
    ): self {
        // @todo This can be removed when minimum requirement is raised to PHP 8.2.
        if ($httpClient !== null && !($httpClient instanceof HttpClientInterface && $httpClient instanceof RequestFactoryInterface && $httpClient instanceof StreamFactoryInterface)) {
            throw new \TypeError(sprintf(
                '%s::%s(): Argument #2 ($httpClient) must be of type (%s&%s&%s)|null, %s given',
                self::class,
                __METHOD__,
                HttpClientInterface::class,
                RequestFactoryInterface::class,
                StreamFactoryInterface::class,
                \get_debug_type($httpClient),
            ));
        }

        if ($httpClient === null) {
            if (!\class_exists(Psr18Client::class)) {
                throw new \LogicException('Pass in a suitable object or install package `php-http/discovery` to have one automatically created');
            }

            $httpClient = new Psr18Client();
        }

        return new self($httpClient, $httpClient, $httpClient, $secretKey);
    }

    public function verify(string $response, ?string $siteKey = null, ?string $remoteIp = null): Result
    {
        $body = [
            'response' => $response,
            'secret' => $this->secretKey,
            'remoteip' => $remoteIp,
            'sitekey' => $siteKey,
        ];

        $body = array_filter($body, function (?string $value) {
            return $value !== '' && $value !== null;
        });
        $body = \http_build_query($body, '', '&', \PHP_QUERY_RFC1738);

        $request = $this->requestFactory
            ->createRequest('POST', 'https://api.hcaptcha.com/siteverify')
            ->withAddedHeader('Accept', 'application/json')
            ->withBody($this->streamFactory->createStream($body));

        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() === 200 && $response->getHeaderLine('Content-Type') === 'application/json') {
            return Result::fromJson((string) $response->getBody());
        }

        throw Exception\NotSupportedResponseException::fromResponse($response);
    }
}
