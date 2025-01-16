<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\CodeSamples\Fixer;


class ClassOrder
{
    public const CONST_VALUE    = 'value';
    public const PUB_CONS_VALUE = 'pub-value';

    private const PRIVATE_CONS_VALUE = 'private-value';

    public static $publicStatic;

    public ?string $publicValue = null;

    protected static $staticValue;

    private $value;

    public function __construct()
    {
    }

    public function setUpBeforeClass(): void
    {
    }

    private function setUp()
    {
    }

    public static function instance(): self
    {
        return new self();
    }

    public static function doPublicStatic(): void
    {
    }

    public function doPublic(): void
    {
    }

    protected function doProtected()
    {
    }

    protected static function doProtectedStatic(): int
    {
        return 1;
    }

    private function doPrivate()
    {
    }
}
