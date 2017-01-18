<?hh
namespace GraphQL\Language\AST;

class FloatValueNode extends Node implements ValueNode
{
    public string $kind = NodeKind::FLOAT;

    /**
     * @var string
     */
    public $value;
}
