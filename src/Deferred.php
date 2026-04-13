<?hh //partial
namespace GraphQL;

use GraphQL\Executor\Promise\Adapter\SyncPromise;

class Deferred
{
    private static ?\SplQueue $queue;

    private (function():mixed) $callback;

    public SyncPromise $promise;

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

        $hasAsync = false;
        foreach ($deferreds as $dfd)
        {
            if ($dfd->async)
            {
                $hasAsync = true;
                break;
            }
        }

        if ($hasAsync)
        {
            self::runQueueAsync($deferreds);
        }
        else
        {
            foreach ($deferreds as $dfd)
            {
                $dfd->run();
            }
        }
    }

    private static function runQueueAsync(array<Deferred> $deferreds):void
    {
        $awaitables = [];
        $syncDeferreds = [];

        foreach ($deferreds as $dfd)
        {
            if ($dfd->async)
            {
                $awaitables[] = \Pair {$dfd, $dfd->runAsync()};
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

        if (\count($awaitables) > 0)
        {
            $asyncAwaitables = [];
            foreach ($awaitables as $pair)
            {
                $asyncAwaitables[] = $pair[1];
            }
            $results = \HH\Asio\join(\HH\Asio\v($asyncAwaitables));
            foreach ($awaitables as $i => $pair)
            {
                $dfd = $pair[0];
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
    }

    private bool $async = false;
    private ?(function():Awaitable<mixed>) $asyncCallback = null;

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
