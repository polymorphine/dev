# Polymorphine/Dev
[![Latest Stable Version](https://poser.pugx.org/polymorphine/dev/version)](https://packagist.org/packages/polymorphine/dev)
[![Build status](https://github.com/polymorphine/dev/workflows/build/badge.svg)](https://github.com/polymorphine/dev/actions)
[![Coverage status](https://coveralls.io/repos/github/polymorphine/dev/badge.svg?branch=develop)](https://coveralls.io/github/polymorphine/dev?branch=develop)
[![PHP version](https://img.shields.io/packagist/php-v/polymorphine/dev.svg)](https://packagist.org/packages/polymorphine/dev)
[![LICENSE](https://img.shields.io/github/license/polymorphine/dev.svg?color=blue)](LICENSE)
### Development tools & coding standard scripts for Polymorphine libraries

## Coding Standards
Combination of [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
and [CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) with custom
settings added as dev dependency of Polymorphine packages.

`PHP-CS-Fixer` will automatically fix code formatting, and `CodeSniffer`
will check style errors that need to be adjusted manually like: naming
conventions, line lengths and some [phpDoc constraints](#codesniffer-custom-phpdoc-requirements).

#### CI integration
Add `cs-fixer.php.dist` configuration file similar to the one supplied
with this package to root directory directory of your project - see: [`cs-fixer.php.dist`](cs-fixer.php.dist).
It will setup `PHP-CS-Fixer` factory with package name used in docBlock
headers and set absolute path for its working directory.

Add following composer script command to package's `composer.json` file:
```json
    "scripts": {
        "style-check": "polymorphine-cs"
    }
```
It will perform dry run (without fixes) checks running both `php-cs-fixer`
and `phpcs` commands.

Configure CI checks with it. For example to check both `src/` and `tests/`
top level directories (and their subdirectories) using TravisCI configure
`.travis.yml` to call script twice:
```yaml
    script:
      - composer style-check src
      - composer style-check tests
```

You can specify concrete files after selecting directory. Listed files
located outside directory will be ignored. This way you can tell Travis
to check only recently changed files:
```yaml
    before_script:
      - CHANGED_FILES=$(git diff --name-only --diff-filter=ACMRTUXB $TRAVIS_COMMIT_RANGE)
    script:
      - composer style-check src ${CHANGED_FILES}
      - composer style-check tests/unit ${CHANGED_FILES}
```

Style checking will be skipped if none of `CHANGED_FILES` is located within
`src` or `tests/unit` directories. For example command below will check
only `UsernameValidation.php` file:
```shell script
composer style-check src/Validation src/Validation/UsernameValidation.php src/app/Config.php
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
  with path to [`phpcs.xml`](phpcs.xml) file provided with this package - as a project
  dependency it will be located in `vendor/polymorphine/cs/` directory, and composer's
  autoload script will be two levels above.

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
