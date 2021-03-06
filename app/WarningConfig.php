<?php

namespace App;

use App\Scopes\StatusScope;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Illuminate\Support\Collection;

class WarningConfig extends Model
{
    const RISE_VALUE_TYPE = 'riseValue';
    const FALL_VALUE_TYPE = 'fallValue';
    const RISE_RATE_TYPE = 'riseRate';
    const FALL_RATE_TYPE = 'fallRate';
    const RISE_VALUE_TYPE_VALUE = 1;
    const FALL_VALUE_TYPE_VALUE = 2;
    const RISE_RATE_TYPE_VALUE = 3;
    const FALL_RATE_TYPE_VALUE = 4;

    protected $fillable = ['stock_id', 'user_id', 'type', 'value', 'switch'];

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusScope());
    }

    public static function addAndUpdateWarningConfigs($data)
    {
        $warningConfigs = WarningConfig::where([
            'user_id' => Auth::user()->id,
            'stock_id' => $data['stockId']
        ])->get();
        $userId = Auth::user()->id;

        self::addOrUpdateWarningConfig($warningConfigs, self::RISE_VALUE_TYPE, self::RISE_VALUE_TYPE_VALUE, $userId, $data);
        self::addOrUpdateWarningConfig($warningConfigs, self::FALL_VALUE_TYPE, self::FALL_VALUE_TYPE_VALUE, $userId, $data);
        self::addOrUpdateWarningConfig($warningConfigs, self::RISE_RATE_TYPE, self::RISE_RATE_TYPE_VALUE, $userId, $data);
        self::addOrUpdateWarningConfig($warningConfigs, self::FALL_RATE_TYPE, self::FALL_RATE_TYPE_VALUE, $userId, $data);

        self::updateNotificationTypes($data);

        return true;
    }

    private static function addOrUpdateWarningConfig($warningConfigs, $type, $typeValue, $userId, $data)
    {
        $value = $data[$type];
        $switch = $data[$type . 'Switch'];
        $stockId = $data['stockId'];
        $warningConfig = null;
        if ($warningConfig = $warningConfigs->first(function ($warningConfig) use ($typeValue) {
            return $warningConfig->type == $typeValue;
            })) {
            // 已有记录
            if ($switch) {
                $warningConfig->value = $value;
            }
            $warningConfig->switch = $switch;
        } else {
            if ($switch) {
                // 未有记录
                $warningConfig = new WarningConfig();
                $warningConfig->stock_id = $stockId;
                $warningConfig->user_id = $userId;
                $warningConfig->type = $typeValue;
                $warningConfig->value = $value;
                $warningConfig->switch = $switch;
            }
        }

        if ($warningConfig != null) {
            return $warningConfig->save();
        }

        return true;
    }

    private static function updateNotificationTypes($data)
    {
        $stock = Stock::find($data['stockId']);
        $newNotificationTypes = new Collection();
        foreach ($data['checkedNotificationTypes'] as $checkedNotificationType) {
            $newNotificationTypes[] = NotificationType::where(['name' => $checkedNotificationType])->first();
        }

        NotificationType::updateNotificationTypes($stock, $newNotificationTypes);

        return true;
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function notificationTypes()
    {
        return $this->belongsTo('App\NotificationType');
    }

    public function scopeHaveNotWarning($query)
    {
        return $query->where(['number_of_warnings' => 0]);
    }

    public function setNumberOfWarnings()
    {
        $this->number_of_warnings++;
        return $this->save();
    }
}
