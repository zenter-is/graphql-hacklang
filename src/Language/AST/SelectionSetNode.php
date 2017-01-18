<?hh
namespace GraphQL\Language\AST;

class SelectionSetNode extends Node
{
    public string $kind = NodeKind::SELECTION_SET;

    /**
     * @var SelectionNode[]
     */
    public $selections;
}
