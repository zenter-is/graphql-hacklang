<?hh
namespace GraphQL\Language\AST;

class VariableDefinitionNode extends Node implements DefinitionNode
{
    public string $kind = NodeKind::VARIABLE_DEFINITION;

    /**
     * @var VariableNode
     */
    public $variable;

    /**
     * @var TypeNode
     */
    public $type;

    /**
     * @var ValueNode|null
     */
    public $defaultValue;
}
