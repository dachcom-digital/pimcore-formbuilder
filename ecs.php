<?php

use PhpCsFixer\Fixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

$header = <<<HEADER
This source file is available under two different licenses:
  - GNU General Public License version 3 (GPLv3)
  - DACHCOM Commercial License (DCL)
Full copyright and license information is available in
LICENSE.md which is distributed with this source code.

@copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
@license    GPLv3 and DCL
HEADER;

return ECSConfig::configure()
    ->withSets([SetList::CLEAN_CODE, SetList::PSR_12])
    ->withConfiguredRule(Fixer\Comment\HeaderCommentFixer::class, [
        'header' => $header,
        'comment_type' => 'comment'
    ])
    ->withConfiguredRule(Fixer\Basic\BracesFixer::class, [
        'allow_single_line_closure' => true,
    ])
    ->withConfiguredRule(Fixer\Operator\ConcatSpaceFixer::class, [
        'spacing' => 'one',
    ])
    ->withConfiguredRule(Fixer\Phpdoc\PhpdocAlignFixer::class, [
        'tags' => ['method', 'param', 'property', 'return', 'throws', 'type', 'var'],
    ])
    ->withConfiguredRule(Fixer\Operator\BinaryOperatorSpacesFixer::class, [
        'operators' => [
            '='  => 'single_space',
            '=>' => 'align',
        ]
    ])
    ->withConfiguredRule(Fixer\Operator\IncrementStyleFixer::class, [
        'style' => 'post',
    ])
    ->withConfiguredRule(Fixer\ClassNotation\ClassAttributesSeparationFixer::class, [
        'elements' => [
            'const'        => 'none',
            'method'       => 'one',
            'property'     => 'none',
            'trait_import' => 'none'
        ],
    ])
    ->withConfiguredRule(Fixer\ClassNotation\ClassDefinitionFixer::class, [
        'single_line' => true,
    ])
    ->withConfiguredRule(Fixer\Comment\SingleLineCommentStyleFixer::class, [
        'comment_types' => ['hash'],
    ])
    ->withConfiguredRule(Fixer\Alias\NoMixedEchoPrintFixer::class, [
        'use' => 'echo',
    ])
    ->withConfiguredRule(Fixer\Basic\NoTrailingCommaInSinglelineFixer::class, [
        'elements' => ['array_destructuring']
    ])
    ->withConfiguredRule(Fixer\NamespaceNotation\BlankLinesBeforeNamespaceFixer::class, [
        'min_line_breaks' => 2,
        'max_line_breaks' => 2
    ])
    ->withConfiguredRule(Fixer\Whitespace\TypeDeclarationSpacesFixer::class, [
        'elements' => ['function']
    ])
    ->withConfiguredRule(Fixer\Whitespace\NoExtraBlankLinesFixer::class, [
        'tokens' => ['curly_brace_block', 'extra', 'parenthesis_brace_block', 'square_brace_block', 'throw', 'use'],
    ])
    ->withRules([
        Fixer\PhpTag\BlankLineAfterOpeningTagFixer::class,
        Fixer\Operator\NewWithParenthesesFixer::class,
        Fixer\Operator\UnaryOperatorSpacesFixer::class,
        Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer::class,
        Fixer\Operator\StandardizeNotEqualsFixer::class,
        Fixer\Operator\TernaryOperatorSpacesFixer::class,
        Fixer\Operator\StandardizeIncrementFixer::class,
        Fixer\Whitespace\BlankLineBeforeStatementFixer::class,
        Fixer\Whitespace\ArrayIndentationFixer::class,
        Fixer\Whitespace\NoSpacesAroundOffsetFixer::class,
        Fixer\Whitespace\NoWhitespaceInBlankLineFixer::class,
        Fixer\CastNotation\CastSpacesFixer::class,
        Fixer\CastNotation\LowercaseCastFixer::class,
        Fixer\CastNotation\NoShortBoolCastFixer::class,
        Fixer\CastNotation\ShortScalarCastFixer::class,
        Fixer\LanguageConstruct\DeclareEqualNormalizeFixer::class,
        Fixer\ControlStructure\IncludeFixer::class,
        Fixer\ControlStructure\NoUnneededControlParenthesesFixer::class,
        Fixer\Casing\NativeFunctionCasingFixer::class,
        Fixer\Casing\MagicConstantCasingFixer::class,
        Fixer\Comment\NoEmptyCommentFixer::class,
        Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer::class,
        Fixer\Semicolon\NoEmptyStatementFixer::class,
        Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer::class,
        Fixer\Semicolon\SpaceAfterSemicolonFixer::class,
        Fixer\Semicolon\SemicolonAfterInstructionFixer::class,
        Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer::class,
        Fixer\ArrayNotation\NormalizeIndexBraceFixer::class,
        Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer::class,
        Fixer\ArrayNotation\TrimArraySpacesFixer::class,
        Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer::class,
        Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer::class,
        Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer::class,
        Fixer\Phpdoc\PhpdocIndentFixer::class,
        Fixer\Phpdoc\PhpdocInlineTagNormalizerFixer::class,
        Fixer\Phpdoc\PhpdocNoAccessFixer::class,
        Fixer\Phpdoc\PhpdocNoEmptyReturnFixer::class,
        Fixer\Phpdoc\PhpdocNoPackageFixer::class,
        Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer::class,
        Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer::class,
        Fixer\Phpdoc\PhpdocScalarFixer::class,
        Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer::class,
        Fixer\Phpdoc\PhpdocSummaryFixer::class,
        Fixer\Phpdoc\PhpdocToCommentFixer::class,
        Fixer\Phpdoc\PhpdocTrimFixer::class,
        Fixer\Phpdoc\PhpdocTypesFixer::class,
        Fixer\Phpdoc\NoEmptyPhpdocFixer::class,
        Fixer\Phpdoc\PhpdocSeparationFixer::class,
        Fixer\Phpdoc\PhpdocVarWithoutNameFixer::class,
        Fixer\Phpdoc\PhpdocNoAliasTagFixer::class,
        Fixer\FunctionNotation\ReturnTypeDeclarationFixer::class,
        Fixer\FunctionNotation\MethodArgumentSpaceFixer::class,
        Fixer\StringNotation\SingleQuoteFixer::class,
        Fixer\Import\NoUnusedImportsFixer::class,
        Fixer\Import\NoLeadingImportSlashFixer::class,
        Fixer\PhpUnit\PhpUnitFqcnAnnotationFixer::class,
        Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer::class,
        Fixer\ClassNotation\SelfAccessorFixer::class,
        Fixer\ClassNotation\SingleClassElementPerStatementFixer::class,
        Fixer\ClassNotation\NoUnneededFinalMethodFixer::class
    ]);