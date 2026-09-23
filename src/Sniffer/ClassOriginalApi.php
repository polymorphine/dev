<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Sniffer;

use PHP_CodeSniffer\Tokenizers\PHP;


class ClassOriginalApi
{
    private ClassInfo  $class;
    private ClassFiles $files;

    /**
     * @param ClassInfo $class
     */
    public function __construct(ClassInfo $class)
    {
        $this->class = $class;
        $this->files = new ClassFiles();
    }

    /**
     * @return list<string>
     */
    public function methodNames(): array
    {
        $methods = $this->class->apiMethods();
        return array_values(array_diff($methods, $this->inheritedMethods()));
    }

    private function inheritedMethods(?ClassInfo $ancestor = null): array
    {
        $methods = $ancestor ? $ancestor->apiMethods() : [];
        $class   = $ancestor ?: $this->class;
        $parent  = $this->classInfo($class->parentName());
        $methods = $parent ? [...$methods, ...$this->inheritedMethods($parent)] : $methods;
        return $ancestor ? $methods : [...$methods, ...$this->interfaceMethods()];
    }

    private function interfaceMethods(?ClassInfo $ancestor = null): array
    {
        $class   = $ancestor ?: $this->class;
        $methods = [];
        foreach ($class->interfaces() as $interface) {
            if (!$info = $this->classInfo($interface)) { continue; }
            $methods = [...$methods, ...$info->apiMethods(), ...$this->interfaceMethods($info)];
        }
        return $methods;
    }

    private function classInfo(string $className): ?ClassInfo
    {
        $filename = $className ? $this->files->filename($className) : null;
        if (!$filename || !is_file($filename)) { return null; }

        $tokenizer = new PHP(file_get_contents($filename), null);
        return ClassInfo::fromTokens(new Tokens($tokenizer->getTokens()));
    }
}
