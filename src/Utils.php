<?hh //partial
namespace GraphQL;

use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\GraphQlType as Type;
use GraphQL\Type\Definition\WrappingType;
use GraphQL\Utils\SchemaUtils;
use \Traversable, \InvalidArgumentException;
use stdClass;

class Utils
{
    public static function undefined()
    {
        static $undefined;
        return $undefined ?: $undefined = new stdClass();
    }

    /**
     * @param object $obj
     * @param array  $vars
     * @param array  $requiredKeys
     *
     * @return array
     */
    public static function assign($obj, array $vars, array $requiredKeys = [])
    {
        foreach ($requiredKeys as $key) {
            if (!isset($key, $vars)) {
                throw new InvalidArgumentException("Key {$key} is expected to be set and not to be null");
            }
        }

        foreach ($vars as $key => $value) {
            if (!property_exists($obj, $key)) {
                $cls = get_class($obj);
                trigger_error("Trying to set non-existing property '$key' on class '$cls'");
            }
            $objReflected = new \ReflectionClass($obj);
            $objProperty = $objReflected->getProperty($key);
            $objProperty->setValue($obj, $value);
            //$obj->{$key} = $value;
        }
        return $obj;
    }

    /**
     * @param array|Traversable $traversable
     * @param callable $predicate
     * @return null
     */
    public static function find($traversable, (function(any, string):bool) $predicate)
    {
        self::invariant(is_array($traversable) || $traversable instanceof \Traversable, __METHOD__ . ' expects array or Traversable');

        foreach ($traversable as $key => $value) {
            if ($predicate($value, $key)) {
                return $value;
            }
        }
        return null;
    }

    /**
     * @param $traversable
     * @param callable $predicate
     * @return array
     * @throws \Exception
     */
    public static function filter($traversable, (function(any, string):bool) $predicate)
    {
        self::invariant(is_array($traversable) || $traversable instanceof \Traversable, __METHOD__ . ' expects array or Traversable');

        $result = [];
        $assoc = false;
        foreach ($traversable as $key => $value) {
            if (!$assoc && !is_int($key)) {
                $assoc = true;
            }
            if ($predicate($value, $key)) {
                $result[$key] = $value;
            }
        }

        return $assoc ? $result : array_values($result);
    }

    /**
     * @param array|\Traversable $traversable
     * @param callable $fn function(mixed $value, string $key) => $newValue
     * @return array
     * @throws \Exception
     */
    public static function map($traversable, (function(any, string):mixed) $fn)
    {
        self::invariant(is_array($traversable) || $traversable instanceof \Traversable, __METHOD__ . ' expects array or Traversable');

        $map = [];
        foreach ($traversable as $key => $value) {
            $map[$key] = $fn($value, $key);
        }
        return $map;
    }

    /**
     * @param $traversable
     * @param callable $fn
     * @return array
     * @throws \Exception
     */
    public static function mapKeyValue($traversable, (function(any, string):array) $fn)
    {
        self::invariant(is_array($traversable) || $traversable instanceof \Traversable, __METHOD__ . ' expects array or Traversable');

        $map = [];
        foreach ($traversable as $key => $value) {
            list($newKey, $newValue) = $fn($value, $key);
            $map[$newKey] = $newValue;
        }
        return $map;
    }

    /**
     * @param $traversable
     * @param callable $keyFn function(mixed $value, string $key) => $newKey
     * @return array
     * @throws \Exception
     */
    public static function keyMap($traversable, (function(any, string):mixed) $keyFn)
    {
        self::invariant(is_array($traversable) || $traversable instanceof \Traversable, __METHOD__ . ' expects array or Traversable');

        $map = [];
        foreach ($traversable as $key => $value) {
            $newKey = $keyFn($value, $key);
            if (is_scalar($newKey)) {
                $map[$newKey] = $value;
            }
        }
        return $map;
    }

    /**
     * @param $traversable
     * @param callable $fn
     */
    public static function each($traversable, (function(any, string):void) $fn)
    {
        self::invariant(is_array($traversable) || $traversable instanceof \Traversable, __METHOD__ . ' expects array or Traversable');

        foreach ($traversable as $key => $item) {
            $fn($item, $key);
        }
    }

