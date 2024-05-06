<?php

namespace App\Console\Commands;

use App\Models\Automation;
use Illuminate\Console\Command;

class ExecuteHourlyAutomations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'octansgen:execute-hourly-automations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes all automations that are scheduled to run hourly.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $output = Automation::where('schedule', 'hourly')->get()->each(function ($automation) {
            dd($automation->execute());
        });
    }
}
