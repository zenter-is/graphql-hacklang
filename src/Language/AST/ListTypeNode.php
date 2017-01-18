<?hh
namespace GraphQL\Language\AST;

class ListTypeNode extends Node implements TypeNode
{
    public string $kind = NodeKind::LIST_TYPE;

    /**
     * @var Node
     */
    public $type;
}
