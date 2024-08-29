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

namespace WiderPlan\Hcaptcha\Exception;

use Psr\Http\Message\ResponseInterface;

/**
 * @copyright 2024 Wider Plan
 * @license   MIT
 */
final class NotSupportedResponseException extends \RuntimeException
{
    public static function fromResponse(ResponseInterface $response): self
    {
        return new self(sprintf(
            'Response was not a successful JSON response, got: status `%d` with content type `%s`',
            $response->getStatusCode(),
            $response->getHeaderLine('Content-Type'),
        ));
    }
}
