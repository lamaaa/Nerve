<?php
/**
 * Created by PhpStorm.
 * User: LAM
 * Date: 2018/3/23
 * Time: 16:37
 */
namespace App\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class StatusScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder->where($model->getTable() . '.status', '=', 1);
    }
}