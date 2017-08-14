<?hh
namespace GraphQL\Type\Definition;

use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\FloatValueNode;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Utils;

/**
 * Class FloatType
 * @package GraphQL\Type\Definition
 */
class FloatType extends ScalarType
{
    /**
     * @var string
     */
    public string $name = GraphQlType::FLOAT;

    /**
     * @var string
     */
    public string $description =
'The `Float` scalar type represents signed double-precision fractional
values as specified by
[IEEE 754](http://en.wikipedia.org/wiki/IEEE_floating_point). ';

    /**
     * @param mixed $value
     * @return float|null
     */
    public function serialize($value)
    {
        return $this->coerceFloat($value);
    }

    /**
     * @param mixed $value
     * @return float|null
     */
    public function parseValue($value)
    {
        return $this->coerceFloat($value);
    }

    /**
     * @param $value
     * @return float|null
     */
    private function coerceFloat($value)
    {
        if ($value === '') {
            throw new InvariantViolation(
                'Float cannot represent non numeric value: (empty string)'
            );
        }
        if (is_numeric($value) || $value === true || $value === false) {
            return (float)$value;
        }
        throw new InvariantViolation('Float cannot represent non numeric value: ' . Utils::printSafe($value));
    }

    /**
     * @param $ast
     * @return float|null
     */
    public function parseLiteral($ast)
    {
        if ($ast instanceof FloatValueNode || $ast instanceof IntValueNode) {
            return (float) $ast->value;
        }
        return null;
    }
}
