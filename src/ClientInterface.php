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
 * Client for the hCaptcha site verify API.
 *
 * @copyright 2024 Wider Plan
 * @license   MIT
 */
interface ClientInterface
{
    /**
     * Test if the CAPTCHA response is valid.
     *
     * @param string|null $siteKey  If supplied check the response belongs to
     *                              this site key.
     * @param string|null $remoteIp Optionally validate against the client IP
     *                              address.
     */
    public function verify(string $response, ?string $siteKey = null, ?string $remoteIp = null): Result;
}
