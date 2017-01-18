<?hh
namespace GraphQL\Language\AST;

class SchemaDefinitionNode extends Node implements TypeSystemDefinitionNode
{
    /**
     * @var string
     */
    public string $kind = NodeKind::SCHEMA_DEFINITION;

    /**
     * @var DirectiveNode[]
     */
    public $directives;

    /**
     * @var OperationTypeDefinitionNode[]
     */
    public $operationTypes;
}
