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

/**
 * hCaptcha API client for testing.
 *
 * Allows a Result to be set for given responses which are then returned, the
 * response used once behaviour is emulated to ensure you only validate a
 * response once.
 *
 * @copyright 2024 Wider Plan
 * @license   MIT
 */
final class TestClient implements ClientInterface
{
    /** @var array<string,Result> */
    private array $seen = [];

    private ?Result $unknown = null;

    public function __construct(
        /** @var array<string,Result> */
        private array $results,
    ) {}

    /**
     * Set result to use for a specific response.
     */
    public function setResult(string $response, Result $result): void
    {
        unset($this->seen[$response]);
        $this->results[$response] = $result;
    }

    /**
     * Set result to use when a response doesn't have an assigned result.
     */
    public function setUnknownResult(?Result $result): void
    {
        $this->unknown = $result;
    }

    public function verify(string $response, ?string $siteKey = null, ?string $remoteIp = null): Result
    {
        if (isset($this->seen[$response])) {
            return new Result(
                false,
                [ErrorCode::RESPONSE_ALREADY_SEEN],
                $this->seen[$response]->challengeTime,
                $this->seen[$response]->hostname,
            );
        }

        if (isset($this->results[$response])) {
            $this->seen[$response] = $this->results[$response];
            unset($this->results[$response]);

            return $this->seen[$response];
        }

        if ($this->unknown !== null) {
            $this->seen[$response] = $this->unknown;

            return $this->seen[$response];
        }

        throw new \RuntimeException('No response found');
    }
}
