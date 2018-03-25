<?php

namespace App;

use App\Scopes\StatusScope;
use App\Scopes\StockStatusScope;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['name', 'exchange', 'code'];

    protected $hidden = ['pivot', 'status'];

    const NORMAL = 0;
    const ABNORMAL = 1;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusScope());
        static::addGlobalScope(new StockStatusScope());
    }

    public static function changeStatus($toChangeStockCodes, $toStatus)
    {
        if (in_array($toStatus, [self::NORMAL, self::ABNORMAL]) && count($toChangeStockCodes) > 0) {
            foreach ($toChangeStockCodes as &$toChangeStockCode) {
                $toChangeStockCode = substr($toChangeStockCode, 2);
            }
            return Stock::whereIn('code', $toChangeStockCodes)
            ->update([
                'stock_status' => $toStatus
            ]);
        }
        return false;
    }

    public function users()
    {
        return $this->belongsToMany('App\User')->withTimestamps();
    }
}
