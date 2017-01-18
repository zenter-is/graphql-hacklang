<?hh
namespace GraphQL\Language\AST;

class DirectiveNode extends Node
{
    public string $kind = NodeKind::DIRECTIVE;

    /**
     * @var NameNode
     */
    public $name;

    /**
     * @var ArgumentNode[]
     */
    public $arguments;
}
