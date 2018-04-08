<?php

namespace App\Console\Commands;

use App\Jobs\SendReminderNotification;
use App\Notifications\ThresholdReached;
use App\Stock;
use App\User;
use App\WarningConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;
use Predis\Client;

class CheckUsersStockQuote extends Command
{
    const TIMEOUT = 1;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:usersStockQuote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if user\'s warning configs settings have been met';
    private $queueCount;
    private $queueNamePrefix;
    private $stockPrefix;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $redisPrefix = Config::get('database.redis.default.prefix');
        $this->queueCount = env('REDIS_WARNING_CONFIG_USER_QUEUE_COUNT', 3);
        $this->queueNamePrefix = $redisPrefix . 'warning_config:user:';
        $this->stockPrefix = $redisPrefix . 'stock:';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $subRedisClient = Redis::connection('sub');
        $subRedisClient->subscribe(['prepare-warning-configs-channel'], function ($message) {
            $data = json_decode($message);
            if (!$data->success) {
                Log::error('Prepare failed');
            }

            while ($longestQueue = $this->getLongestQueue()) {
                $res = Redis::brpop($longestQueue, 2);

                if (!$res) {
                    Log::error('Redis Error Queue:' . $longestQueue);
                    continue;
                }

                $warningConfigs = unserialize($res[1])['warning_configs'];

                $stockQuotes = $this->assembleStockQuotes($warningConfigs);

                foreach ($warningConfigs as $warningConfig) {
                    $this->checkUserStockQuote($warningConfig, $stockQuotes);
                }
            }
        });
    }

    private function assembleStockQuotes($warningConfigs)
    {
        $stocks = new Collection();
        $stockQuotes = [];
        foreach ($warningConfigs as $warningConfig) {
            if (!$stocks->first(function ($stock) use ($warningConfig) {
                return $stock->id === intval($warningConfig->stock_id);
            })) {
                $stock = Stock::find($warningConfig->stock_id);
                if (!$stock) {
                    Log::error('Can not find this stock. warning_config id:' . $warningConfig->id);
                }

                $stockQuote = Redis::hgetall($this->stockPrefix . $stock->code);
                $currentPrice = floatval($stockQuote['current_price']);
                $quoteChange = floatval($stockQuote['quote_change']);
                $stockQuotes[$stock->id] = [
                    'currentPrice' => $currentPrice,
                    'quoteChange' => $quoteChange
                ];
            }
        }

        return $stockQuotes;
    }

    private function checkUserStockQuote($warningConfig, $stockQuotes)
    {
        $stockId = $warningConfig->stock_id;
        $currentPrice = $stockQuotes[$stockId]['currentPrice'];
        $quoteChange = $stockQuotes[$stockId]['quoteChange'];
        $isRemind = false;

        switch ($warningConfig->type) {
            case WarningConfig::RISE_VALUE_TYPE_VALUE:
                $warningPrice = floatVal($warningConfig->value);
                if ($currentPrice >= $warningPrice) {
                    $isRemind = true;
                }
                break;
            case WarningConfig::FALL_VALUE_TYPE_VALUE:
                $warningPrice = floatVal($warningConfig->value);
                if ($currentPrice <= $warningPrice) {
                    $isRemind = true;
                }
                break;
            case WarningConfig::RISE_RATE_TYPE_VALUE:
            case WarningConfig::FALL_RATE_TYPE_VALUE:
                $warningQuoteChange = floatval($warningConfig->value);
                if ($quoteChange * 100 >= $warningQuoteChange) {
                    $isRemind = true;
                }
                break;
        }

        if ($isRemind) {
            $user = User::find($warningConfig->user_id);
            $test = Notification::send($user, (new ThresholdReached($warningConfig, $currentPrice, $quoteChange)));
            $warningConfig->setNumberOfWarnings();
        }
        $this->info('warning_config_id: ' . $warningConfig->id . ' is_remind: ' . ($isRemind == true ? 'true' : 'false'));
    }

    private function getLongestQueue()
    {
        if ($this->queueCount === 0) {
            return false;
        }

        $longestQueueNum = 0;
        $longestQueueCount = Redis::llen($this->queueNamePrefix . $longestQueueNum);

        for ($queueNum = 1; $queueNum < $this->queueCount; $queueNum++) {
            $queueName = $this->queueNamePrefix . $queueNum;
            $count = Redis::llen($queueName);

            if ($longestQueueCount < $count) {
                $longestQueueNum = $queueNum;
                $longestQueueCount = $count;
            }
        }

        if ($longestQueueCount === 0) {
            return false;
        }

        return $this->queueNamePrefix . $longestQueueNum;
    }
}
