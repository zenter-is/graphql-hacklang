<?hh //partial
namespace GraphQL;

use GraphQL\Executor\Promise\Adapter\SyncPromise;

class Deferred
{
    private static ?\SplQueue $queue;

    private (function():mixed) $callback;

    public SyncPromise $promise;

    private bool $async = false;
    private ?(function():Awaitable<mixed>) $asyncCallback = null;

    public static function getQueue()
    {
        return self::$queue ?? self::$queue = new \SplQueue();
    }

    public static function runQueue()
    {
        $q = self::$queue;
        if ($q === null || $q->isEmpty())
        {
            return;
        }

        $deferreds = [];
        while (!$q->isEmpty())
        {
            $deferreds[] = $q->dequeue();
        }

        $asyncDeferreds = [];
        $syncDeferreds = [];
        foreach ($deferreds as $dfd)
        {
            if ($dfd->async)
            {
                $asyncDeferreds[] = $dfd;
            }
            else
            {
                $syncDeferreds[] = $dfd;
            }
        }

        foreach ($syncDeferreds as $dfd)
        {
            $dfd->run();
        }

        if (\count($asyncDeferreds) > 0)
        {
            self::runAsyncDeferreds($asyncDeferreds);
        }
    }

    private static function runAsyncDeferreds(array $deferreds):void
    {
        $awaitables = [];
        foreach ($deferreds as $dfd)
        {
            $awaitables[] = $dfd->runAsync();
        }

        $results = \HH\Asio\join(\HH\Asio\v($awaitables));

        foreach ($deferreds as $i => $dfd)
        {
            try
            {
                $dfd->promise->resolve($results[$i]);
            }
            catch (\Exception $e)
            {
                $dfd->promise->reject($e);
            }
        }
    }

    public function __construct((function():mixed) $callback)
    {
        $this->callback = $callback;
        $this->promise = new SyncPromise();
        self::getQueue()->enqueue($this);
    }

    public static function createAsync((function():Awaitable<mixed>) $callback):Deferred
    {
        $noop = () ==> null;
        $dfd = new self($noop);
        $dfd->async = true;
        $dfd->asyncCallback = $callback;
        return $dfd;
    }

    public function then($onFulfilled = null, $onRejected = null)
    {
        return $this->promise->then($onFulfilled, $onRejected);
    }

    private function run():void
    {
        try
        {
            $cb = $this->callback;
            $this->promise->resolve($cb());
        }
        catch (\Exception $e)
        {
            $this->promise->reject($e);
        }
    }

    private async function runAsync():Awaitable<mixed>
    {
        $cb = $this->asyncCallback;
        if ($cb === null)
        {
            throw new \Exception('Deferred::runAsync called without async callback');
        }
        return await $cb();
    }
}
