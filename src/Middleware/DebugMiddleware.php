<?php

declare(strict_types=1);

namespace Doctrine\Bundle\DoctrineBundle\Middleware;

use Closure;
use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\Persistence\ConnectionRegistry;
use Symfony\Bridge\Doctrine\Middleware\Debug\DebugDataHolder;
use Symfony\Bridge\Doctrine\Middleware\Debug\Middleware as BridgeDebugMiddleware;
use Symfony\Component\Stopwatch\Stopwatch;

use function method_exists;

class DebugMiddleware implements Middleware, ConnectionNameAwareInterface
{
    private string $connectionName = 'default';

    /** @param (Closure(): bool)|null $disabled */
    public function __construct(
        private readonly DebugDataHolder $debugDataHolder,
        private readonly Stopwatch|null $stopwatch,
        private readonly ConnectionRegistry|null $connectionRegistry = null,
        private readonly Closure|null $disabled = null,
    ) {
    }

    public function setConnectionName(string $name): void
    {
        $this->connectionName = $name;
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        $middleware = new BridgeDebugMiddleware($this->debugDataHolder, $this->stopwatch, $this->connectionName, $this->connectionRegistry);
        // Keep supporting DoctrineBridge versions without the profiler state checker API.
        // @phpstan-ignore-next-line
        if ($this->disabled !== null && method_exists($middleware, 'setProfilerStateChecker')) {
            $middleware->setProfilerStateChecker($this->disabled);
        }

        return $middleware->wrap($driver);
    }
}
