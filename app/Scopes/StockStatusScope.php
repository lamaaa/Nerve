<?php
/**
 * Created by PhpStorm.
 * User: LAM
 * Date: 2018/3/25
 * Time: 10:47
 */
namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StockStatusScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder->where($model->getTable() . '.stock_status', '=', 0);
    }
}