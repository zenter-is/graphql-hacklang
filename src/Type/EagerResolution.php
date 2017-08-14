<?hh
namespace GraphQL\Type;

use GraphQL\Type\Definition\AbstractType;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\GraphQlType;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Definition\WrappingType;
use GraphQL\Utils;

class EagerResolution implements Resolution
{
    /**
     * @var Type[]
     */
    private $typeMap = [];

    /**
     * @var array<string, ObjectType[]>
     */
    private $implementations = [];

    /**
     * EagerResolution constructor.
     * @param Type[] $initialTypes
     */
    public function __construct(array $initialTypes)
    {
        foreach ($initialTypes as $type) {
            $this->extractTypes($type);
        }
        $this->typeMap += GraphQlType::getInternalTypes();

        // Keep track of all possible types for abstract types
        foreach ($this->typeMap as $typeName => $type)
        {
            if ($type instanceof ObjectType)
            {
                foreach ($type->getInterfaces() as $iface)
                {
                    $this->implementations[$iface->name][] = $type;
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function resolveType($name)
    {
        return isset($this->typeMap[$name]) ? $this->typeMap[$name] : null;
    }

    /**
     * @inheritdoc
     */
    public function resolvePossibleTypes(AbstractType $abstractType)
    {
        $name = Utils::getObjectValue($abstractType, $name);
        if(!$name)
        {
            return [];
        }

        if (!array_key_exists($name, $this->typeMap))
        {
            return [];
        }

        if ($abstractType instanceof UnionType)
        {
            return $abstractType->getTypes();
        }

        /** @var InterfaceType $abstractType */
        Utils::invariant($abstractType instanceof InterfaceType);
        return ($this->implementations[$name]) ? $this->implementations[$name] : [];
    }

    /**
     * @return Type[]
     */
    public function getTypeMap()
    {
        return $this->typeMap;
    }

    /**
     * Returns serializable schema representation suitable for GraphQL\Type\LazyResolution
     *
     * @return array
     */
    public function getDescriptor()
    {
        $typeMap = [];
        $possibleTypesMap = [];
        foreach ($this->getTypeMap() as $type) {
            if ($type instanceof UnionType) {
                foreach ($type->getTypes() as $innerType) {
                    $possibleTypesMap[$type->name][$innerType->name] = 1;
                }
            } else if ($type instanceof InterfaceType) {
                foreach ($this->implementations[$type->name] as $obj) {
                    $possibleTypesMap[$type->name][$obj->name] = 1;
                }
            }
            $typeMap[$type->name] = 1;
        }
        return [
            'version' => '1.0',
            'typeMap' => $typeMap,
            'possibleTypeMap' => $possibleTypesMap
        ];
    }

    /**
     * @param $type
     * @return array
     */
    private function extractTypes($type)
    {
        if (!$type)
        {
            return $this->typeMap;
        }

        if ($type instanceof WrappingType)
        {
            return $this->extractTypes($type->getWrappedType(true));
        }

        if (!empty($this->typeMap[$type->name]))
        {
            Utils::invariant(
                $this->typeMap[$type->name] === $type,
                "Schema must contain unique named types but contains multiple types named \"$type\"."
            );
            return $this->typeMap;
        }
        $this->typeMap[$type->name] = $type;

        $nestedTypes = [];

        if ($type instanceof UnionType)
        {
            $nestedTypes = $type->getTypes();
        }
        if ($type instanceof ObjectType)
        {
            $nestedTypes = array_merge($nestedTypes, $type->getInterfaces());
        }
        if ($type instanceof ObjectType || $type instanceof InterfaceType || $type instanceof InputObjectType)
        {
            foreach ((array) $type->getFields() as $fieldName => $field)
            {
                if (isset($field->args))
                {
                    $fieldArgTypes = array_map(function(FieldArgument $arg) { return $arg->getType(); }, $field->args);
                    $nestedTypes = array_merge($nestedTypes, $fieldArgTypes);
                }
                $nestedTypes[] = $field->getType();
            }
        }
        foreach ($nestedTypes as $type)
        {
            $this->extractTypes($type);
        }
        return $this->typeMap;
    }
}
