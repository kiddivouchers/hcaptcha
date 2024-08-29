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

use Webmozart\Assert\Assert;

/**
 * @copyright 2024 Wider Plan
 * @license   MIT
 */
final class Result
{
    public function __construct(
        public readonly bool $success,
        /** @var list<ErrorCode> */
        public readonly array $errorCodes,
        public readonly ?\DateTimeImmutable $challengeTime,
        public readonly ?string $hostname,
    ) {
        Assert::allIsInstanceOf($errorCodes, ErrorCode::class);

        if ($success && $errorCodes !== []) {
            throw new \InvalidArgumentException(
                'Successful result has error codes',
            );
        }

        if (!$success && $errorCodes === []) {
            throw new \InvalidArgumentException(
                'Unsuccessful result requires at least one error code',
            );
        }
    }

    /**
     * Create instance representing a success.
     */
    public static function success(?\DateTimeImmutable $challengeTime = null, ?string $hostname = null): self
    {
        return new self(
            true,
            [],
            $challengeTime,
            $hostname,
        );
    }

    /**
     * Create instance representing a failure.
     *
     * @param list<ErrorCode>&non-empty-array $errorCodes
     */
    public static function failure(array $errorCodes, ?\DateTimeImmutable $challengeTime = null, ?string $hostname = null): self
    {
        Assert::minCount($errorCodes, 1);

        return new self(
            false,
            $errorCodes,
            $challengeTime,
            $hostname,
        );
    }

    public static function fromJson(string $json): self
    {
        try {
            $response = \json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Failed to decode result from JSON', 0, $e);
        }

        if (!\is_array($response) || ($response !== [] && \array_is_list($response))) {
            throw new \InvalidArgumentException(\sprintf(
                'JSON should be an object, got: `%s`',
                \get_debug_type($response),
            ));
        }

        return self::fromArray($response);
    }

    /**
     * @param array<string,mixed> $response
     */
    public static function fromArray(array $response): self
    {
        if (isset($response['challenge_ts']) && \is_string($response['challenge_ts'])) {
            $challengeTime = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s.u\Z', $response['challenge_ts'], new \DateTimeZone('UTC'));

            if ($challengeTime === false) {
                $challengeTime = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s\Z', $response['challenge_ts'], new \DateTimeZone('UTC'));
            }

            if ($challengeTime === false) {
                throw new \InvalidArgumentException(sprintf(
                    '`challenge_ts` property is not valid, got: `%s`',
                    $response['challenge_ts'],
                ));
            }
        } elseif (isset($response['challenge_ts'])) {
            throw new \InvalidArgumentException(sprintf(
                '`challenge_ts` property is not valid, got: `%s`',
                \get_debug_type($response['challenge_ts']),
            ));
        } else {
            $challengeTime = null;
        }

        $success = $response['success'] ?? null;
        $errorCodes = $response['error-codes'] ?? [];

        if (!\is_bool($success)) {
            throw new \InvalidArgumentException(sprintf(
                '`success` property is not valid, got: `%s`',
                \get_debug_type($success),
            ));
        }

        if (isset($response['hostname']) && \is_string($response['hostname'])) {
            $hostname = $response['hostname'] !== ''
                ? $response['hostname']
                : null;
        } elseif (isset($response['hostname'])) {
            throw new \InvalidArgumentException(sprintf(
                '`hostname` property is not valid, got: `%s`',
                \get_debug_type($response['hostname']),
            ));
        } else {
            $hostname = null;
        }

        if (!\is_array($errorCodes)) {
            throw new \InvalidArgumentException(sprintf(
                '`error-codes` property is not valid, got: `%s`',
                \get_debug_type($response['error-codes']),
            ));
        }

        foreach ($errorCodes as $k => $v) {
            try {
                $errorCodes[$k] = ErrorCode::from($v);
            } catch (\ValueError $e) {
                throw new \InvalidArgumentException(sprintf(
                    'Error code `%s` is not recognised',
                    $v,
                ), 0, $e);
            }
        }

        usort($errorCodes, function (ErrorCode $a, ErrorCode $b) {
            return $a->name <=> $b->name;
        });

        return new self(
            $success,
            $errorCodes,
            $challengeTime,
            $hostname,
        );
    }

    public function isError(ErrorCode $errorCode): bool
    {
        return !$this->success
            && \in_array($errorCode, $this->errorCodes, true);
    }
}
