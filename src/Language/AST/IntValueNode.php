<?hh
namespace GraphQL\Language\AST;

class IntValueNode extends Node implements ValueNode
{
    public string $kind = NodeKind::INT;

    /**
     * @var string
     */
    public $value;
}
