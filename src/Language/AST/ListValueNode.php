<?hh

namespace GraphQL\Language\AST;

class ListValueNode extends Node implements ValueNode
{
    public string $kind = NodeKind::LST;

    /**
     * @var ValueNode[]
     */
    public $values;
}
