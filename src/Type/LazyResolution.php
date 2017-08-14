<?hh
namespace GraphQL\Type;

use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\AbstractType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\GraphQlType;
use GraphQL\Utils;

class LazyResolution implements Resolution
{
    /**
     * @var array
     */
    private $typeMap;

    /**
     * @var array
     */
    private $possibleTypeMap;

    /**
     * @var callable
     */
    private $typeLoader;

    /**
     * List of currently loaded types
     *
     * @var Type[]
     */
    private $loadedTypes;

    /**
     * Map of $interfaceTypeName => $objectType[]
     *
     * @var array
     */
    private $loadedPossibleTypes;

    /**
     * LazyResolution constructor.
     * @param array $descriptor
     * @param callable $typeLoader
     */
    public function __construct(array $descriptor, callable $typeLoader)
    {
        Utils::invariant(
            isset($descriptor['typeMap'], $descriptor['possibleTypeMap'], $descriptor['version'])
        );
        Utils::invariant(
            $descriptor['version'] === '1.0' 
        );

        $this->typeLoader = $typeLoader;
        $this->typeMap = $descriptor['typeMap'] + GraphQlType::getInternalTypes();
        $this->possibleTypeMap = $descriptor['possibleTypeMap'];
        $this->loadedTypes = GraphQlType::getInternalTypes();
        $this->loadedPossibleTypes = [];
    }

    /**
     * @inheritdoc
     */
    public function resolveType($name)
    {
        if (!isset($this->typeMap[$name])) {
            return null;
        }
        if (!isset($this->loadedTypes[$name])) {
            $type = call_user_func($this->typeLoader, $name);
            if (!$type instanceof GraphQlType && null !== $type) {
                throw new InvariantViolation(
                    "Lazy Type Resolution Error: Expecting GraphQL Type instance, but got " .
                    Utils::getVariableType($type)
                );
            }

            $this->loadedTypes[$name] = $type;
        }
        return $this->loadedTypes[$name];
    }

    /**
     * @inheritdoc
     */
    public function resolvePossibleTypes(AbstractType $type)
    {
        $name = Utils::getObjectValue($type, 'name');
        if (!isset($this->possibleTypeMap[$name])) {
            return [];
        }
        if (!isset($this->loadedPossibleTypes[$name])) {
            $tmp = [];
            foreach ($this->possibleTypeMap[$name] as $typeName => $true) {
                $obj = $this->resolveType($typeName);
                if (!$obj instanceof ObjectType) {
                    throw new InvariantViolation(
                        "Lazy Type Resolution Error: Implementation {$typeName} of interface {$name} " .
                        "is expected to be instance of ObjectType, but got " . Utils::getVariableType($obj)
                    );
                }
                $tmp[] = $obj;
            }
            $this->loadedPossibleTypes[$name] = $tmp;
        }
        return $this->loadedPossibleTypes[$name];
    }
}
