<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/4/21
 * Time: 16:51
 */
namespace LinLancer\Laravel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use LinLancer\Laravel\Relations\{
    BelongsCompositeTo,
    HasManyComposite,
    HasManyFromStr,
    HasOneComposite
};

class ExtraRelationsServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot():void
    {
        Builder::macro('belongsCompositeTo', function ($related, $foreignKey = [], $ownerKey = []) {
            /**
             * @var $model Model
             */
            $model = $this->getModel();
            $instance = $model->newRelatedInstance($related);
            return new BelongsCompositeTo($instance->getQuery(), $model, $foreignKey, $ownerKey, null);
        });

        Builder::macro('hasManyComposite', function ($related, $foreignKey = [], $localKey = []) {
            /**
             * @var $model Model
             */
            $model = $this->getModel();
            $instance = $model->newRelatedInstance($related);
            $foreignKey = $foreignKey ?: $model->getForeignKey();
            $localKey = $localKey ?: $model->getKeyName();
            return new HasManyComposite($instance->newQuery(), $model, $foreignKey, $localKey);
        });

        Builder::macro('hasOneComposite', function ($related, $foreignKey = [], $localKey = []) {
            /**
             * @var $model Model
             */
            $model = $this->getModel();
            $instance = $model->newRelatedInstance($related);
            $foreignKey = $foreignKey ?: $model->getForeignKey();
            $localKey = $localKey ?: $model->getKeyName();
            return new HasOneComposite($instance->newQuery(), $model, $foreignKey, $localKey);
        });

        Builder::macro('hasManyFromStr', function ($related, $foreignKey = null, $localKey = null, $separator = ',') {
            /**
             * @var $model Model
             */
            $model = $this->getModel();
            $instance = $model->newRelatedInstance($related);
            $foreignKey = $foreignKey ?: $model->getForeignKey();
            $localKey = $localKey ?: $model->getKeyName();
            return new HasManyFromStr($instance->getQuery(), $model, $instance->getTable().'.'.$foreignKey, $localKey, $separator);
        });

        \Illuminate\Database\Query\Builder::macro('sql', function () {
            $bindings = $this->getBindings();
            $sql = str_replace('?','\'%s\'',$this->toSql());
            return vsprintf($sql, $bindings);
        });

        Builder::macro('sql', function(){
            return ($this->getQuery()->sql());
        });

        \Illuminate\Database\Query\Builder::macro('handle', function ($column, $operator = null, $value = null, $boolean = 'and') {
            $query = $this;
            if (is_array($column)) {
                foreach ($column as $k => $item) {
                    //如果不是数组先包裹成数组
                    !is_array($item) && $item = [$item];
                    //兼容tp  where【列名】 = 【操作符，【条件】】 的旧格式
                    if (is_string($k) && !is_numeric($k)) {
                        array_unshift($item, $k);
                    }
                    if (count($item) >= 3 && !in_array($item[1], $query->operators)) {
                        switch (strtolower($item[1])) {
                            case 'between':
                                unset($item[1]);
                                $item = array_values($item);
                                $query->whereBetween(...$item);
                                break;
                            case 'in':
                                unset($item[1]);
                                $item = array_values($item);
                                $query->whereIn(...$item);
                                break;
                            default:
                                break;
                        }
                    } else {
                        $query->where(...$item);
                    }
                }
            } else {
                if (func_num_args() >= 3 && !in_array($operator, $query->operators)) {
                    switch (strtolower($operator)) {
                        case 'between':
                            $query->whereBetween(...func_get_args());
                            break;
                        case 'in':
                            $query->whereIn(...func_get_args());
                            break;
                        default:
                            break;
                    }
                } else {
                    $query->where(...func_get_args());
                }
            }
            return $query;
        });

        Builder::macro('handle', function($column, $operator = null, $value = null, $boolean = 'and'){
            $query = $this->getQuery()->handle($column, $operator, $value, $boolean);
            return $this->setQuery($query);
        });
    }


}