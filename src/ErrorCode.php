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
    // Your secret key is missing.
    case SECRET_MISSING = 'missing-input-secret';

    // Your secret key is invalid or malformed.
    case SECRET_INVALID = 'invalid-input-secret';

    // The response parameter (verification token) is missing.
    case RESPONSE_MISSING = 'missing-input-response';

    // The response parameter (verification token) is invalid or malformed.
    case RESPONSE_INVALID = 'invalid-input-response';

    // The request is invalid or malformed.
    case BAD_REQUEST = 'bad-request';

    // The remoteip parameter is missing.
    case REMOTE_IP_MISSING = 'missing-remoteip';

    // The remoteip parameter is not a valid IP address or blinded value.
    case REMOTE_IP_INVALID = 'invalid-remoteip';

    // The response parameter (verification token) was already verified once.
    case RESPONSE_ALREADY_SEEN = 'already-seen-response';

    // You have used a testing sitekey but have not used its matching secret.
    case TESTING_INVALID_SECRET = 'not-using-dummy-passcode';

    // The sitekey is not registered with the provided secret.
    case SECRET_MISMATCH = 'sitekey-secret-mismatch';

    // The response parameter (verification token) is expired. (120s default)
    case EXPIRED_INPUT_RESPOSE = 'expired-input-response';
}
