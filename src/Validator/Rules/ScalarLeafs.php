<?hh
namespace GraphQL\Validator\Rules;

use GraphQL\Error\Error;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Type\Definition\GraphQlType;
use GraphQL\Validator\ValidationContext;

class ScalarLeafs
{
    public static function noSubselectionAllowedMessage($field, $type)
   : @string {
        return "Field \"$field\" of type \"$type\" must not have a sub selection.";
    }

    public static function requiredSubselectionMessage($field, $type)
   : @string {
        return "Field \"$field\" of type \"$type\" must have a sub selection.";
    }

    public function __invoke(ValidationContext $context)
   : @array {
        return [
            NodeKind::FIELD => function(FieldNode $node) use ($context) {
                $type = $context->getType();
                if ($type) {
                    if (GraphQlType::isLeafType($type)) {
                        if ($node->selectionSet) {
                            $context->reportError(new Error(
                                self::noSubselectionAllowedMessage($node->name->value, $type),
                                [$node->selectionSet]
                            ));
                        }
                    } else if (!$node->selectionSet) {
                        $context->reportError(new Error(
                            self::requiredSubselectionMessage($node->name->value, $type),
                            [$node]
                        ));
                    }
                }
            }
        ];
    }
}
