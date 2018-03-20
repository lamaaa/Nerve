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
        $this->middleware('auth');
        $this->redisPrefix = Config::get('database.redis.default.prefix');
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
