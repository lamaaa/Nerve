<?php

namespace App\Http\Controllers;

use App\Stock;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::all();
        $redisPrefix = Config::get('database.redis.default.prefix');
        $stockDataArray = Redis::pipeline(function ($pipe) use ($stocks, $redisPrefix) {
            foreach ($stocks as $stock) {
                $pipe->hgetall($redisPrefix . $stock->exchange . $stock->code);
            }
        });
        return response()->json(['data' => $stockDataArray], 200);
    }

    public function test()
    {
    }
}
