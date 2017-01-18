<?hh
namespace GraphQL\Language\AST;

class OperationDefinitionNode extends Node implements DefinitionNode, HasSelectionSet
{
    /**
     * @var string
     */
    public string $kind = NodeKind::OPERATION_DEFINITION;

    /**
     * @var NameNode
     */
    public $name;

    /**
     * @var string (oneOf 'query', 'mutation'))
     */
    public $operation;

    /**
     * @var VariableDefinitionNode[]
     */
    public $variableDefinitions;

    /**
     * @var DirectiveNode[]
     */
    public $directives;

    /**
     * @var SelectionSetNode
     */
    public $selectionSet;
}
