<?hh
namespace GraphQL\Language\AST;

class EnumValueNode extends Node implements ValueNode
{
    public string $kind = NodeKind::ENUM;

    /**
     * @var string
     */
    public $value;
}
