<?php

namespace App\Models;

/**
 * BoostStats — Theo dõi thống kê đợt buff theo thời gian thực
 */
class BoostStats {
    public int   $totalSent      = 0;
    public int   $successful     = 0;
    public int   $failed         = 0;
    public float $startTime;
    public float $peakSpeed      = 0.0;
    private array $speedSamples  = [];

    public function __construct() {
        $this->startTime = microtime(true);
    }

    public function recordSuccess(): void {
        $this->totalSent++;
        $this->successful++;
        $this->updateSpeed();
    }

    public function recordFailure(): void {
        $this->totalSent++;
        $this->failed++;
    }

    private function updateSpeed(): void {
        $elapsed = $this->elapsed();
        if ($elapsed > 0) {
            $current = $this->successful / $elapsed;
            $this->speedSamples[] = $current;
            if (count($this->speedSamples) > 10) {
                array_shift($this->speedSamples);
            }
            if ($current > $this->peakSpeed) {
                $this->peakSpeed = $current;
            }
        }
    }

    public function elapsed(): float {
        return round(microtime(true) - $this->startTime, 2);
    }

    public function currentSpeed(): float {
        if (empty($this->speedSamples)) return 0.0;
        return round(array_sum($this->speedSamples) / count($this->speedSamples), 1);
    }

    public function successRate(): float {
        if ($this->totalSent === 0) return 0.0;
        return round(($this->successful / $this->totalSent) * 100, 1);
    }

    /**
     * Xuất ra array JSON cho API real-time
     */
    public function toArray(): array {
        $vps = $this->currentSpeed();
        return [
            'total'        => $this->totalSent,
            'success'      => $this->successful,
            'failed'       => $this->failed,
            'elapsed'      => $this->elapsed(),
            'vps'          => $vps,
            'vpm'          => round($vps * 60),
            'peak_vps'     => round($this->peakSpeed, 1),
            'success_rate' => $this->successRate(),
        ];
    }
}
