<?hh
namespace GraphQL\Validator\Rules;

use GraphQL\Error\Error;
use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\GraphQlType;
use GraphQL\Validator\ValidationContext;

class FragmentsOnCompositeTypes
{
    public static function inlineFragmentOnNonCompositeErrorMessage($type)
   : @string {
        return "Fragment cannot condition on non composite type \"$type\".";
    }

    public static function fragmentOnNonCompositeErrorMessage($fragName, $type)
   : @string {
        return "Fragment \"$fragName\" cannot condition on non composite type \"$type\".";
    }

    public function __invoke(ValidationContext $context)
   : @array {
        return [
            NodeKind::INLINE_FRAGMENT => function(InlineFragmentNode $node) use ($context) {
                $type = $context->getType();

                if ($node->typeCondition && $type && !GraphQlType::isCompositeType($type)) {
                    $context->reportError(new Error(
                        static::inlineFragmentOnNonCompositeErrorMessage($type),
                        [$node->typeCondition]
                    ));
                }
            },
            NodeKind::FRAGMENT_DEFINITION => function(FragmentDefinitionNode $node) use ($context) {
                $type = $context->getType();

                if ($type && !GraphQlType::isCompositeType($type)) {
                    $context->reportError(new Error(
                        static::fragmentOnNonCompositeErrorMessage($node->name->value, Printer::doPrint($node->typeCondition)),
                        [$node->typeCondition]
                    ));
                }
            }
        ];
    }
}
