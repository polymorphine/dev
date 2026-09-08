<?php

namespace Some\NamespaceX;

use BrokenNamingConventions\Sniffs\Category\Sniff;
use Exception;
use Closure;


interface PhpDocTagValidation
{
    /**
     * @param iterable $iterable Argument description
     * @param mixed $another
     * @param Foo\Bar $foobar Should be valid
     *
     * @return mixed
     */
    public function incorrectOrderPhpDoc(iterable $iterable, Foo\Bar $foobar, $another);

    /**
     * @param null|int $integer Argument description
     * @param Foo\Bar $foobar Should be valid
     * @param mixed $another
     *
     * @return mixed
     */
    public function correctPhpDoc(?int $integer, Foo\Bar $foobar, $another): void;

    /**
     * @param callable(Foo): Test $function correct definition
     * @param Closure(all, types, combined...): bool $closure Description of returned callaback
     * @param int $unexpected Type not in method signature
     * @param list<string> $type List
     *
     * @return array<string, mixed>
     */
    public function correctParamCallback(callable $function, Closure $closure, array $type): array;

    /**
     * @param callable(Foo): Test $function correct definition
     */
    public function missingRequiredReturn(callable $function, Foo\Bar $another): array;

    /**
     * @param array{
     *            callback: Closure(string): void,
     *            index: int
     *        } $methodParam Variable multiline definition
     *
     * @throws Exception
     *
     * @return array{
     *             strict_comparison: bool,
     *             strict_param: bool,
     *             string_implicit_backslashes: array{single_quoted: string},
     *             sniff_class: Sniff
     *         } Return type definition
     */
    public function multilineArrayDefinition(array $methodParam): array;
}
