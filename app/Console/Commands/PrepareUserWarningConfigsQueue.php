<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class PrepareUserWarningConfigsQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prepare:userWarningConfigsQueue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Put user\'s WarningConfigs Into Redis Queue From MySQL';

    private $queueNamePrefix;
    private $queueCount;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->queueNamePrefix = Config::get('database.redis.default.prefix') . 'warning_config:user:';
        $this->queueCount = env('REDIS_WARNING_CONFIG_USER_QUEUE_COUNT', 3);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::all();

        foreach ($users->chunk(count($users) / $this->queueCount + 1) as $index => $chunk) {
            $queueName = $this->queueNamePrefix . $index;
            foreach ($chunk as $user) {
                $queueItem = [];
                $queueItem['user_id'] = $user->id;
                $queueItem['warning_configs'] = $user->warningConfigs()->haveNotWarning()->get();
                \Log::info($queueItem);
                Redis::lpush($queueName, serialize($queueItem));
            }
        }

        Redis::publish('prepare-warning-configs-channel', json_encode(['success' => true]));
        $this->info('Prepared Finished');
    }
}
