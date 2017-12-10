<?php

namespace Scrutiny\Probes\QueueIsRunning;

use Illuminate\Contracts\Bus\SelfHandling;

class SelfHandlingQueueIsRunningJob extends QueueIsRunningJob implements SelfHandling
{

}
