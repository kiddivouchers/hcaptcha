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

use Composer\InstalledVersions;
use Http\Discovery\Psr17FactoryDiscovery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[CoversClass(Client::class)]
#[UsesClass(Result::class)]
#[UsesClass(Exception\NotSupportedResponseException::class)]
final class ClientTest extends TestCase
{
    public function testCreateWithInvalidObject(): void
    {
        $httpClient = new class implements HttpClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new \Exception();
            }
        };

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('WiderPlan\\Hcaptcha\\Client::create(): Argument #2 ($httpClient) must be of type (Psr\\Http\\Client\\ClientInterface&Psr\\Http\\Message\\RequestFactoryInterface&Psr\\Http\\Message\\StreamFactoryInterface)|null, Psr\Http\Client\ClientInterface@anonymous given');

        // @phpstan-ignore-next-line argument.type
        Client::create('', $httpClient);
    }

    public function testWithoutSiteKeyAndRemoteIp(): void
    {
        $client = $this->createClient(function (string $method, string $url, string $body) {
            self::assertSame('POST', $method);
            self::assertSame('https://api.hcaptcha.com/siteverify', $url);
            self::assertSame('response=abcdef', $body);

            return $this->createResponse([]);
        });

        $result = $client->verify('abcdef');
        self::assertTrue($result->success);
    }

    public function testWithSiteKeyAndWithoutRemoteIp(): void
    {
        $client = $this->createClient(function (string $method, string $url, string $body) {
            self::assertSame('POST', $method);
            self::assertSame('https://api.hcaptcha.com/siteverify', $url);
            self::assertSame('response=abcdef&sitekey=12345+67890', $body);

            return $this->createResponse([]);
        });

        $result = $client->verify('abcdef', '12345 67890');
        self::assertTrue($result->success);
    }

    public function testWithoutSiteKeyAndWithRemoteIp(): void
    {
        $client = $this->createClient(function (string $method, string $url, string $body) {
            self::assertSame('POST', $method);
            self::assertSame('https://api.hcaptcha.com/siteverify', $url);
            self::assertSame('response=abcdef&remoteip=10.9.2.234', $body);

            return $this->createResponse([]);
        });

        $result = $client->verify('abcdef', null, '10.9.2.234');
        self::assertTrue($result->success);
    }

    public function testWithSiteKeyRemoteIp(): void
    {
        $client = $this->createClient(function (string $method, string $url, string $body) {
            self::assertSame('POST', $method);
            self::assertSame('https://api.hcaptcha.com/siteverify', $url);
            self::assertSame('response=abcdef&remoteip=10.9.2.234&sitekey=112233_445566%26789', $body);

            return $this->createResponse([]);
        });

        $result = $client->verify('abcdef', '112233_445566&789', '10.9.2.234');
        self::assertTrue($result->success);
    }

    public function testWithNonJsonContentType(): void
    {
        $client = $this->createClient(function (string $method, string $url, string $body) {
            self::assertSame('POST', $method);
            self::assertSame('https://api.hcaptcha.com/siteverify', $url);
            self::assertSame('response=abcdef&remoteip=10.9.2.234&sitekey=112233_445566%26789', $body);

            $body = \json_encode(['success' => true], flags: \JSON_THROW_ON_ERROR);

            return Psr17FactoryDiscovery::findResponseFactory()
                ->createResponse(200)
                ->withHeader('Content-Type', 'application/not-json')
                ->withBody(Psr17FactoryDiscovery::findStreamFactory()->createStream($body));
        });

        $this->expectException(Exception\NotSupportedResponseException::class);
        $this->expectExceptionMessage('Response was not a successful JSON response, got: status `200` with content type `application/not-json`');

        $client->verify('abcdef', '112233_445566&789', '10.9.2.234');
    }

    /**
     * @param \Closure(string, string, string): ResponseInterface $responseFactory
     */
    private function createClient(\Closure $responseFactory): Client
    {
        if (InstalledVersions::isInstalled('symfony/http-client')) {
            $factory = 'symfony.php';
        }

        if (InstalledVersions::isInstalled('guzzlehttp/guzzle')) {
            $factory = 'guzzle.php';
        }

        if (isset($factory)) {
            return (require basename(__FILE__, '.php') . '/' . $factory)('', $responseFactory);
        }

        self::markTestSkipped('No supported packages installed');
    }

    /** @param list<ErrorCode> $errorCodes */
    private function createResponse(array $errorCodes): ResponseInterface
    {
        $content = ['error-codes' => $errorCodes];
        $content['success'] = $errorCodes === [] ? true : false;
        $body = \json_encode($content, flags: \JSON_THROW_ON_ERROR);

        return Psr17FactoryDiscovery::findResponseFactory()
            ->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Psr17FactoryDiscovery::findStreamFactory()->createStream($body));
    }
}
