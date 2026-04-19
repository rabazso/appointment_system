<?php

namespace App\Services\Booking;


class EmployeeAvailabilityService
{
    public function calculateValidFrom($employee, $now)
    {
        $emps = $employee->versions;

        $cfgs = $employee->serviceConfigurations;

        foreach ($emps as $emp) {
            foreach ($cfgs as $cfg) {

                $start = $this->maxDate($emp->valid_from, $cfg->valid_from);
                $end   = $this->minDate($emp->valid_to, $cfg->valid_to);

                if ($end === null || $start <= $end) {
                    return $start;
                }
            }
        }

        return null;
    }
    
    public function calculateValidTo($employee, $start)
    {
        $current = $start->copy();

        while (true) {

            $emp = $employee->versions
                ->first(fn ($version) =>
                    $version->valid_from <= $current &&
                    ($version->valid_to === null || $version->valid_to > $current)
                );

            $cfg = $employee->serviceConfigurations
                ->first(fn ($configuration) =>
                    $configuration->valid_from <= $current &&
                    ($configuration->valid_to === null || $configuration->valid_to > $current)
                );

            if (!$emp || !$cfg) {
                return $current;
            }

            $next = $this->minDate($emp->valid_to, $cfg->valid_to);

            if ($next === null) {
                return null;
            }

            $current = $next;
        }
    }

    private function maxDate($a, $b)
    {
        if (!$a) return $b;
        if (!$b) return $a;
        return $a > $b ? $a : $b;
    }

    private function minDate($a, $b)
    {
        if (!$a) return $b;
        if (!$b) return $a;
        return $a < $b ? $a : $b;
    }
}
