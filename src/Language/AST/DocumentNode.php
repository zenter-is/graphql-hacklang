<?hh
namespace GraphQL\Language\AST;

class DocumentNode extends Node
{
    public string $kind = NodeKind::DOCUMENT;

    /**
     * @var DefinitionNode[]
     */
    public $definitions;
}
