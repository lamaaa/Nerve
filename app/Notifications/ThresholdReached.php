<?php

namespace App\Notifications;

use App\Stock;
use App\WarningConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ThresholdReached extends Notification implements ShouldQueue
{
    use Queueable;

    private $warningConfig;
    private $currentPrice;
    private $quoteChange;

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
            $notificationChannelNames[] = $notificationChannel->name;
        }

        return $notificationChannelNames;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $stockId = $this->warningConfig->stock_id;
        $type = $this->warningConfig->type;
        $value = $this->warningConfig->value;
        $message = '';

        $stock = Stock::find($stockId);
        switch ($type) {
            case WarningConfig::RISE_VALUE_TYPE_VALUE:
                $message = '价格高于：' . $value . '  最新价：' . $this->currentPrice . ' （设定值：' . $value .'）';
                break;
            case WarningConfig::FALL_VALUE_TYPE_VALUE:
                $message = '价格低于：' . $value . '  最新价：' . $this->currentPrice . ' （设定值：' . $value .'）';
                break;
            case WarningConfig::RISE_RATE_TYPE_VALUE:
                $message = '涨幅已达：' . $this->quoteChange . '  最新价：' . $this->currentPrice . ' （设定值：' . $value .'）';
                break;
            case WarningConfig::FALL_RATE_TYPE_VALUE:
                $message = '跌幅已达：' . $this->quoteChange . '  最新价：' . $this->currentPrice . ' （设定值：' . $value .'）';

        }
        return (new MailMessage)
                    ->greeting('已达到您的目标股价')
                    ->salutation('祝君好')
                    ->subject('股价预警')
                    ->line('名称： ' . $stock->name)
                    ->line('价格： '  . $message)
                    ->line('时间：' . date('Y-m-d H:i:s', time()));
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
