<?hh
namespace GraphQL\Language\AST;

class InlineFragmentNode extends Node implements SelectionNode
{
    public string $kind = NodeKind::INLINE_FRAGMENT;

    /**
     * @var NamedTypeNode
     */
    public $typeCondition;

    /**
     * @var DirectiveNode[]|null
     */
    public $directives;

    /**
     * @var SelectionSetNode
     */
    public $selectionSet;
}
