<?hh //decl
namespace GraphQL\Type\Definition;

use GraphQL\Utils;

/**
 * Class ListOfType
 * @package GraphQL\Type\Definition
 */
class ListOfType extends GraphQlType implements WrappingType, OutputType, InputType
{
    /**
     * @var callable|GraphQlType
     */
    public $ofType;

    /**
     * @param callable|GraphQlType $type
     */
    public function __construct($type)
    {
        Utils::invariant(
            $type instanceof GraphQlType || is_callable($type),
            'Expecting instance of GraphQL\Type\Definition\GraphQlType or callable returning instance of that class'
        );

        $this->ofType = $type;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $type = GraphQlType::resolve($this->ofType);
        $str = $type instanceof GraphQlType ? $type->toString() : (string) $type;
        return '[' . $str . ']';
    }

    /**
     * @param bool $recurse
     * @return mixed
     */
    public function getWrappedType(@bool $recurse = false)
    {
        $type = GraphQlType::resolve($this->ofType);
        return ($recurse && $type instanceof WrappingType) ? $type->getWrappedType($recurse) : $type;
    }
}
