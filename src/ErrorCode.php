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
 * @copyright 2024 Wider Plan
 * @license   MIT
 *
 * @link https://docs.hcaptcha.com/#siteverify-error-codes-table
 */
enum ErrorCode: string
{
    case SECRET_MISSING = 'missing-input-secret';
    case SECRET_INVALID = 'invalid-input-secret';
    case RESPONSE_MISSING = 'missing-input-response';
    case RESPONSE_INVALID = 'invalid-input-response';
    case BAD_REQUEST = 'bad-request';
    case REMOTE_IP_MISSING = 'missing-remoteip';
    case REMOTE_IP_INVALID = 'invalid-remoteip';
    case RESPONSE_ALREADY_SEEN = 'invalid-or-already-seen-response';
    case TESTING_INVALID_SECRET = 'not-using-dummy-passcode';
    case SECRET_MISMATCH = 'sitekey-secret-mismatch';
}
