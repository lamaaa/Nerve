<?php

namespace App\Console\Commands;

use App\Stock;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class CrawlStockQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:stockQuotes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl Stock Quotes And Store It In The Redis';

    private $totalCount;
    private $counter = 1;
    private $concurrency = 5;
    private $stockCodeStrArray;

    // 一次获取的股票个数
    const STEP = 500;
    const SINA_STOCK_URI = 'http://hq.sinajs.cn/list=';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->initStockCodeStrArray(500);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Client();
        
        $requests = function ($total) use ($client) {
            foreach ($this->stockCodeStrArray as $stockCodeStr) {
                $uri = self::SINA_STOCK_URI . $stockCodeStr;
                yield function () use ($client, $uri) {
                    return $client->getAsync($uri);
                };
            }
        };

        $pool = new Pool($client, $requests($this->totalCount), [
            'concurrency' => $this->concurrency,
            'fulfilled' => function ($response, $index) {

                $stockQuotesStr = $response->getBody()->getContents();
                $stockQuotesStr = str_replace("\n", "", $stockQuotesStr);
                $stockQuotesStrArray = explode(";", $stockQuotesStr);
                $this->store($stockQuotesStrArray);
                $this->info("请求第" .  $index . "个请求");
                $this->countedAndCheckEnded();
            },
            'rejected' => function ($reason, $index) {
                $this->error("请求第" . $index . "个请求 " . "rejected. rejected reason: " . $reason . "\n");
                $this->countedAndCheckEnded();
            }
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    private function store($stockQuotesStrArray)
    {
        foreach ($stockQuotesStrArray as $stockQuotesStr) {
            if ($stockQuotesStr == "") {
                continue;
            }
            $stockQuotesData = $this->parseStockQuotesStr($stockQuotesStr);
            Redis::hmset('nerve:stock:' . $stockQuotesData[0], $stockQuotesData[1]);
        }
    }

    /**
     * @param $stockQuotesStr
     * @return array
     * ****stockTempDataArray****
     * ----0---- 股票名字
     * ----1---- 今日开盘价
     * ----2---- 昨日收盘价
     * ----3---- 当前价格
     * ----4---- 今日最高价
     * ----5---- 今日最低价
     * ----6---- 竞买价，即“买一”报价
     * ----7---- 竞卖价，即“卖一”报价
     * ----8---- 成交的股票数
     * ----9---- 成交金额，单位为“元”
     * ----10--- 日期
     * ----11--- 时间
     */
    private function parseStockQuotesStr($stockQuotesStr)
    {
        $stockQuotesDataArray = [];
        $stockQuotesArray = explode("=", $stockQuotesStr);
        $stockCode = str_replace("var hq_str_", "", $stockQuotesArray[0]);
        $stockTempData = substr($stockQuotesArray[1], 1, strlen($stockQuotesArray[1]) - 1);

        $stockTempDataArray = [];
        if ($stockTempData != "") {
            $stockTempDataArray = explode(",", $stockTempData);
        }

        $stockQuotesKeys = [
            'name', 'todayOpening', 'yesterdayClosing', 'currentPrice', 'todayHighestPrice',
            'todayLowestPrice', 'bidPrice', 'askedPrice', 'totalVolume', 'totalAccount', 'date', 'time'
        ];

        if (count($stockTempDataArray) >= 32) {
            foreach ($stockQuotesKeys as $index => $stockQuotesKey) {
                $stockQuotesDataArray[$stockQuotesKey] = $stockTempDataArray[$index];
                if ($stockQuotesKey == 'date') {
                    $stockQuotesDataArray[$stockQuotesKey] = $stockTempDataArray[30];
                }
                if ($stockQuotesKey == 'time') {
                    $stockQuotesDataArray[$stockQuotesKey] = $stockTempDataArray[31];
                }
            }
        } else {
            foreach ($stockQuotesKeys as $stockQuotesKey) {
                $stockQuotesDataArray[$stockQuotesKey] = '-';
            }
        }

        return [$stockCode, $stockQuotesDataArray];
    }

    private function initStockCodeStrArray($step)
    {
        $stocks = Stock::all();
        $stockCodeStr = '';
        foreach ($stocks as $index => $stock) {
            $stockCodeStr .= $stock->exchange . $stock->code . ",";
            $index++;
            if ($index % $step == 0) {
                $this->stockCodeStrArray[] = substr($stockCodeStr, 0, strlen($stockCodeStr) - 1);
                $stockCodeStr = "";
            }
        }
        $totalStockCount = count($stocks);
        $this->totalCount = ceil((float)$totalStockCount / self::STEP);
        if (count($stocks) % $step != 0) {
            $this->stockCodeStrArray[] = substr($stockCodeStr, 0, strlen($stockCodeStr) - 1);
        }
    }

    private function countedAndCheckEnded()
    {
        if ($this->counter < $this->totalCount) {
            $this->counter++;
            return;
        }
        $this->info("请求结束");
    }
}
