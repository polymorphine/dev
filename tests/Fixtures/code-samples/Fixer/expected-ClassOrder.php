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

    protected static $staticValue;

    public static function instance(): self
    {
        return new self();
    }

    public static function doPublicStatic(): void
    {
    }

    protected static function doProtectedStatic(): int
    {
        return 1;
    }

    public ?string $publicValue = null;

    private $value;

    public function __construct()
    {
    }

    public function setUpBeforeClass(): void
    {
    }

    public function doPublic(): void
    {
    }

    protected function doProtected()
    {
    }

    private function doPrivate()
    {
    }

    private function setUp()
    {
    }
}
