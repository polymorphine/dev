<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev;

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use SplFileInfo;


final class FixerSetup
{
    private const LINE_SEPARATION = ['method' => 'one', 'trait_import' => 'none', 'case' => 'none'];
    private const BLANK_LINE_TOKENS = [
        'break', 'continue', 'extra', 'return', 'throw', 'use', 'switch', 'case', 'default', 'comma',
        'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block', 'attribute'
    ];
    private const HEADER = <<<'TPL'
        This file is part of {package.name} package.
        
        (c) {author.name} <{author.email}>
        
        This source file is subject to the MIT license that is bundled
        with this source code in the file LICENSE.
        TPL;

    private static array $rules = [
        '@Symfony'                              => true,
        'align_multiline_comment'               => true,
        'backtick_to_shell_exec'                => true,
        'blank_line_before_statement'           => false,
        'class_attributes_separation'           => ['elements' => self::LINE_SEPARATION],
        'combine_consecutive_issets'            => true,
        'combine_consecutive_unsets'            => true,
        'compact_nullable_type_declaration'     => true,
        'concat_space'                          => ['spacing' => 'one'],
        'echo_tag_syntax'                       => false,
        'explicit_indirect_variable'            => true,
        'explicit_string_variable'              => false,
        'final_internal_class'                  => true,
        'function_to_constant'                  => true,
        'global_namespace_import'               => true,
        'header_comment'                        => false,
        'heredoc_to_nowdoc'                     => true,
        'increment_style'                       => false,
        'list_syntax'                           => ['syntax' => 'short'],
        'method_chaining_indentation'           => false,
        'method_argument_space'                 => ['on_multiline' => 'ensure_fully_multiline'],
        'modernize_types_casting'               => true,
        'multiline_comment_opening_closing'     => true,
        'no_extra_blank_lines'                  => ['tokens' => self::BLANK_LINE_TOKENS],
        'no_homoglyph_names'                    => true,
        'no_null_property_initialization'       => true,
        'no_php4_constructor'                   => true,
        'no_superfluous_elseif'                 => true,
        'no_superfluous_phpdoc_tags'            => false,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else'                       => true,
        'no_useless_return'                     => true,
        'non_printable_character'               => ['use_escape_sequences_in_strings' => true],
        'ordered_class_elements'                => false,
        'ordered_imports'                       => false,
        'php_unit_strict'                       => false,
        'php_unit_method_casing'                => false,
        'php_unit_namespaced'                   => true,
        'php_unit_test_annotation'              => false,
        'php_unit_test_class_requires_covers'   => false,
        'phpdoc_add_missing_param_annotation'   => true,
        'phpdoc_order'                          => true,
        'phpdoc_types_order'                    => ['null_adjustment' => 'always_first', 'sort_algorithm' => 'none'],
        'pow_to_exponentiation'                 => true,
        'psr_autoloading'                       => true,
        'simplified_null_return'                => false,
        'single_line_after_imports'             => false,
        'single_line_comment_style'             => true,
        'strict_comparison'                     => true,
        'strict_param'                          => true,
        'string_implicit_backslashes'           => ['single_quoted' => 'ignore'],
        'ternary_to_null_coalescing'            => true,
        'trailing_comma_in_multiline'           => false,
        'yoda_style'                            => false
    ];

    private static Config $config;

    /**
     * @param string      $rootDirectory Path to root project directory
     * @param null|string $file          Fixed file path to adjust filtering when outside root directory
     *
     * @see ./polymorphine-csfixer
     */
    public static function init(string $rootDirectory, ?string $file = null): void
    {
        self::$config = self::configInstance($rootDirectory, $file);
    }

    /**
     * @return Config
     *
     * @see ./cs-fixer.php
     */
    public static function config(): Config
    {
        return self::$config ??= self::configInstance(getcwd());
    }

