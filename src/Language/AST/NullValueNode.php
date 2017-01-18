<?hh
namespace GraphQL\Language\AST;

class NullValueNode extends Node implements ValueNode
{
    public string $kind = NodeKind::NULL;
}