    /**
     * Splits original traversable to several arrays with keys equal to $keyFn return
     *
     * E.g. Utils::groupBy([1, 2, 3, 4, 5], function($value) {return $value % 3}) will output:
     * [
     *    1 => [1, 4],
     *    2 => [2, 5],
     *    0 => [3],
     * ]
     *
     * $keyFn is also allowed to return array of keys. Then value will be added to all arrays with given keys
     *
     * @param $traversable
     * @param callable $keyFn function(mixed $value, string $key) => $newKey(s)
     * @return array
     */
    public static function groupBy($traversable, (function(any, string):array) $keyFn)
    {
        self::invariant(is_array($traversable) || $traversable instanceof \Traversable, __METHOD__ . ' expects array or Traversable');

        $grouped = [];
        foreach ($traversable as $key => $value) {
            $newKeys = (array) $keyFn($value, $key);
            foreach ($newKeys as $key) {
                $grouped[$key][] = $value;
            }
        }

        return $grouped;
    }

    /**
     * @param $traversable
     * @param callable $predicate
     * @return bool
     */
    public static function every($traversable, (function(any, string):bool) $predicate)
    {
        foreach ($traversable as $key => $value) {
            if (!$predicate($value, $key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $test
     * @param string $message
     * @param mixed $sprintfParam1
     * @param mixed $sprintfParam2 ...
     * @throws \Exception
     */
    public static function invariant($test, string ...$messages)
    {
        if (!$test) {
            $message = call_user_func_array('sprintf', $messages);
            throw new InvariantViolation($message);
        }
    }

    /**
     * @param $var
     * @return string
     */
    public static function getVariableType($var)
    {
        if ($var instanceof Type) {
            // FIXME: Replace with schema printer call
            if ($var instanceof WrappingType) {
                $var = $var->getWrappedType(true);
            }
            return $var->name;
        }
        return is_object($var) ? get_class($var) : gettype($var);
    }

    /**
     * @param $var
     * @return string
     */
    public static function printSafe($var)
    {
        if ($var instanceof Type) {
            return $var->toString();
        }
        if (is_object($var)) {
            return 'instance of ' . get_class($var);
        }
        if ('' === $var) {
            return '(empty string)';
        }
        if (is_scalar($var)) {
            return (string) $var;
        }
        if (null === $var) {
            return 'null';
        }
        return gettype($var);
    }

    /**
     * UTF-8 compatible chr()
     *
     * @param string $ord
     * @param string $encoding
     * @return string
     */
    public static function chr($ord, @string $encoding = 'UTF-8')
    {
        if ($ord <= 255) {
            return chr($ord);
        }
        if ($encoding === 'UCS-4BE') {
            return pack("N", $ord);
        } else {
            return mb_convert_encoding(self::chr($ord, 'UCS-4BE'), $encoding, 'UCS-4BE');
        }
    }

    /**
     * UTF-8 compatible ord()
     *
     * @param string $char
     * @param string $encoding
     * @return mixed
     */
    public static function ord($char, @string $encoding = 'UTF-8')
    {
        if (!isset($char[1])) {
            return ord($char);
        }
        if ($encoding === 'UCS-4BE') {
            list($temp, $ord) = (strlen($char) === 4) ? unpack('N', $char) : unpack('n', $char);
            return $ord;
        } else {
            return self::ord(mb_convert_encoding($char, 'UCS-4BE', $encoding), 'UCS-4BE');
        }
    }

    /**
     * Returns UTF-8 char code at given $positing of the $string
     *
     * @param $string
     * @param $position
     * @return mixed
     */
    public static function charCodeAt($string, $position)
    {
        $char = mb_substr($string, $position, 1, 'UTF-8');
        return self::ord($char);
    }

    /**
     * @param $code
     * @return string
     */
    public static function printCharCode($code)
    {
        if (null === $code) {
            return '<EOF>';
        }
        return $code < 0x007F
            // Trust JSON for ASCII.
             ? json_encode(Utils::chr($code))
            // Otherwise print the escaped form.
            : '"\\u' . dechex($code) . '"';
    }

    public static function getObjectValue($object, $fieldName):any
    {
        $class = new \ReflectionClass($object);
        if($class->hasProperty($fieldName))
        {
            $property = $class->getProperty($fieldName);
            return $property->getValue($object);
        }
        throw new \Exception("Trying to access non-existing property ($fieldName) of object (" . get_class($object) . ")");
    }

    public static function setObjectValue($object, $fieldName, $value):void
    {
        $class = new \ReflectionClass($object);
        if($class->hasProperty($fieldName))
        {
            $property = $class->getProperty($fieldName);
            $property->setValue($object, $value);
        }
    }
}
