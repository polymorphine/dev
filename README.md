# Polymorphine/Dev
[![Latest stable release](https://poser.pugx.org/polymorphine/dev/version)](https://packagist.org/packages/polymorphine/dev)
[![Build status](https://github.com/polymorphine/dev/workflows/build/badge.svg)](https://github.com/polymorphine/dev/actions)
[![Coverage status](https://coveralls.io/repos/github/polymorphine/dev/badge.svg?branch=develop)](https://coveralls.io/github/polymorphine/dev?branch=develop)
[![PHP version](https://img.shields.io/packagist/php-v/polymorphine/dev.svg)](https://packagist.org/packages/polymorphine/dev)
[![LICENSE](https://img.shields.io/github/license/polymorphine/dev.svg?color=blue)](LICENSE)
### Development tools & coding standard scripts for Polymorphine libraries

- [PHPUnit](https://github.com/sebastianbergmann/phpunit) testing library.
- Combination of [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
  and [CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) with custom
  settings added as dev dependency of Polymorphine packages.
- Package skeleton scripted with [Skeletons](https://github.com/shudd3r/skeletons) engine.

### Installation with [Composer](https://getcomposer.org/)
```bash
composer require --dev polymorphine/dev
```

#### PHP-CS-Fixer
`PHP-CS-Fixer` will automatically fix code formatting, and `CodeSniffer`
will check style errors that need to be adjusted manually like: naming
conventions, line lengths and some [phpDoc constraints](#codesniffer-custom-phpdoc-requirements).

Fixer needs project scope configuration to set up factory with package name
used in a file docBlock headers and absolute path to its root directory.
Add [`cs-fixer.php.dist`](cs-fixer.php.dist) configuration file similar to
the one supplied with this package to root directory of your project.

Use this command to run fixer for given file or directory (path):
```
vendor/bin/php-cs-fixer fix -v --config=cs-fixer.php.dist --using-cache=no --path-mode=intersection path
```

#### CodeSniffer
`CodeSniffer` tool is used only because of Fixer limitations. Not every style
constraint violation can be automatically fixed, so this tool will inform you
about inconsistencies that need manual fixing.

[`phpcs.xml`](phpcs.xml) file is the configuration for all projects using this
package as a composer dependency (in default vendor directory). However, console
command running CodeSniffer requires the absolute path to this config file. For example
Github Action command may build the absolute path with `$GITHUB_WORKSPACE` env variable:
```
vendor/bin/phpcs --extensions=php --standard=$GITHUB_WORKSPACE/vendor/polymorphine/dev/phpcs.xml path
```

#### IDE Setup (PhpStorm)
###### PHP-CS-Fixer
Use `Setting > Tools > External Tools` to configure `php-cs-fixer` environment:
- `Program:` add path to `vendor/bin/php-cs-fixer` (for Windows: `vendor/bin/php-cs-fixer.bat`)
- `Parameters:` add command fixing currently opened project file:
    ```
    fix -v --config=cs-fixer.php.dist --using-cache=no --path-mode=intersection "$FileDir$\$FileName$"
    ```
    If you want to add another tool entry with checking command the command above would
    need additional `--dry-run` switch.
- `Working directory:` set to `$ProjectFileDir$`
- Add keyboard shortcuts to run commands in `Settings > Keymap > External Tool`

###### Code Sniffer
Code sniffer does not change the code by itself, so it's better to set is as one of the
inspections:
- Add path to local `phpcs` script in `Settings > Languages & Frameworks > PHP > Code Sniffer`
- Set custom ruleset in `Settings > Editor > Inspections > PHP Code Sniffer validation`
  with absolute path to [`phpcs.xml`](phpcs.xml) file provided with this package - as a
  project dependency it will be located in `vendor/polymorphine/cs/` directory, and
  composer's autoload script will be two levels above.

#### CodeSniffer custom PhpDoc requirements
- Original public method signatures require phpDoc block comments (their contents are not inspected).
  Original method is the one that introduces new signature - it doesn't override parent's method nor
  provides implementation for method defined by an interface. In case of traits every method is
  considered original.
- PhpDoc's `@param` and `@return` tags with `callable` or `Closure` type require additional description
  formatted similar to short lambda notation - example:
    ```php
    /**
     * @param Closure $callback fn(Param1Type, Param2Type) => ReturnType
     *
     * @return callable fn(bool) => \FQN\Return\Type
     */
    ```
