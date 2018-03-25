<?php

namespace App\Http\Controllers;

use App\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class StockController extends Controller
{
    private $redisPrefix;

    public function __construct()
    {
        $this->middleware('auth');
        $this->redisPrefix = Config::get('database.redis.default.prefix');
    }

    public function index()
    {
        $stocks = Stock::select('code', 'name')->get();
        return response()->json(['data' => $stocks], 200);
    }

    public function quotes(Request $request)
    {

    }
}
