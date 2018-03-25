<?php

namespace App\Http\Controllers;

use App\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;
use Validator;
use Auth;

class UserController extends Controller
{
    public function getStockQuotes($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|in:' . Auth::user()->id
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['errors' => $errors], 422);
        }

        $stocks = Auth::user()->stocks;
        $redisPrefix = Config::get('database.redis.default.prefix') . 'stock:';

        $quotes = Redis::pipeline(function ($pipe) use ($stocks, $redisPrefix) {
            foreach ($stocks as $stock) {
                $pipe->hgetall($redisPrefix . $stock->code);
            }
        });

        foreach ($quotes as &$quote) {
            foreach ($stocks as $stock) {
                if ($quote['code'] == $stock->code) {
                    $quote['id'] = $stock->id;
                    break;
                }
            }
        }
        return response()->json(['data' => $quotes], 200);
    }

    public function addStock(Request $request, $id)
    {
        $user = Auth::user();
        $validator = Validator::make(
            array_merge($request->all(), ['id' => $id]),
            [
                'code' => 'required|exists:stocks,code',
                'id' => 'required|in:' . $user->id
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['errors' => $errors], 422);
        }

        $code = $request->input('code', '');
        $stock = Stock::where(['code' => $code])->first();
        if ($stock) {
            $isExist = $user->stocks()->where('stock_id', $stock->id)->first();
            if ($isExist) {
                return response()->json(['errors' => 'Data exists'], 409);
            }
            if ($user->stocks()->save($stock)) {
                return response()->json(null, 204);
            }
        }

        return response()->json(['errors' => 'The server has a problem'], 500);
    }

    public function deleteStock($id, $stockId)
    {
        $user = Auth::user();
        $validator = Validator::make(
            [
                'id' => $id,
                'stock_id' => $stockId
            ],
            [
                'stock_id' => 'required|exists:stocks,id',
                Rule::exists('stock_user.stock_id')->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }),
                'id' => 'required|in:' . $user->id
            ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['errors' => $errors], 422);
        }

        if ($user->stocks()->detach($stockId)) {
            return response()->json(null, 204);
        } else {
            return response()->json(['errors' => 'The server has a problem'], 500);
        }
    }

    public function getCurrentUserInfo()
    {
        $user = Auth::user();

        return response()->json(['data' => $user], 200);
    }
}
