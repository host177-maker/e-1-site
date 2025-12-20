<?php

namespace Absteam\SpeedMeter;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Monolog\Logger;

/**
 * Класс измеритель интервала времени, между выполнением методов start и stop.
 * Инкапсулирует всю логику работы с Monolog для логгирования производительности кода.
 */
final class SpeedMeter
{
    protected Logger $logger;
    private array $timers = array();

    public function __construct(SpeedMeterBuilder $builder)
    {
        $this->logger = $builder->logger;
    }

    public function startMeter(string $metricName)
    {
        if (!isset($this->timers[$metricName])) {
            $this->timers[$metricName] = array(
                'totalTime' => null,
                'count'     => 0
            );
        }
        $this->timers[$metricName]['start'] = floatval(microtime(true));
    }

    public function stopMeter(string $metricName)
    {
        if (isset($this->timers[$metricName]['start'])) {
            $timer_stop                             = floatval(microtime(true));
            $time                                   = $timer_stop - $this->timers[$metricName]['start'];
            $totalTime                              = $this->timers[$metricName]['totalTime'] + $time;
            $this->timers[$metricName]['totalTime'] = $totalTime;
            $count                                  = $this->timers[$metricName]['count'] + 1;
            $this->timers[$metricName]['count']     = $count;
        } else {
            $time                                   = $totalTime = null;
            $this->timers[$metricName]['totalTime'] = null;
            $count                                  = 0;
        }

        $timerInfo = array(
            'timer_time' => $time,
            'totalTime'  => $totalTime,
            'count'      => $count
        );

        $this->logger->debug($metricName, [
            'timer_start' => $this->timers[$metricName]['start'],
            'timer_stop'  => $timer_stop,
            'timer_time'  => $timerInfo['timer_time'],
            'timer_total' => $timerInfo['totalTime'],
            'timer_count' => $timerInfo['count'],
        ]);

        unset($this->timers[$metricName]['start']);
    }
}
