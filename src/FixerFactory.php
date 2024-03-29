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


final class FixerFactory
{
    private static array $rules = [
        '@Symfony'                              => true,
        'align_multiline_comment'               => true,
        'backtick_to_shell_exec'                => true,
        'blank_line_before_statement'           => false,
        'braces'                                => ['allow_single_line_closure' => true],
        'combine_consecutive_issets'            => true,
        'combine_consecutive_unsets'            => true,
        'compact_nullable_typehint'             => true,
        'concat_space'                          => ['spacing' => 'one'],
        'escape_implicit_backslashes'           => true,
        'explicit_indirect_variable'            => true,
        'explicit_string_variable'              => false,
        'final_internal_class'                  => true,
        'function_to_constant'                  => true,
        'heredoc_to_nowdoc'                     => true,
        'increment_style'                       => false,
        'list_syntax'                           => ['syntax' => 'short'],
        'method_chaining_indentation'           => false,
        'method_argument_space'                 => ['on_multiline' => 'ensure_fully_multiline'],
        'modernize_types_casting'               => true,
        'multiline_comment_opening_closing'     => true,
        'no_extra_blank_lines'                  => [],
        'no_homoglyph_names'                    => true,
        'no_null_property_initialization'       => true,
        'no_php4_constructor'                   => true,
        'echo_tag_syntax'                       => false,
        'no_superfluous_elseif'                 => true,
        'no_superfluous_phpdoc_tags'            => false,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else'                       => true,
        'no_useless_return'                     => true,
        'non_printable_character'               => ['use_escape_sequences_in_strings' => true],
        'ordered_class_elements'                => true,
        'ordered_imports'                       => false,
        'php_unit_strict'                       => false,
        'php_unit_method_casing'                => false,
        'php_unit_namespaced'                   => true,
        'php_unit_test_annotation'              => false,
        'php_unit_test_class_requires_covers'   => false,
        'phpdoc_add_missing_param_annotation'   => true,
        'phpdoc_order'                          => true,
        'phpdoc_types_order'                    => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'pow_to_exponentiation'                 => true,
        'psr_autoloading'                       => true,
        'simplified_null_return'                => false,
        'single_line_after_imports'             => false,
        'single_line_comment_style'             => true,
        'strict_comparison'                     => true,
        'strict_param'                          => true,
        'ternary_to_null_coalescing'            => true,
        'trailing_comma_in_multiline'           => false,
        'yoda_style'                            => false
    ];

    public static function createFor(string $launchFile): Config
    {
        $workingDir = dirname($launchFile);

        self::setHeaderFrom($launchFile);

        self::$rules['no_extra_blank_lines']['tokens'] = [
            'break', 'continue', 'extra', 'return', 'throw', 'parenthesis_brace_block',
            'square_brace_block', 'curly_brace_block'
        ];

        self::$rules['Polymorphine/double_line_before_class_definition']     = true;
        self::$rules['Polymorphine/no_trailing_comma_after_multiline_array'] = true;
        self::$rules['Polymorphine/constructors_first']                      = true;
        self::$rules['Polymorphine/aligned_method_chain']                    = true;
        self::$rules['Polymorphine/aligned_assignments']                     = true;
        self::$rules['Polymorphine/aligned_array_values']                    = true;
        self::$rules['Polymorphine/aligned_properties']                      = true;
        self::$rules['Polymorphine/short_conditions_single_line']            = true;
        self::$rules['Polymorphine/declare_strict_first_line']               = true;
        self::$rules['Polymorphine/brace_after_multiline_param_method']      = true;

        $excludeSamples = function (SplFileInfo $file) use ($workingDir) {
            $filePath   = $file->getPath();
            $testsPath  = $workingDir . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR;
            $samplesDir = DIRECTORY_SEPARATOR . 'code-samples' . DIRECTORY_SEPARATOR;
            return strpos($filePath, $testsPath) !== 0 || strpos($filePath, $samplesDir) === false;
        };

        $config = new Config();
        return $config
            ->setRiskyAllowed(true)
            ->setRules(self::$rules)
            ->setFinder(Finder::create()->in($workingDir)->filter($excludeSamples))
            ->setUsingCache(false)
            ->registerCustomFixers([
                new Fixer\DoubleLineBeforeClassDefinitionFixer(),
                new Fixer\NoTrailingCommaInMultilineArrayFixer(),
                new Fixer\ConstructorsFirstFixer(),
                new Fixer\AlignedMethodChainFixer(),
                new Fixer\AlignedAssignmentsFixer(),
                new Fixer\AlignedArrayValuesFixer(),
                new Fixer\AlignedTypedPropertiesFixer(),
                new Fixer\ShortConditionsSingleLineFixer(),
                new Fixer\DeclareStrictFirstLineFixer(),
                new Fixer\BraceAfterMultilineParamMethodFixer()
            ]);
    }

    private static function setHeaderFrom(string $filename): void
    {
        self::$rules['header_comment'] = false;

        $contents    = file_get_contents($filename) ?: '';
        $headerStart = strpos($contents, "\n/*\n");
        $headerEnd   = strpos($contents, "\n */\n");
        if (!$headerStart || !$headerEnd) { return; }

        $header = substr($contents, $headerStart + 4, $headerEnd - $headerStart - 4);
        if (!$header) { return; }

        self::$rules['header_comment'] = [
            'comment_type' => 'comment',
            'header'       => str_replace([' * ', ' *'], '', $header)
        ];
    }
}
