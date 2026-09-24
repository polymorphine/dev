<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\Sniffer\ClassInfo;

use PHPUnit\Framework\TestCase;
use Polymorphine\Dev\Sniffer\ClassInfo\OriginalApi;
use Polymorphine\Dev\Sniffer\ClassInfo\NameResolution;
use Polymorphine\Dev\Sniffer\Tokens;
use Polymorphine\Dev\Tests\Fixtures\Tools\SnifferTokens;


class OriginalApiTest extends TestCase
{
    public function test_AccumulatePublicMethods()
    {
        $file   = dirname(__DIR__, 2) . '/Fixtures/code-samples/Sniffs/PhpDocClass.php';
        $tokens = new Tokens(SnifferTokens::tokenizedFile($file)->getTokens());
        $api    = new OriginalApi(NameResolution::fromTokens($tokens));

        $this->assertSame([
            'staticConstructor', 'originalMethodWithDoc', 'originalMethodWithoutDoc',
            'originalMethodWithoutRequiredDoc', 'iterate'
        ], $api->methodNames());
    }
}
