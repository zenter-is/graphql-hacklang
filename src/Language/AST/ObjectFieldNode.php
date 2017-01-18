<?hh
namespace GraphQL\Language\AST;


class ObjectFieldNode extends Node
{
    public string $kind = NodeKind::OBJECT_FIELD;

    /**
     * @var NameNode
     */
    public $name;

    /**
     * @var ValueNode
     */
    public $value;
}
