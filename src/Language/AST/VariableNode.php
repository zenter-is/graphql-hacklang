<?hh
namespace GraphQL\Language\AST;

class VariableNode extends Node
{
    public string $kind = NodeKind::VARIABLE;

    /**
     * @var NameNode
     */
    public $name;
}
