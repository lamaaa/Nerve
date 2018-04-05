<?php

namespace App\Http\Controllers;

use App\Mail\ThresholdReached;
use App\Stock;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    public function test()
    {
//        $user = User::find(3);
//        Mail::to($user)
//                ->send(new ThresholdReached());
//        $test = Redis::keys('nerve:stock:*');
//        foreach ($test as $item) {
//            Redis::del($item);
//        }
//        \Artisan::call('crawl:stockQuotes');
//        \Artisan::call('prepare:userWarningConfigsQueue');
        \Artisan::call('check:usersStockQuote');
//        $client = new Client();
//        $url = 'http://hq.sinajs.cn/list=';
//        $stocks = Stock::all();
//        $count = 0;
//        foreach ($stocks as $stock) {
//            if ($count == 500) {
//                break;
//            }
//            $url .= $stock->exchange . $stock->code . ",";
//            $count++;
//        }
//        $url = substr($url, 0, strlen($url) - 1);
//        $response = $client->request('GET', $url);
//        if ($response->getStatusCode() == 200) {
//            echo $response->getBody();
//        } else {
//            echo 'failed';
//        }
    }
}
