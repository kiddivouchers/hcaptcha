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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Result::class)]
final class ResultTest extends TestCase
{
    public function testSuccessAndHasErrorCodes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Successful result has error codes');

        new Result(true, [ErrorCode::BAD_REQUEST], null, null);
    }

    public function testNotSuccessAndHasNoErrorCodes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsuccessful result requires at least one error code');

        new Result(false, [], null, null);
    }

    #[DataProvider('dataFromJson')]
    public function testFromJson(\Closure $callback, string $input): void
    {
        $result = Result::fromJson($input);

        $callback($result);
    }

    /** @return iterable<array-key,array{\Closure,string}> */
    public static function dataFromJson(): iterable
    {
        foreach (self::dataFromArray() as $testCase) {
            yield [$testCase[0], \json_encode($testCase[1], \JSON_THROW_ON_ERROR)];
        }
    }

    #[DataProvider('dataFromJsonError')]
    public function testFromJsonError(\Closure $callback, string $input): void
    {
        try {
            Result::fromJson($input);

            self::fail('Expected throwable to be thrown.');
        } catch (\Throwable $error) {
            if ($error instanceof \PHPUnit\Exception) {
                throw $error;
            }

            $callback($error);
        }
    }

    /** @return iterable<array-key,array{\Closure,string}> */
    public static function dataFromJsonError(): iterable
    {
        yield [
            function (\Throwable $e) {
                self::assertInstanceOf(\InvalidArgumentException::class, $e);
                self::assertEquals('JSON should be an object, got: `string`', $e->getMessage());
            },
            '"test"',
        ];

        yield [
            function (\Throwable $e) {
                self::assertInstanceOf(\InvalidArgumentException::class, $e);
                self::assertEquals('Failed to decode result from JSON', $e->getMessage());
                self::assertInstanceOf(\JsonException::class, $e->getPrevious());
            },
            '{success: true}',
        ];

        foreach (self::dataFromArrayWithError() as $testCase) {
            yield [$testCase[0], \json_encode($testCase[1], \JSON_THROW_ON_ERROR)];
        }
    }

    /** @param array<array-key,mixed> $input */
    #[DataProvider('dataFromArray')]
    public function testFromArray(\Closure $callback, array $input): void
    {
        $result = Result::fromArray($input);

        $callback($result);
    }

    /** @return iterable<array-key,array{\Closure,array<array-key,mixed>}> */
    public static function dataFromArray(): iterable
    {
        yield [
            function (Result $result) {
                self::assertTrue($result->success);
                self::assertSame([], $result->errorCodes);
                self::assertNull($result->challengeTime);
                self::assertNull($result->hostname);
            },
            [
                'success' => true,
            ],
        ];

        yield [
            function (Result $result) {
                self::assertTrue($result->success);
                self::assertSame([], $result->errorCodes);
                self::assertEquals(new \DateTimeImmutable('2024-02-12T13:29:38Z'), $result->challengeTime);
                self::assertNull($result->hostname);
            },
            [
                'success' => true,
                'challenge_ts' => '2024-02-12T13:29:38Z',
            ],
        ];

        yield [
            function (Result $result) {
                self::assertTrue($result->success);
                self::assertSame([], $result->errorCodes);
                self::assertEquals(new \DateTimeImmutable('2024-08-27T16:11:27.595Z'), $result->challengeTime);
                self::assertNull($result->hostname);
            },
            [
                'success' => true,
                'challenge_ts' => '2024-08-27T16:11:27.595Z',
            ],
        ];

        yield [
            function (Result $result) {
                self::assertTrue($result->success);
                self::assertSame([], $result->errorCodes);
                self::assertNull($result->challengeTime);
                self::assertNull($result->hostname);
            },
            [
                'success' => true,
                'hostname' => '',
            ],
        ];

        yield [
            function (Result $result) {
                self::assertTrue($result->success);
                self::assertSame([], $result->errorCodes);
                self::assertNull($result->challengeTime);
                self::assertSame('foo.bar', $result->hostname);
            },
            [
                'success' => true,
                'hostname' => 'foo.bar',
            ],
        ];

        yield [
            function (Result $result) {
                self::assertFalse($result->success);
                self::assertSame([ErrorCode::BAD_REQUEST, ErrorCode::REMOTE_IP_INVALID, ErrorCode::RESPONSE_INVALID], $result->errorCodes);
                self::assertNull($result->challengeTime);
                self::assertNull($result->hostname);
            },
            [
                'success' => false,
                'error-codes' => ['invalid-remoteip', 'invalid-input-response', 'bad-request'],
            ],
        ];
    }

    /** @param array<array-key,mixed> $input */
    #[DataProvider('dataFromArrayWithError')]
    public function testFromArrayWithError(\Closure $callback, array $input): void
    {
        try {
            Result::fromArray($input);

            self::fail('Expected throwable to be thrown.');
        } catch (\Throwable $error) {
            if ($error instanceof \PHPUnit\Exception) {
                throw $error;
            }

            $callback($error);
        }
    }

    /** @return iterable<array-key,array{\Closure,array<array-key,mixed>}> */
    public static function dataFromArrayWithError(): iterable
    {
        yield [
            function (\Throwable $error) {
                self::assertInstanceOf(\InvalidArgumentException::class, $error);
                self::assertEquals('`success` property is not valid, got: `string`', $error->getMessage());
            },
            [
                'success' => 'x',
            ],
        ];

        yield [
            function (\Throwable $error) {
                self::assertInstanceOf(\InvalidArgumentException::class, $error);
                self::assertEquals('`challenge_ts` property is not valid, got: `2024-02-12x13:29:38Z`', $error->getMessage());
            },
            [
                'success' => true,
                'challenge_ts' => '2024-02-12x13:29:38Z',
            ],
        ];

        yield [
            function (\Throwable $error) {
                self::assertInstanceOf(\InvalidArgumentException::class, $error);
                self::assertEquals('`challenge_ts` property is not valid, got: `int`', $error->getMessage());
            },
            [
                'success' => true,
                'challenge_ts' => 1263894603,
            ],
        ];

        yield [
            function (\Throwable $error) {
                self::assertInstanceOf(\InvalidArgumentException::class, $error);
                self::assertEquals('`hostname` property is not valid, got: `array`', $error->getMessage());
            },
            [
                'success' => true,
                'hostname' => ['x'],
            ],
        ];

        yield [
            function (\Throwable $error) {
                self::assertInstanceOf(\InvalidArgumentException::class, $error);
                self::assertEquals('`error-codes` property is not valid, got: `string`', $error->getMessage());
            },
            [
                'success' => false,
                'error-codes' => 'invalid-input-response',
            ],
        ];

        yield [
            function (\Throwable $error) {
                self::assertInstanceOf(\InvalidArgumentException::class, $error);
                self::assertEquals('Error code `not-an-error` is not recognised', $error->getMessage());
            },
            [
                'success' => false,
                'error-codes' => ['not-an-error'],
            ],
        ];

        yield [
            function (\Throwable $error) {
                self::assertInstanceOf(\InvalidArgumentException::class, $error);
                self::assertEquals('Error code `bool` is not recognised', $error->getMessage());
            },
            [
                'success' => false,
                'error-codes' => [true, 'also-not-an-error'],
            ],
        ];
    }

    public function testIsError(): void
    {
        $r = new Result(true, [], null, null);

        foreach (ErrorCode::cases() as $errorCode) {
            self::assertFalse($r->isError($errorCode));
        }

        $r = new Result(false, [ErrorCode::BAD_REQUEST], null, null);

        self::assertTrue($r->isError(ErrorCode::BAD_REQUEST));
        self::assertFalse($r->isError(ErrorCode::REMOTE_IP_INVALID));
    }
}
