<?php

namespace App\Http\Controllers;

use App\Stock;
use GuzzleHttp\Client;

class TestController extends Controller
{
    public function test()
    {
        \Artisan::call('crawl:stockQuotes');
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
