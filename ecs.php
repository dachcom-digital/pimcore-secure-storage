<?php

use PhpCsFixer\Fixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {

    // B. full sets
    $ecsConfig->sets([SetList::CLEAN_CODE, SetList::PSR_12]);

    $ecsConfig->ruleWithConfiguration(Fixer\Basic\BracesFixer::class, [
        'allow_single_line_closure' => true,
    ]);

    $ecsConfig->rule(Fixer\PhpTag\BlankLineAfterOpeningTagFixer::class);

    $ecsConfig->ruleWithConfiguration(Fixer\Operator\ConcatSpaceFixer::class, [
            'spacing' => 'one',
        ]
    );

    $ecsConfig->rule(Fixer\Operator\NewWithBracesFixer::class);

    $ecsConfig->ruleWithConfiguration(Fixer\Phpdoc\PhpdocAlignFixer::class, [
            'tags' => ['method', 'param', 'property', 'return', 'throws', 'type', 'var'],
        ]
    );

    $ecsConfig->ruleWithConfiguration(Fixer\Operator\BinaryOperatorSpacesFixer::class, [
            'operators' => [
                '='  => 'single_space',
                '=>' => 'align',
            ]
        ]
    );

    $ecsConfig->ruleWithConfiguration(Fixer\Operator\IncrementStyleFixer::class, [
            'style' => 'post',
        ]
    );

    $ecsConfig->rule(Fixer\Operator\UnaryOperatorSpacesFixer::class);
    $ecsConfig->rule(Fixer\Whitespace\BlankLineBeforeStatementFixer::class);
    $ecsConfig->rule(Fixer\CastNotation\CastSpacesFixer::class);
    $ecsConfig->rule(Fixer\LanguageConstruct\DeclareEqualNormalizeFixer::class);
    $ecsConfig->rule(Fixer\FunctionNotation\FunctionTypehintSpaceFixer::class);
    $ecsConfig->ruleWithConfiguration(Fixer\Comment\SingleLineCommentStyleFixer::class, [
            'comment_types' => ['hash'],
        ]
    );

    $ecsConfig->rule(Fixer\ControlStructure\IncludeFixer::class);
    $ecsConfig->rule(Fixer\CastNotation\LowercaseCastFixer::class);
    $ecsConfig->ruleWithConfiguration(Fixer\ClassNotation\ClassAttributesSeparationFixer::class, [
            'elements' => [
                'const'        => 'none',
                'method'       => 'one',
                'property'     => 'none',
                'trait_import' => 'none'
            ],
        ]
    );

    $ecsConfig->rule(Fixer\Casing\NativeFunctionCasingFixer::class);
    $ecsConfig->rule(Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer::class);
    $ecsConfig->rule(Fixer\Comment\NoEmptyCommentFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\NoEmptyPhpdocFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocSeparationFixer::class);
    $ecsConfig->rule(Fixer\Semicolon\NoEmptyStatementFixer::class);
    $ecsConfig->rule(Fixer\Whitespace\ArrayIndentationFixer::class);
    $ecsConfig->ruleWithConfiguration(Fixer\Whitespace\NoExtraBlankLinesFixer::class, [
            'tokens' => ['curly_brace_block', 'extra', 'parenthesis_brace_block', 'square_brace_block', 'throw', 'use'],
        ]
    );

    $ecsConfig->rule(Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer::class);
    $ecsConfig->rule(Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer::class);
    $ecsConfig->rule(Fixer\CastNotation\NoShortBoolCastFixer::class);
    $ecsConfig->rule(Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer::class);
    $ecsConfig->rule(Fixer\Whitespace\NoSpacesAroundOffsetFixer::class);
    $ecsConfig->rule(Fixer\ControlStructure\NoTrailingCommaInListCallFixer::class);
    $ecsConfig->rule(Fixer\ControlStructure\NoUnneededControlParenthesesFixer::class);
    $ecsConfig->rule(Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer::class);
    $ecsConfig->rule(Fixer\Whitespace\NoWhitespaceInBlankLineFixer::class);
    $ecsConfig->rule(Fixer\ArrayNotation\NormalizeIndexBraceFixer::class);
    $ecsConfig->rule(Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocIndentFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocInlineTagNormalizerFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocNoAccessFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocNoEmptyReturnFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocNoPackageFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocScalarFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocSummaryFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocToCommentFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocTrimFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocTypesFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocVarWithoutNameFixer::class);
    $ecsConfig->rule(Fixer\FunctionNotation\ReturnTypeDeclarationFixer::class);
    $ecsConfig->rule(Fixer\ClassNotation\SelfAccessorFixer::class);
    $ecsConfig->rule(Fixer\CastNotation\ShortScalarCastFixer::class);
    $ecsConfig->rule(Fixer\StringNotation\SingleQuoteFixer::class);
    $ecsConfig->rule(Fixer\Semicolon\SpaceAfterSemicolonFixer::class);
    $ecsConfig->rule(Fixer\Operator\StandardizeNotEqualsFixer::class);
    $ecsConfig->rule(Fixer\Operator\TernaryOperatorSpacesFixer::class);
    $ecsConfig->rule(Fixer\ArrayNotation\TrimArraySpacesFixer::class);
    $ecsConfig->rule(Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer::class);
    $ecsConfig->rule(Fixer\Import\NoUnusedImportsFixer::class);

    $ecsConfig->ruleWithConfiguration(Fixer\ClassNotation\ClassDefinitionFixer::class, [
            'single_line' => true,
        ]
    );

    $ecsConfig->rule(Fixer\Casing\MagicConstantCasingFixer::class);
    $ecsConfig->rule(Fixer\FunctionNotation\MethodArgumentSpaceFixer::class);
    $ecsConfig->ruleWithConfiguration(Fixer\Alias\NoMixedEchoPrintFixer::class, [
            'use' => 'echo',
        ]
    );

    $ecsConfig->rule(Fixer\Import\NoLeadingImportSlashFixer::class);
    $ecsConfig->rule(Fixer\PhpUnit\PhpUnitFqcnAnnotationFixer::class);
    $ecsConfig->rule(Fixer\Phpdoc\PhpdocNoAliasTagFixer::class);
    $ecsConfig->rule(Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer::class);
    $ecsConfig->rule(Fixer\ClassNotation\SingleClassElementPerStatementFixer::class);

    # new since PHP-CS-Fixer 2.6
    $ecsConfig->rule(Fixer\ClassNotation\NoUnneededFinalMethodFixer::class);
    $ecsConfig->rule(Fixer\Semicolon\SemicolonAfterInstructionFixer::class);

    # new since 2.11
    $ecsConfig->rule(Fixer\Operator\StandardizeIncrementFixer::class);
};
