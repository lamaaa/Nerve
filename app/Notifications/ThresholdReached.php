<?php

namespace App\Notifications;

use App\Stock;
use App\WarningConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Wechat\WechatChannel;
use NotificationChannels\Wechat\WechatMessage;

class ThresholdReached extends Notification implements ShouldQueue
{
    use Queueable;

    private $warningConfig;
    private $currentPrice;
    private $quoteChange;
    private $price;
    private $stock;
    private $time;

    /**
     *
     * Create a new notification instance.
     *
     * ThresholdReachedViaEmail constructor.
     * @param $warningConfig
     * @param $channel
     */
    public function __construct($warningConfig, $currentPrice, $quoteChange)
    {
        $this->warningConfig = $warningConfig;
        $this->currentPrice = $currentPrice;
        $this->quoteChange = $quoteChange;
        $this->price = $this->assemblePriceMessage();
        $this->stock = Stock::find($this->warningConfig->stock_id);
        $this->time = date('Y-m-d H:i:s', time() + 28800);
    }



    public function assemblePriceMessage()
    {
        $type = $this->warningConfig->type;
        $value = $this->warningConfig->value;

        switch ($type) {
            case WarningConfig::RISE_VALUE_TYPE_VALUE:
                return '价格高于：' . $value . '  最新价：' . $this->currentPrice . ' （设定值：' . $value .'）';
            case WarningConfig::FALL_VALUE_TYPE_VALUE:
                return '价格低于：' . $value . '  最新价：' . $this->currentPrice . ' （设定值：' . $value .'）';
            case WarningConfig::RISE_RATE_TYPE_VALUE:
                return '涨幅已达：' . round($this->quoteChange * 100, 2) . '%  最新价：' . $this->currentPrice . ' （设定值：' . $value .'%）';
            case WarningConfig::FALL_RATE_TYPE_VALUE:
                return '跌幅已达：' . round($this->quoteChange * 100, 2) . '%  最新价：' . $this->currentPrice . ' （设定值：' . $value .'%）';
        }
    }
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $notificationChannelNames = [];
        $stock = Stock::find($this->warningConfig->stock_id);
        $notificationChannels = $stock->notificationTypes;
        foreach ($notificationChannels as $notificationChannel) {
            switch ($notificationChannel->name) {
                case 'mail':
                    $notificationChannelNames[] = $notificationChannel->name;
                    break;
                case 'wechat':
                    $notificationChannelNames[] = WechatChannel::class;
                    break;
            }
        }

        return $notificationChannelNames;
    }

    public function toWechat($notifiable)
    {
        Log::info($this->price);
        $notice = [
            'templateId' => '6TAOAK7w7qYbMh8LDHUnLl5-j6WiqNuPDrrWpPuOxaM',
            'url' => '',
            'data' => [
                'title' => '股票价格提醒',
                'name' => $this->stock->name,
                'price' => $this->price,
                'time' => $this->time
            ]
        ];

        return new WechatMessage($notice);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->greeting('已达到您的目标股价')
                    ->salutation('祝君好')
                    ->subject('股价预警')
                    ->line('名称： ' . $this->stock->name)
                    ->line('价格： '  . $this->price)
                    ->line('时间：' . $this->time);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