    private static function configInstance(string $rootDirectory, ?string $file = null): Config
    {
        self::$rules['header_comment'] = self::fileHeader($rootDirectory) ?: false;
        $filesLocation = $file ? self::resolveLocation($rootDirectory, $file) : $rootDirectory;

        $testsPath = $filesLocation . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR;
        $excludeSamples = static function (SplFileInfo $file) use ($testsPath) {
            $filePath   = $file->getPathname();
            $samplesDir = DIRECTORY_SEPARATOR . 'code-samples' . DIRECTORY_SEPARATOR;
            return strpos($filePath, $testsPath) !== 0 || strpos($filePath, $samplesDir) === false;
        };

        $finder = Finder::create()->filter($excludeSamples)->in($filesLocation);
        return (new Config())
            ->setUsingCache(false)
            ->setRiskyAllowed(true)
            ->setFinder($finder)
            ->registerCustomFixers(self::customFixers($testsPath))
            ->setRules(self::$rules);
    }

    private static function fileHeader(string $rootDirectory): array
    {
        $metaFile = $rootDirectory . DIRECTORY_SEPARATOR . '.github' . DIRECTORY_SEPARATOR . 'skeleton.json';
        $contents = is_file($metaFile) ? file_get_contents($metaFile) : false;
        $metaData = $contents ? (json_decode($contents, true) ?? []) : [];
        if (!$metaData) { return []; }

        $tokenize     = static fn (string $value): string => sprintf('{%s}', $value);
        $placeholders = array_map($tokenize, array_keys($metaData));
        if (!$placeholders) { return []; }

        return [
            'comment_type' => 'comment',
            'header'       => str_replace($placeholders, array_values($metaData), self::HEADER)
        ];
    }

    private static function resolveLocation(string $rootDirectory, string $file): string
    {
        $inRoot  = substr($file, 0, strlen($rootDirectory)) === $rootDirectory;
        $tempDir = $inRoot ? false : strpos($file, DIRECTORY_SEPARATOR . 'PHP CS Fixertemp');
        if (!$tempDir) { return $rootDirectory; }
        $length = strpos($file, DIRECTORY_SEPARATOR, $tempDir + 17);
        if (!$length) { return $rootDirectory; }
        return substr($file, 0, $length);
    }

    private static function customFixers(string $testsPath): array
    {
        self::$rules['Polymorphine/double_line_before_class_definition']     = true;
        self::$rules['Polymorphine/no_trailing_comma_after_multiline_array'] = true;
        self::$rules['Polymorphine/multi_ordered_class_elements']            = true;
        self::$rules['Polymorphine/named_constructors_first_static']         = true;
        self::$rules['Polymorphine/aligned_method_chain']                    = true;
        self::$rules['Polymorphine/aligned_assignments']                     = true;
        self::$rules['Polymorphine/aligned_array_values']                    = true;
        self::$rules['Polymorphine/aligned_properties']                      = true;
        self::$rules['Polymorphine/short_conditions_single_line']            = true;
        self::$rules['Polymorphine/declare_strict_first_line']               = true;
        self::$rules['Polymorphine/brace_after_multiline_param_method']      = true;

        $srcOrder = [
            'use_trait', 'case', 'constant_public', 'constant_protected', 'constant_private',
            'property_public_static', 'property_protected_static', 'property_private_static',
            'method_public_static', 'method_protected_static', 'method_private_static',
            'property_public', 'property_protected', 'property_private',
            'construct', 'magic', 'method_public', 'destruct', 'method_protected', 'method_private'
        ];

        $testOrder = [
            'use_trait', 'constant_public', 'constant_protected', 'constant_private',
            'property_public_static', 'property_protected_static', 'property_private_static',
            'property_public', 'property_protected', 'property_private',
            'construct', 'phpunit', 'magic', 'destruct',
            'method_public', 'method_public_static',
            'method_protected', 'method_protected_static',
            'method_private', 'method_private_static'
        ];

        return [
            new Fixer\DoubleLineBeforeClassDefinitionFixer(),
            new Fixer\NoTrailingCommaInMultilineArrayFixer(),
            new Fixer\MultiOrderedClassElementsFixer($testsPath, $srcOrder, $testOrder),
            new Fixer\NamedConstructorsFirstStaticFixer(),
            new Fixer\AlignedMethodChainFixer(),
            new Fixer\AlignedAssignmentsFixer(),
            new Fixer\AlignedArrayValuesFixer(),
            new Fixer\AlignedTypedPropertiesFixer(),
            new Fixer\ShortConditionsSingleLineFixer(),
            new Fixer\DeclareStrictFirstLineFixer(),
            new Fixer\BraceAfterMultilineParamMethodFixer()
        ];
    }
}
