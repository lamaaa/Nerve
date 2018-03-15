<?php

namespace App\Console\Commands;

use App\Stock;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class CrawlStockCodeAndStockName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:stockCodeAndStockName';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl Stock Code and It\'s Name';

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
        $client = new Client();
        $crawler = new Crawler();
        $stocks = [];

        $url = 'http://quote.eastmoney.com/stocklist.html';
        $response = $client->request('GET', $url);

        if ($response->getStatusCode() == 200) {

            $crawler->addHtmlContent($response->getBody()->getContents(), 'gb2312');
            // 上交所
            $shContents = $crawler->filterXPath('//*[@id="quotesearch"]/ul[1]');
            // 深交所
            $szContents = $crawler->filterXPath('//*[@id="quotesearch"]/ul[2]');

            if ($shContents->count() != 1 || $szContents->count() != 1) {
                echo 'error';
            }
            $shValues = $shContents->children()->each(function (Crawler $node, $index) {
                return $node->text();
            });
            $szValues = $szContents->children()->each(function (Crawler $node, $index) {
                return $node->text();
            });

            $this->prepareInsertArray($stocks, $shValues, 'sh');
            $this->prepareInsertArray($stocks, $szValues, 'sz');

            Stock::insert($stocks);
            echo 'Finished';
        } else {
            echo $response->getStatusCode();
        }
    }

    private function prepareInsertArray(&$stocks, $values, $exchange)
    {
        $now = Carbon::now('utc')->toDateTimeString();
        foreach ($values as $value) {
            $codeAndName = explode('(', $value);
            $stocks[] = [
                'name' => $codeAndName[0],
                'code' => trim($codeAndName[1], ')'),
                'exchange' => $exchange,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }
}
