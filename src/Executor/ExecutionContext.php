<?hh
namespace GraphQL\Executor;

use GraphQL\Error\Error;
use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Schema;

/**
 * Data that must be available at all points during query execution.
 *
 * Namely, schema of the type system that is currently executing,
 * and the fragments defined in the query document
 */
class ExecutionContext
{
    /**
     * @var Schema
     */
    public $schema;

    /**
     * @var FragmentDefinitionNode[]
     */
    public $fragments;

    /**
     * @var mixed
     */
    public mixed $rootValue;

    /**
     * @var mixed
     */
    public mixed $contextValue;

    /**
     * @var OperationDefinitionNode
     */
    public $operation;

    /**
     * @var array
     */
    public $variableValues;

    /**
     * @var array
     */
    public array<Error> $errors;

    public function __construct($schema, $fragments, $root, $contextValue, $operation, $variables, $errors)
    {
        $this->schema = $schema;
        $this->fragments = $fragments;
        $this->rootValue = $root;
        $this->contextValue = $contextValue;
        $this->operation = $operation;
        $this->variableValues = $variables;
        $this->errors = $errors ?: [];
    }

    public function addError(Error $error)
    {
        $this->errors[] = $error;
        return $this;
    }
}
