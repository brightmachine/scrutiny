<?php

namespace Scrutiny;

class MeasurementThresholdException extends \Exception
{
    /**
     * @var Measurement
     */
    protected $measurement;

    public function __construct($message = "", Measurement $measurement)
    {
        $this->measurement = $measurement;
        parent::__construct($message);
    }

    /**
     * @return Measurement
     */
    public function measurement()
    {
        return $this->measurement;
    }
}
