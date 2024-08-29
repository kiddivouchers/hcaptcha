<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        'header_comment' => [
            'header' => <<<'EOT'
            This file is part of the hCaptcha API Client package.

            (c) Wider Plan <development@widerplan.com>

            For the full copyright and license information, please view the LICENSE
            file that was distributed with this source code.
            EOT,
            'comment_type' => 'comment',
            'location' => 'after_open',
            'separate' => 'both'
        ],
    ])
    ->setFinder($finder)
;
