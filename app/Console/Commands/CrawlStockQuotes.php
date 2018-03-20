<?php

namespace App\Console\Commands;

use App\Stock;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use function GuzzleHttp\Psr7\str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
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
    private $allNormalStocks;
    private $allAbnormalStocks;
    private $redisPrefix;

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
        $this->redisPrefix = Config::get('database.redis.default.prefix');
        $this->initStockCodeStrArray(self::STEP);
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
                // 最后一个是空
                unset($stockQuotesStrArray[count($stockQuotesStrArray) - 1]);
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
        $normalStockCodes = [];
        $abnormalStockCodes = [];
        $tempStockQuotesDataArray = [];
        foreach ($stockQuotesStrArray as $stockQuotesStr) {
            list($stockStatus, $stockCode, $stockQuotesDataArray) = $this->parseStockQuotesStr($stockQuotesStr);
            //
            if ($stockStatus == 0) {
                $normalStockCodes[] = $this->redisPrefix . 'stock:' . $stockCode;
                $tempStockQuotesDataArray[] = [$stockCode, $stockQuotesDataArray];
                $this->allNormalStocks[] = $this->redisPrefix . 'stock:' . $stockCode;
            } else {
                $abnormalStockCodes[] = $this->redisPrefix . 'stock:' . $stockCode;
                $this->allAbnormalStocks[] = $this->redisPrefix . 'stock:' . $stockCode;
            }
//            Redis::hmset($redisPrefix . $stockQuotesData[0], $stockQuotesData[1]);
        }
        Stock::changeStatus($normalStockCodes, Stock::NORMAL);
        Stock::changeStatus($abnormalStockCodes, Stock::ABNORMAL);
        Redis::pipeline(function ($pipe) use ($tempStockQuotesDataArray) {
            foreach ($tempStockQuotesDataArray as $stockQuotesData) {
                $pipe->hmset($this->redisPrefix . 'stock:' . $stockQuotesData[0], $stockQuotesData[1]);
            }
        });
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
        $stockStatus = 0;
        $stockQuotesArray = explode("=", $stockQuotesStr);
        $stockCode = str_replace("var hq_str_", "", $stockQuotesArray[0]);
        $stockTempData = str_replace("\"", "", $stockQuotesArray[1]);

        $stockTempDataArray = [];
        if ($stockTempData != "") {
            $stockTempDataArray = explode(",", $stockTempData);
        }

        $stockQuotesKeys = [
            'today_opening', 'yesterday_closing', 'current_price', 'today_highest_price',
            'today_lowest_price', 'bid_price', 'asked_price', 'total_volume', 'total_account'
        ];

        $isNormalData = count($stockTempDataArray) >= 32 ?: false;
        $stockQuotesDataArray['code'] = $stockCode;
        $stockQuotesDataArray['name'] = $isNormalData ? iconv('GB2312', 'UTF-8//IGNORE', $stockTempDataArray[0]) : '';
        $stockQuotesDataArray['datetime'] = $isNormalData ? $stockTempDataArray[30] . " " . $stockTempDataArray[31] : '';
        if ($isNormalData) {
            foreach ($stockQuotesKeys as $index => $stockQuotesKey) {
                $stockQuotesDataArray[$stockQuotesKey] = $stockTempDataArray[$index + 1];
            }
        } else {
            $stockStatus = 1;
        }

        if (isset($stockQuotesDataArray['today_opening']) && $stockQuotesDataArray['today_opening'] !== '0.000') {
            $stockQuotesDataArray['quote_change'] = ((float)$stockQuotesDataArray['current_price'] - (float)$stockQuotesDataArray['yesterday_closing']) / (float)$stockQuotesDataArray['yesterday_closing'];
        } else {
            $stockQuotesDataArray['quote_change'] = 0.0000;
        }

        return [$stockStatus, $stockCode, $stockQuotesDataArray];
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
        Redis::sadd($this->redisPrefix . 'stocks', $this->allNormalStocks);
        Redis::srem($this->redisPrefix . 'stocks', $this->allAbnormalStocks);
        $this->info("请求结束");
    }
}
