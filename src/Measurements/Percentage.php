<?php

namespace Scrutiny\Measurements;

use Scrutiny\Measurement;

class Percentage implements Measurement
{
    /**
     * @var float
     */
    protected $percentage;

    /**
     * @var
     */
    protected $threshold;

    /**
     * @var string
     */
    protected $label;

    public function __construct($percentage, $threshold, $label='Percentage')
    {
        $this->percentage = (float)$percentage;
        $this->threshold = (float)$threshold;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function label()
    {
        return $this->label ?: 'Percentage';
    }

    public function __toString()
    {
        return $this->number().'%';
    }

    /**
     * @return string
     */
    public function number()
    {
        $format = $this->wholeNumber() ? '%d' : '%.1f';

        return sprintf($format, $this->percentage);
    }

    /**
     * @return string
     */
    public function round()
    {
        return round($this->number()).'%';
    }

    /**
     * @return int|float
     */
    public function threshold()
    {
        return $this->threshold;
    }

    /**
     * @return bool
     */
    public function underThreshold()
    {
        return $this->percentage <= $this->threshold;
    }

    /**
     * @return bool
     */
    public function aboveThreshold()
    {
        return $this->percentage >= $this->threshold;
    }

    /**
     * @return bool
     */
    protected function wholeNumber()
    {
        return $this->percentage == round($this->percentage);
    }
}
