<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrawlStockData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:stockData {source}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl Stock Data And Store To Redis';

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
        echo $this->argument('source');
    }
}
