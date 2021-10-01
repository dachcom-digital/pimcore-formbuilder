<?php

use PhpCsFixer\Fixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {

    // B. full sets
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SETS, [SetList::CLEAN_CODE, SetList::PSR_12]);

    $services = $containerConfigurator->services();

    $services->set(Fixer\Basic\BracesFixer::class)
        ->call('configure', [
            [
                'allow_single_line_closure' => true,
            ]
        ]);

    $services->set(Fixer\PhpTag\BlankLineAfterOpeningTagFixer::class);

    $services->set(Fixer\Operator\ConcatSpaceFixer::class)
        ->call('configure', [
            [
                'spacing' => 'one',
            ]
        ]);

    $services->set(Fixer\Operator\NewWithBracesFixer::class);

    $services->set(Fixer\Phpdoc\PhpdocAlignFixer::class)
        ->call('configure', [
            [
                'tags' => ['method', 'param', 'property', 'return', 'throws', 'type', 'var'],
            ]
        ]);

    $services->set(Fixer\Operator\BinaryOperatorSpacesFixer::class)
        ->call('configure', [
            [
                'operators' => [
                    '='  => 'single_space',
                    '=>' => 'align',
                ]
            ]
        ]);
    $services->set(Fixer\Operator\IncrementStyleFixer::class)
        ->call('configure', [
            [
                'style' => 'post',
            ]
        ]);

    $services->set(Fixer\Operator\UnaryOperatorSpacesFixer::class);
    $services->set(Fixer\Whitespace\BlankLineBeforeStatementFixer::class);
    $services->set(Fixer\CastNotation\CastSpacesFixer::class);
    $services->set(Fixer\LanguageConstruct\DeclareEqualNormalizeFixer::class);
    $services->set(Fixer\FunctionNotation\FunctionTypehintSpaceFixer::class);
    $services->set(Fixer\Comment\SingleLineCommentStyleFixer::class)
        ->call('configure', [
            [
                'comment_types' => ['hash'],
            ]
        ]);

    $services->set(Fixer\ControlStructure\IncludeFixer::class);
    $services->set(Fixer\CastNotation\LowercaseCastFixer::class);
    $services->set(Fixer\ClassNotation\ClassAttributesSeparationFixer::class)
        ->call('configure', [
            [
                'elements' => [
                    'const'        => 'none',
                    'method'       => 'one',
                    'property'     => 'none',
                    'trait_import' => 'none'
                ],
            ]
        ]);

    $services->set(Fixer\Casing\NativeFunctionCasingFixer::class);
    $services->set(Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer::class);
    $services->set(Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer::class);
    $services->set(Fixer\Comment\NoEmptyCommentFixer::class);
    $services->set(Fixer\Phpdoc\NoEmptyPhpdocFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocSeparationFixer::class);
    $services->set(Fixer\Semicolon\NoEmptyStatementFixer::class);
    $services->set(Fixer\Whitespace\ArrayIndentationFixer::class);
    $services->set(Fixer\Whitespace\NoExtraBlankLinesFixer::class)
        ->call('configure', [
            [
                'tokens' => ['curly_brace_block', 'extra', 'parenthesis_brace_block', 'square_brace_block', 'throw', 'use'],
            ]
        ]);

    $services->set(Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer::class);
    $services->set(Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer::class);
    $services->set(Fixer\CastNotation\NoShortBoolCastFixer::class);
    $services->set(Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer::class);
    $services->set(Fixer\Whitespace\NoSpacesAroundOffsetFixer::class);
    $services->set(Fixer\ControlStructure\NoTrailingCommaInListCallFixer::class);
    $services->set(Fixer\ControlStructure\NoUnneededControlParenthesesFixer::class);
    $services->set(Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer::class);
    $services->set(Fixer\Whitespace\NoWhitespaceInBlankLineFixer::class);
    $services->set(Fixer\ArrayNotation\NormalizeIndexBraceFixer::class);
    $services->set(Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocIndentFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocInlineTagFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocNoAccessFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocNoEmptyReturnFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocNoPackageFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocScalarFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocSummaryFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocToCommentFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocTrimFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocTypesFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocVarWithoutNameFixer::class);
    $services->set(Fixer\FunctionNotation\ReturnTypeDeclarationFixer::class);
    $services->set(Fixer\ClassNotation\SelfAccessorFixer::class);
    $services->set(Fixer\CastNotation\ShortScalarCastFixer::class);
    $services->set(Fixer\StringNotation\SingleQuoteFixer::class);
    $services->set(Fixer\Semicolon\SpaceAfterSemicolonFixer::class);
    $services->set(Fixer\Operator\StandardizeNotEqualsFixer::class);
    $services->set(Fixer\Operator\TernaryOperatorSpacesFixer::class);
    $services->set(Fixer\ArrayNotation\TrimArraySpacesFixer::class);
    $services->set(Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer::class);

    $services->set(Fixer\ClassNotation\ClassDefinitionFixer::class)
        ->call('configure', [
            [
                'single_line' => true,
            ]
        ]);

    $services->set(Fixer\Casing\MagicConstantCasingFixer::class);
    $services->set(Fixer\FunctionNotation\MethodArgumentSpaceFixer::class);
    $services->set(Fixer\Alias\NoMixedEchoPrintFixer::class)
        ->call('configure', [
            [
                'use' => 'echo',
            ]
        ]);

    $services->set(Fixer\Import\NoLeadingImportSlashFixer::class);
    $services->set(Fixer\PhpUnit\PhpUnitFqcnAnnotationFixer::class);
    $services->set(Fixer\Phpdoc\PhpdocNoAliasTagFixer::class);
    $services->set(Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer::class);
    $services->set(Fixer\ClassNotation\SingleClassElementPerStatementFixer::class);

    # new since PHP-CS-Fixer 2.6
    $services->set(Fixer\ClassNotation\NoUnneededFinalMethodFixer::class);
    $services->set(Fixer\Semicolon\SemicolonAfterInstructionFixer::class);

    # new since 2.11
    $services->set(Fixer\Operator\StandardizeIncrementFixer::class);
};