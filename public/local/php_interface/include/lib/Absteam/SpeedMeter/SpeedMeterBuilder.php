<?php

namespace Absteam\SpeedMeter;

use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\WebProcessor;
use Absteam\SpeedMeter\MonologHandler;
use PDO;

/**
 * Класс "строитель" для класса SpeedMeter
 */
class SpeedMeterBuilder
{
    public const additionalParams = [
        'timer_start',
        'timer_stop',
        'timer_time',
        'timer_total',
        'timer_count',
        'url',
        'memory_usage'
    ];
    protected MonologHandler $mySQLHandler;
    public Logger $logger;

    public function __construct(PDO $pdo, string $chanelName, array $params = [])
    {
        $params       = array_merge(self::additionalParams, $params);
        $this->logger = new Logger($chanelName);

        $this->mySQLHandler = new MonologHandler($pdo, "log", $params, Logger::DEBUG, true, true);
        $this->logger->pushHandler($this->mySQLHandler);
    }

    public function addWebProcessor()
    {
        $this->logger->pushProcessor(new WebProcessor());
        return $this;
    }
    public function addMemoryUsageProcessor()
    {
        $this->logger->pushProcessor(new MemoryUsageProcessor());
        return $this;
    }
    public function build(): SpeedMeter
    {
        return new SpeedMeter($this);
    }
}
