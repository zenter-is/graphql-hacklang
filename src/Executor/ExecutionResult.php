<?hh
namespace GraphQL\Executor;

use GraphQL\Error\Error;

class ExecutionResult
{
    /**
     * @var array
     */
    public $data;

    /**
     * @var Error[]
     */
    public array<Error> $errors;

    /**
     * @var array[]
     */
    public $extensions;

    /**
     * @param array $data
     * @param array $errors
     * @param array $extensions
     */
    public function __construct(?array $data = null, array $errors = [], array $extensions = [])
    {
        $this->data = $data;
        $this->errors = $errors;
        $this->extensions = $extensions;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];

        if (null !== $this->data) {
            $result['data'] = $this->data;
        }

        if (!empty($this->errors)) {
            //['GraphQL\Error\Error', 'formatError']
            $result['errors'] = array_map((Error $error) ==> Error::formatError($error), $this->errors);
        }

        if (!empty($this->extensions)) {
            $result['extensions'] = (array) $this->extensions;
        }

        return $result;
    }
}
