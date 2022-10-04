<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests;

use PHPUnit\Framework\TestCase;
use Polymorphine\Dev\FixerFactory;
use PhpCsFixer\ConfigInterface;


class FixerFactoryTest extends TestCase
{
    public function testConfigInstantiation()
    {
        $this->assertInstanceOf(ConfigInterface::class, FixerFactory::createFor('package/name', __DIR__));
    }

    public function testConfigFinder_IgnoresCodeSamples()
    {
        $excluded = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/Fixtures/code-samples');
        $finder   = FixerFactory::createFor('package/name', dirname(__DIR__))->getFinder();

        $this->assertTrue($finder->hasResults());

        foreach ($finder->getIterator() as $file) {
            $this->assertFalse(strpos($file->getPath(), $excluded));
        }
    }
}
