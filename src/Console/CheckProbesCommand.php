<?php

namespace Scrutiny\Console;

use Illuminate\Console\Command;
use Scrutiny\CheckProbes;
use Scrutiny\CheckProbesResult;

class CheckProbesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrutiny:check-probes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the Scrutiny probes';

    public function handle(CheckProbes $checkProbes)
    {
        /** @var CheckProbesResult $result */
        $result = $checkProbes->handle(true)->first();

        $this->line('');
        $this->line($result->pluck('icon')->implode(' '));
        $this->info("*** {$result->passed()->count()} passed ***");

        $failed = $result->failed();
        if ($failed->count() > 0) {
            $this->line('');
            $this->error("*** {$failed->count()} failed ***");

            $this->table(
                ['Probe', 'Message'],
                $failed->map(function ($value) {
                    return [
                        $value['name'],
                        $value['message']
                    ];
                })
            );
        }

        $skipped = $result->skipped();
        if ($skipped->count() > 0) {
            $this->line('');
            $this->warn("*** {$skipped->count()} skipped ***");

            $this->table(
                ['Probe', 'Message'],
                $skipped->map(function ($value) {
                    return [
                        $value['name'],
                        $value['message']
                    ];
                })
            );
        }

        $this->line('');

        return $failed->count() ? 1 : 0;
    }
}
