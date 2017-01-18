<?hh
namespace GraphQL\Language\AST;

class DirectiveDefinitionNode extends Node implements TypeSystemDefinitionNode
{
    /**
     * @var string
     */
    public string $kind = NodeKind::DIRECTIVE_DEFINITION;

    /**
     * @var NameNode
     */
    public $name;

    /**
     * @var ArgumentNode[]
     */
    public $arguments;

    /**
     * @var NameNode[]
     */
    public $locations;
}
