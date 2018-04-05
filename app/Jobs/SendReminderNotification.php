<?php

namespace App\Jobs;

use App\Notifications\ThresholdReached;
use App\Stock;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendReminderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 15;
    private $warningConfig;
    private $currentPrice;
    private $quoteChange;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($warningConfig, $currentPrice, $quoteChange)
    {
        $this->warningConfig = $warningConfig;
        $this->currentPrice = $currentPrice;
        $this->quoteChange = $quoteChange;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->warningConfig->user_id);
        Notification::send($user, new ThresholdReached($this->warningConfig, $this->getNotificationChannelNames($this->warningConfig), $this->currentPrice, $this->quoteChange));
    }

    private function getNotificationChannelNames($warningConfig)
    {
        $notificationChannelNames = [];
        $stock = Stock::find($warningConfig->stock_id);
        $notificationChannels = $stock->notificationTypes;
        foreach ($notificationChannels as $notificationChannel) {
            $notificationChannelNames[] = $notificationChannel->name;
        }

        return $notificationChannelNames;
    }
}
