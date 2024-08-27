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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(Client::class)]
#[UsesClass(Result::class)]
#[UsesClass(Exception\NotSupportedResponseException::class)]
final class ClientTest extends TestCase
{
    public function testWithoutSiteKeyAndRemoteIp(): void
    {
        $client = $this->createClient(function (string $method, string $url, array $options) {
            self::assertSame('POST', $method);
            self::assertSame('https://api.hcaptcha.com/siteverify', $url);
            self::assertSame('response=abcdef', $options['body']);

            return $this->createResponse([]);
        });

        $result = $client->verify('abcdef');
        self::assertTrue($result->success);
    }

    public function testWithSiteKeyAndWithoutRemoteIp(): void
    {
        $client = $this->createClient(function (string $method, string $url, array $options) {
            self::assertSame('POST', $method);
            self::assertSame('https://api.hcaptcha.com/siteverify', $url);
            self::assertSame('response=abcdef&sitekey=12345+67890', $options['body']);

            return $this->createResponse([]);
        });

        $result = $client->verify('abcdef', '12345 67890');
        self::assertTrue($result->success);
    }

    public function testWithoutSiteKeyAndWithRemoteIp(): void
    {
        $client = $this->createClient(function (string $method, string $url, array $options) {
            self::assertSame('POST', $method);
            self::assertSame('https://api.hcaptcha.com/siteverify', $url);
            self::assertSame('response=abcdef&remoteip=10.9.2.234', $options['body']);

            return $this->createResponse([]);
        });

        $result = $client->verify('abcdef', null, '10.9.2.234');
        self::assertTrue($result->success);
    }

    public function testWithSiteKeyRemoteIp(): void
    {
        $client = $this->createClient(function (string $method, string $url, array $options) {
            self::assertSame('POST', $method);
            self::assertSame('https://api.hcaptcha.com/siteverify', $url);
            self::assertSame('response=abcdef&remoteip=10.9.2.234&sitekey=112233_445566%26789', $options['body']);

            return $this->createResponse([]);
        });

        $result = $client->verify('abcdef', '112233_445566&789', '10.9.2.234');
        self::assertTrue($result->success);
    }

    public function testWithNonJsonContentType(): void
    {
        $client = $this->createClient(function (string $method, string $url, array $options) {
            self::assertSame('POST', $method);
            self::assertSame('https://api.hcaptcha.com/siteverify', $url);
            self::assertSame('response=abcdef&remoteip=10.9.2.234&sitekey=112233_445566%26789', $options['body']);

            return new MockResponse(\json_encode(['success' => true], flags: \JSON_THROW_ON_ERROR), [
                'response_headers' => ['Content-Type: application/not-json'],
            ]);
        });

        $this->expectException(Exception\NotSupportedResponseException::class);
        $this->expectExceptionMessage('Response was not a successful JSON response, got: status `200` with content type `application/not-json`');

        $client->verify('abcdef', '112233_445566&789', '10.9.2.234');
    }

    private function createClient(\Closure $responseFactory): Client
    {
        $httpClient = new MockHttpClient($responseFactory);

        return Client::create('', new Psr18Client($httpClient));
    }

    /** @param list<ErrorCode> $errorCodes */
    private function createResponse(array $errorCodes): MockResponse
    {
        $content = ['error-codes' => $errorCodes];
        $content['success'] = $errorCodes === [] ? true : false;

        return new MockResponse(\json_encode($content, flags: \JSON_THROW_ON_ERROR), [
            'response_headers' => ['Content-Type: application/json'],
        ]);
    }
}
