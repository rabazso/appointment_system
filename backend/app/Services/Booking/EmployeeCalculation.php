<?php
namespace App\Booking;


class EmployeeCalculation
{
    public function calculateValidFrom($employee, int $serviceId, $now)
    {
        $emps = $employee->versions
            ->filter(fn($v) => $v->valid_to === null || $v->valid_to >= $now)
            ->sortBy('valid_from')
            ->values();

        $cfgs = $employee->serviceConfigurations
            ->where('service_id', $serviceId)
            ->filter(fn($c) => $c->valid_to === null || $c->valid_to >= $now)
            ->sortBy('valid_from')
            ->values();

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
    
    public function calculateValidTo($employee, int $serviceId, $start)
    {
        $current = $start->copy();

        while (true) {

            $emp = $employee->versions->validAt($current);

            $cfg = $employee->serviceConfigurations
                ->where('service_id', $serviceId)
                ->validAt($current);

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