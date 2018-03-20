<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['name', 'exchange', 'code'];

    const NORMAL = 0;
    const ABNORMAL = 1;

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
    //
}
