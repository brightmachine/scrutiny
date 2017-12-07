<?php

namespace Scrutiny\Probes;

use Scrutiny\Probe;
use Scrutiny\ProbeSkippedException;

class ConnectsToHttp implements Probe
{
    /**
     * @var string URL to hit
     */
    protected $url;
    /**
     * @var array any additional parameters to pass
     */
    protected $params;
    /**
     * @var string
     */
    protected $verb;

    /** @var  string|null */
    protected $nameIdentifier;

    public function __construct($url, $params = array(), $verb = 'GET')
    {
        $this->url = $url;
        $this->params = $params;
        $this->verb = $verb;
    }

    public function id()
    {
        if ($this->nameIdentifier) {
            return $this->name();
        }

        return sprintf(
            "probe:%s,url:%s,params:%s,verb:%s",
            class_basename($this),
            $this->reportUrl(),
            $this->params ? http_build_query($this->params) : '',
            $this->verb
        );
    }

    public function name($identifier = null)
    {
        if ($identifier) {
            $this->nameIdentifier = $identifier;
        }

        $defaultIdentifier = $this->verb.' '.$this->reportUrl();

        return sprintf(
            "Connects to Http: %s",
            $this->nameIdentifier ?: $defaultIdentifier
        );
    }

    public function check()
    {
        $httpStatusCode = $this->performHttpCall();

        if (!is_numeric($httpStatusCode)) {
            throw new ProbeSkippedException("{$this->reportUrl()} response not numeric: $httpStatusCode");
        }

        if ($httpStatusCode < 100 || $httpStatusCode >= 600) {
            throw new ProbeSkippedException("{$this->reportUrl()} response not in normal range: $httpStatusCode");
        }

        if ($httpStatusCode < 200 || $httpStatusCode >= 300) {
            throw new \Exception("{$this->reportUrl()} did not return a 2xx response: $httpStatusCode");
        }
    }

    protected function performHttpCall()
    {
        $url = $this->url;
        $reportUrl = $this->reportUrl();
        $params = $this->params;
        $verb = $this->verb;

        $contextParams = array(
            'http' => array(
                'method'          => $verb,
                'ignore_errors'   => true,
                'follow_location' => 0,
            ),
        );

        if (!empty($params)) {
            $params = http_build_query($params);

            if ($verb == 'POST') {
                $contextParams['http']['content'] = $params;
            } else {
                $url .= '?'.$params;
            }
        }

        try {
            $context = stream_context_create($contextParams);
            $fp = fopen($url, 'rb', false, $context);
        } catch (\Exception $e) {
            throw new \Exception("Attempt to open connection to $reportUrl failed");
        }

        if (!$fp) {
            throw new \Exception("$verb request on $reportUrl failed");
        }

        $metaData = stream_get_meta_data($fp);

        $httpResponse = collect(array_get($metaData, 'wrapper_data'))
            ->first(function ($v, $k) {
                if (str_contains("$k $v", 'HTTP/')) {
                    return true;
                }
            });

        if ($httpResponse === null) {
            throw new \Exception("$verb request on $reportUrl failed to return an HTTP response");
        }

        $httpParts = explode(' ', $httpResponse, 3);

        return (int)$httpParts[1];
    }

    protected function reportUrl()
    {
        $parts = parse_url($this->url);

        $url = $parts['scheme'].'://';

        if (isset($parts['user']) || isset($parts['pass'])) {
            $url .= '...@';
        }

        $url .= $parts['host'];

        if (isset($parts['port'])) {
            $url .= ':'.$parts['port'];
        }

        if (isset($parts['path'])) {
            $url .= $parts['path'];
        }

        return $url;
    }
}
