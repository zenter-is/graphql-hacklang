<?hh //decl
namespace GraphQL\Type\Definition;

/*
NonNullType
ListOfType
 */
interface WrappingType
{
    /**
     * @param bool $recurse
     * @return GraphQlType
     */
    public function getWrappedType(@bool $recurse = false);
}
