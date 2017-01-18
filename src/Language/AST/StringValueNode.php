<?hh
namespace GraphQL\Language\AST;

class StringValueNode extends Node implements ValueNode
{
    public string $kind = NodeKind::STRING;

    /**
     * @var string
     */
    public $value;
}
