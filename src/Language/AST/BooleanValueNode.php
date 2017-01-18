<?hh
namespace GraphQL\Language\AST;


class BooleanValueNode extends Node implements ValueNode
{
    public string $kind = NodeKind::BOOLEAN;

    /**
     * @var string
     */
    public $value;
}
