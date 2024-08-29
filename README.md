hCaptcha API Client
===================


Usage
-----

### Basic

With `php-http/discovery` installed this package will be leveraged to attempt to
use the most appropriate implementations of [PSR-7][] and [PSR-18][].

```sh
composer require widerplan/hcaptcha php-http/discovery
```

```php
<?php

declare(strict_types=1);

use WiderPlan\Hcaptcha\Client;

$client = Client::create(getenv('HCAPTCHA_SECRET'));
$result = $client->verify($_POST['h-captcha-response'], getenv('HCAPTCHA_SITE_KEY'));

if ($result->success) {
    // Perform protected action...
}
```

### With custom components

Instead of relying on the automatic discovery you can wire up your chosen implementations.

```sh
composer require widerplan/hcaptcha symfony/http-client slim/psr7
```

```php
<?php

declare(strict_types=1);

use Slim\Psr7\Factory;
use Symfony\Component\HttpClient\Psr18Client;
use WiderPlan\Hcaptcha\Client;

$httpClient = new Psr18Client(
    null,
    new Factory\ResponseFactory(),
    new Factory\StreamFactory(),
);
$client = Client::create(getenv('HCAPTCHA_SECRET'), $httpClient);
$result = $client->verify($_POST['h-captcha-response'], getenv('HCAPTCHA_SITE_KEY'));

if ($result->success) {
    // Perform protected action...
}
```

[PSR-7]: https://www.php-fig.org/psr/psr-7/
[PSR-18]: https://www.php-fig.org/psr/psr-18/
