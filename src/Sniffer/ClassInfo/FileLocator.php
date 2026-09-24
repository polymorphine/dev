<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Sniffer\ClassInfo;

use ReflectionClass;
use ReflectionException;


class FileLocator
{
    private object $streamWrapper;

    /**
     * @param class-string $className
     *
     * @throws ReflectionException
     *
     * @return null|string Filename with class definition (without loading the class)
     */
    public function filename(string $className): ?string
    {
        if ($this->alreadyLoaded($className)) {
            return (new ReflectionClass($className))->getFileName() ?: null;
        }

        set_error_handler(static fn (): bool => true);
        try {
            return $this->capturedFileName($className);
        } finally {
            ($this->streamWrapper)::$locatedFile = null;
            @stream_wrapper_restore('file');
            restore_error_handler();
        }
    }

    private function alreadyLoaded(string $className): bool
    {
        return class_exists($className, false)
            || interface_exists($className, false)
            || trait_exists($className, false);
    }

    private function capturedFileName(string $className): ?string
    {
        stream_wrapper_unregister('file');
        $wrapper = $this->streamWrapper();
        stream_wrapper_register('file', get_class($wrapper));

        foreach (spl_autoload_functions() as $autoloadFunction) {
            $autoloadFunction($className);
            if ($wrapper::$locatedFile) { break; }
        }
        return $wrapper::$locatedFile;
    }

    private function streamWrapper(): object
    {
        // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
        // phpcs:disable Sniffer.PhpDoc.ValidTags.MissingAPI
        return $this->streamWrapper ??= new class {
            public static ?string $locatedFile = null;

            // phpcs:disable Sniffer.NamingConventions.ValidVariableName.NotCamelCaps
            public function stream_open($path, $mode, $options, &$opened_path): bool
            {
                self::$locatedFile = $path;
                return false;
            }

            public function url_stat($path, $flags)
            {
                stream_wrapper_restore('file');
                $result = ($flags & STREAM_URL_STAT_QUIET) === STREAM_URL_STAT_QUIET ? @stat($path) : stat($path);
                stream_wrapper_unregister('file');
                stream_wrapper_register('file', self::class);
                return $result;
            }
        };
    }
}
