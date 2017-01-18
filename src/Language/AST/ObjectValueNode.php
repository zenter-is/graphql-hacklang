<?hh
namespace GraphQL\Language\AST;

class ObjectValueNode extends Node implements ValueNode
{
    public string $kind = NodeKind::OBJECT;

    /**
     * @var ObjectFieldNode[]
     */
    public $fields;
}
