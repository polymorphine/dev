# Polymorphine/Dev
[![Latest stable release](https://poser.pugx.org/polymorphine/dev/version)](https://packagist.org/packages/polymorphine/dev)
[![Build status](https://github.com/polymorphine/dev/workflows/build/badge.svg)](https://github.com/polymorphine/dev/actions)
[![Coverage status](https://coveralls.io/repos/github/polymorphine/dev/badge.svg?branch=develop)](https://coveralls.io/github/polymorphine/dev?branch=develop)
[![PHP version](https://img.shields.io/packagist/php-v/polymorphine/dev.svg)](https://packagist.org/packages/polymorphine/dev)
[![LICENSE](https://img.shields.io/github/license/polymorphine/dev.svg?color=blue)](LICENSE)
### Development tools & coding standard scripts for Polymorphine libraries

- [PHPUnit](https://github.com/sebastianbergmann/phpunit) testing library.
- Combination of [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
  and [CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer) with custom
  settings added as dev dependency of Polymorphine packages.
- Package skeleton scripted with [Skeletons](https://github.com/shudd3r/skeletons) engine.

### Installation with [Composer](https://getcomposer.org/)
```bash
composer require --dev polymorphine/dev
```
### Project initialization (skeleton)
```bash
vandor/bin/polymorphine-skeleton init
```
After initialization this tool will be run mostly to check project's file
consistency. Run `help` instead of `init` for more information about this
tool.

### Composer scripts
To run project tests you could use these composer scripts - see
[`composer.json`](composer.json) to learn equivalent tool commands called
directly.

> [!NOTE]
> Calling these tools from project's root directory would require path to
> `bin` directory, like `vendor/bin/phpunit` instead of `phpunit`. Unless
> installed globally, there's no point registering executable paths for
> each project.

- PhpUnit tests:
  ```bash
  composer test-php
  ```
- Coding standard tests:
  ```bash
  composer test-cs
  ```
- Project skeleton validation:
  ```bash
  composer test-skeleton
  ```

### PHP-CS-Fixer
`PHP-CS-Fixer` will automatically fix code formatting, and `CodeSniffer`
will check style errors that need to be adjusted manually like: naming
conventions, line lengths and some
[phpDoc constraints](#codesniffer-custom-phpdoc-requirements).

Fixer needs project scope configuration to set up factory with package name
used in a file docBlock headers and absolute path to its root directory.
Add [`cs-fixer.php.dist`](cs-fixer.php.dist) configuration file similar to
the one supplied with this package to root directory of your project.

##### PhpStorm setup (inspection & External tool)
Checking code formatting live might be setup in PhpStorm
`Settings > PHP > Quality Tools > PHP CS Fixer`:
- Turn on inspection
- Set path to `vendor\bin\php-cs-fixer` script in <kbd>...</kbd> menu
- Limit file extensions to `php`
- Set `Custom` coding ruleset to `cs-fixer.php.dist` in your project root
  similar to the one included in the package.

Alternatively you can setup manual trigger to fix/check current file as
**external tools** in `Settings > Tools > External Tools`. Here's now to
configure `php-cs-fixer` environment:
- **Program:** set path to `$ProjectFileDir$/vendor/bin/php-cs-fixer` or
   `...php-cs-fixer.bat` for Windows
- **Parameters:** set command fixing currently opened project file as
   `-v --config=cs-fixer.php.dist fix $FilePathRelativeToProjectRoot$`
  If you want to add another tool entry that would only check code formatting
  the command above would need additional `--dry-run` switch up front.
- **Working directory:** set to `$ProjectFileDir$`
- Add keyboard shortcuts to run commands in
  `Settings > Keymap > External Tools`

### CodeSniffer
`CodeSniffer` tool is used only because of Fixer limitations. Not every style
constraint violation can be automatically fixed, so this tool will inform you
about inconsistencies that require manual fixing.

[`phpcs.xml`](phpcs.xml) file that comes with this package is the configuration
for **all projects** using `polymorphine/dev` as a composer dependency
(by default located in project's vendor directory). Providing absolute path to
this file in commands might be inconvenient so
[`polymorphine-phpcs`](polymorphine-phpcs) binary wrapper is provided.

##### PhpStorm setup (inspection)
Code sniffer does not change the code by itself, so it's better to set is as
one of the inspections in `Settings > PHP > Quality Tools > PHP_CodeSniffer`:
- Turn on inspection
- Set path to `vendor\bin\polymorphine-phpcs` script in <kbd>...</kbd> menu
- Limit file extensions to `php`
- Set current **project root directory** as a `Custom` coding standard

> [!NOTE]
> Setting project root directory as a custom coding standard is a workaround
> for a globally installed script to find project's root directory (as current
> working directory is not available when called by IDE).

#### CodeSniffer custom PhpDoc requirements
- Original public API method signatures should include phpDoc block comments.
  These comments are required for methods with composite type declarations
  like: `callable`, `Closure`, `array`, `iterable`, `Traversable`, `Iterator`
  and `Generator`. 
  
  > [!NOTE]
  > **Original method** is the one that introduces new signature - it doesn't
  override parent's method nor provides implementation for method defined by
  an interface. In case of traits every public method is considered original
  API.
- PhpDoc's `@param` and `@return` tags can be omitted unless documenting one
  of composite types - for example:
    ```php
    /**
     * @param callable(int, Foo\Bar): list<string> $callback Required argument tag
     *
     * @return array<string, Closure(string): ReturnType> Associative map of callables
     */
    public function callbackMap(int $value, callable $callback, Foo\Bar $bar): array
    ```
