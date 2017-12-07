<?php

namespace Scrutiny\Measurements;

use Scrutiny\Measurement;

class Duration implements Measurement
{
    /**
     * @var int
     */
    protected $seconds;

    /**
     * @var int
     */
    protected $threshold;

    /**
     * @var string
     */
    protected $label;

    public function __construct($seconds, $threshold, $label='Duration')
    {
        $this->seconds = $seconds;
        $this->threshold = $threshold;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function label()
    {
        return $this->label ?: 'Duration';
    }

    public function __toString()
    {
        return sprintf('%s seconds', $this->seconds);
    }

    /**
     * @return int
     */
    public function seconds()
    {
        return $this->seconds;
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
        return $this->seconds <= $this->threshold;
    }

    /**
     * @return bool
     */
    public function aboveThreshold()
    {
        return $this->seconds >= $this->threshold;
    }
}
