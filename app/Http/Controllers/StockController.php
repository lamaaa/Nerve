<?php

namespace App\Http\Controllers;

use App\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class StockController extends Controller
{
    private $redisPrefix;

    public function __construct()
    {
        $this->redisPrefix = Config::get('database.redis.default.prefix');
    }

    public function test(Request $request)
    {
        $page = $request->input('page', 1);
        $stockPaginatorArray = Stock::where(['stock_status' => Stock::NORMAL])->paginate(16, ['*'], 'page', $page)->toArray();
        $stockQuotesArray = Redis::pipeline(function ($pipe) use ($stockPaginatorArray) {
            foreach ($stockPaginatorArray['data'] as $stock) {
                $pipe->hgetall($this->redisPrefix . 'stocks:' . $stock['exchange'] . $stock['code']);
            }
        });
        unset($stockPaginatorArray['data']);
        unset($stockPaginatorArray['next_page_url']);
        unset($stockPaginatorArray['prev_page_url']);
        unset($stockPaginatorArray['path']);
        $stockPaginatorArray['stocks'] = $stockQuotesArray;
        return response()->json(['data' => $stockPaginatorArray], 200);

//        $stockDataArray = Redis::pipeline(function ($pipe) use ($stocks, $redisPrefix) {
//            foreach ($stocks as $stock) {
//                $pipe->hgetall($redisPrefix . $stock->exchange . $stock->code);
//            }
//        });
//        return response()->json(['data' => $stockDataArray], 200);
    }

    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $criteria = $request->input('criteria', 'current_price');
        $order = $request->input('order', 'desc');
        $pageSize = 16;
        $redisStocksKey = $this->redisPrefix . 'stocks';

        $total = Redis::scard($redisStocksKey);
        $stockCodes = Redis::sort($redisStocksKey, [
            'by' => '*->' . $criteria,
            'limit' => [$pageSize * ($page - 1), $pageSize],
            'sort' => $order,
        ]);
        $stockQuotes = Redis::pipeline(function ($pipe) use ($stockCodes) {
            foreach ($stockCodes as $stockCode) {
                $pipe->hgetall($stockCode);
            }
        });
        return response()->json([
            'data' => [
                'total' => $total,
                'per_page' => $pageSize,
                'current_page' => $page,
                'stocks' => $stockQuotes
            ]
        ], 200);
    }
}
