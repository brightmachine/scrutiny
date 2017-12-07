<?php

namespace Scrutiny;

use Illuminate\Support\Collection;

class CheckProbeHistory extends Collection
{
    /**
     * Return history grouped by probe id, and presented in chronological order.
     *
     * Useful for charting measurements.
     *
     * @return Collection
     */
    public function groupByProbe()
    {
        return $this
            ->reverse()// so we get oldest first
            ->flatten(1)// so all results are in a big ol' collection
            ->groupBy('id') // so each probe has its own collection
            ;
    }

    public function withMeasurements()
    {
        return $this->reject(function ($v) {
            return !array_get($v, 'measurement');
        });
    }

    public function onlyCurrentProbes(Collection $probes)
    {
        $probeIds = $probes->map(function(Probe $probe){
            return hash('sha256', $probe->id());
        });

        return $this->only($probeIds->all());
    }

    /**
     * We need to translate the checks for a single probe into something that
     * resembles a timeline of shifting states.
     *
     * @return static
     */
    public function transformForTimelineChart()
    {
        $stateChanges = collect();

        $pendingStatus = null;

        foreach ($this as $result) {
            if (!$pendingStatus) {
                $pendingStatus = $result;
                $pendingStatus['timeTo'] = time();
                continue;
            }

            // state change
            if ($result['status'] != $pendingStatus['status']) {
                $pendingStatus['timeTo'] = $result['time'];
                $stateChanges->push($pendingStatus);
                $pendingStatus = $result;
                $pendingStatus['timeTo'] = time();
            } else {
                $pendingStatus['timeTo'] = $result['time'];
            }
        }

        $stateChanges->push($pendingStatus);

        return $stateChanges;
    }

    public function mixedOrMissingMeasurements()
    {
        $measurements = $this->uniqueMeasurements();

        if ($measurements->count() != 1) {
            return true;
        }

        if ($measurements->first() == 'NoMeasurement') {
            return true;
        }

        return false;
    }

    public function percentageMeasurements()
    {
        $measurements = $this->uniqueMeasurements();

        if ($measurements->count() == 0) {
            return false;
        }

        if ($measurements->count() == 1 && $measurements->first() == 'Percentage') {
            return true;
        }

        return false;
    }

    public function durationMeasurements()
    {
        $measurements = $this->uniqueMeasurements();

        if ($measurements->count() == 0) {
            return false;
        }

        if ($measurements->count() == 1 && $measurements->first() == 'Duration') {
            return true;
        }

        return false;
    }

    protected function uniqueMeasurements()
    {
        return $this
            ->pluck('measurement')
            ->map(function ($measurement) {
                return class_basename($measurement);
            })
            ->filter()
            ->unique();
    }
}
