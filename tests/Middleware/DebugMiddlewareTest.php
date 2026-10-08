<?php

declare(strict_types=1);

namespace Doctrine\Bundle\DoctrineBundle\Tests\Middleware;

use Doctrine\Bundle\DoctrineBundle\Middleware\BacktraceDebugDataHolder;
use Doctrine\Bundle\DoctrineBundle\Middleware\DebugMiddleware;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\DefaultSchemaManagerFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Middleware\Debug\Middleware as BridgeDebugMiddleware;
use Symfony\Component\Stopwatch\Stopwatch;

use function method_exists;
use function strpos;

class DebugMiddlewareTest extends TestCase
{
    public function testProfilerStateControlsQueriesBacktracesAndStopwatch(): void
    {
        if (! method_exists(BridgeDebugMiddleware::class, 'setProfilerStateChecker')) {
            self::markTestSkipped('Requires the DoctrineBridge profiler state checker API.');
        }

        $disabled      = true;
        $holder        = new BacktraceDebugDataHolder(['default']);
        $stopwatch     = new Stopwatch();
        $configuration = new Configuration();
        $configuration->setMiddlewares([
            new DebugMiddleware(
                $holder,
                $stopwatch,
                null,
                static function () use (&$disabled): bool {
                    return $disabled;
                },
            ),
        ]);
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $configuration);
        $statement  = $connection->prepare('SELECT ?');
        $statement->bindValue(1, 42);
        self::assertSame('42', $statement->executeQuery()->fetchOne());
        self::assertSame([], $holder->getData());
        self::assertSame([], $stopwatch->getSectionEvents('__root__'));

        $disabled = false;
        $statement->executeQuery()->free();
        self::assertCount(1, $holder->getData()['default']);
        self::assertNotEmpty($holder->getData()['default'][0]['backtrace']);
        self::assertCount(1, $stopwatch->getEvent('doctrine')->getPeriods());

        $disabled = true;
        $statement->executeQuery()->free();
        self::assertCount(1, $holder->getData()['default']);
        self::assertCount(1, $stopwatch->getEvent('doctrine')->getPeriods());

        $holder->reset();
        $stopwatch->reset();
        $disabled = false;
        $statement->executeQuery()->free();
        self::assertCount(1, $holder->getData()['default']);
        self::assertCount(1, $stopwatch->getEvent('doctrine')->getPeriods());
    }

    public function testData(): void
    {
        $configuration = new Configuration();
        $configuration->setSchemaManagerFactory(new DefaultSchemaManagerFactory());
        $debugDataHolder = new BacktraceDebugDataHolder(['default']);
        $configuration->setMiddlewares([new DebugMiddleware($debugDataHolder, null)]);

        $conn = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $configuration);

        $conn->executeQuery(<<<'EOT'
CREATE TABLE products (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    price REAL NOT NULL,
    stock INTEGER NOT NULL
);
EOT);

        $data = $debugDataHolder->getData();
        $this->assertCount(1, $data['default'] ?? []);

        $current = $data['default'][0];

        $this->assertSame(0, strpos($current['sql'] ?? '', 'CREATE TABLE products'));
        $this->assertSame([], $current['params'] ?? null);
        $this->assertSame([], $current['types'] ?? null);
        $this->assertGreaterThan(0, $current['executionMS'] ?? 0);
        $this->assertSame(Connection::class, $current['backtrace'][0]['class'] ?? '');
        $this->assertSame('executeQuery', $current['backtrace'][0]['function'] ?? '');

        $debugDataHolder->reset();
        $data = $debugDataHolder->getData();
        $this->assertCount(0, $data['default'] ?? []);
    }
}
