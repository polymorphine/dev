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
    public function setUpBeforeClass(): void
    {

    }

    public static $publicStatic;

    private function doPrivate()
    {

    }

    private $value;

    public static function doPublicStatic(): void
    {

    }

    protected static $staticValue;
    protected function doProtected()
    {

    }

    private const PRIVATE_CONS_VALUE = 'private-value';

    public function doPublic(): void
    {

    }

    public function __construct()
    {

    }

    public ?string $publicValue = null;

    protected static function doProtectedStatic(): int
    {
        return 1;
    }
    const CONST_VALUE = 'value';
    public const PUB_CONS_VALUE = 'pub-value';

    public static function instance(): self
    {
        return new self();
    }

    private function setUp()
    {

    }
}
