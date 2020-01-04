<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Tests;

use PHPUnit\Framework\TestCase;
use Polymorphine\CodeStandards\FixerFactory;
use PhpCsFixer\ConfigInterface;


class FixerFactoryTest extends TestCase
{
    public function testConfigInstantiation()
    {
        $this->assertInstanceOf(ConfigInterface::class, FixerFactory::createFor('package/name', __DIR__));
    }
}
