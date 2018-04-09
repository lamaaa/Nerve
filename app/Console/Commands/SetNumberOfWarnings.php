<?php

namespace App\Console\Commands;

use App\WarningConfig;
use Illuminate\Console\Command;

class SetNumberOfWarnings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:numberOfWarnings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set Number Of Warnings';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        WarningConfig::where(['status' => 1])->update(['number_of_warnings' => 0]);
        $this->info('update success');
    }
}
