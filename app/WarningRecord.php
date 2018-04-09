<?php

namespace App;

use App\Scopes\StatusScope;
use Illuminate\Database\Eloquent\Model;

class WarningRecord extends Model
{
    protected $fillable = [
        'user_id',
        'stock_code',
        'stock_name',
        'stock_price',
        'stock_quote_change',
        'notification_types',
        'status',
        'warning_setting'
    ];

    public static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub

        static::addGlobalScope(new StatusScope());
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public static function logWarningRecord($options)
    {
        $type = $options['type'];
        $value = $options['value'];
        unset($options['type']);
        unset($options['value']);
        $warningSetting = '';

        switch ($type) {
            case WarningConfig::RISE_VALUE_TYPE_VALUE:
                $warningSetting = '当日股价涨到' . $value;
                break;
            case WarningConfig::FALL_VALUE_TYPE_VALUE:
                $warningSetting = '当日股价跌到' . $value;
                break;
            case WarningConfig::RISE_RATE_TYPE_VALUE:
                $warningSetting = '当日涨幅超过' . $value . '%';
                break;
            case WarningConfig::FALL_RATE_TYPE_VALUE:
                $warningSetting = '当日跌幅超过' . $value . '%';
                break;
        }
        $options['warning_setting'] = $warningSetting;
        return WarningRecord::create($options);
    }
}
