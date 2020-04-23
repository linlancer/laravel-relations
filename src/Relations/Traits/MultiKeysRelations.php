<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/4/22
 * Time: 9:59
 */

namespace LinLancer\Laravel\Relations\Traits;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

trait MultiKeysRelations
{
    /**
     * @param array  $key
     * @param string $table
     * @return string
     */
    private function getKeyPattern(array $key, string $table) :string
    {
        $unit = '`%s`.`%s`';
        $pattern = '(%s)';
        $patternArr = [];
        for ($i = 0; $i < count($key); $i++) {
            $patternArr[] = sprintf($unit, $table, $key[$i]);
        }
        return sprintf($pattern, implode(',', $patternArr));
    }

    private function getKeyValueFromModel($keys, $model)
    {
        $pattern = '(\'%s\')';
        $values = $this->getAttributes($keys, $model);
        if (!empty($values)) {
            return sprintf($pattern, implode('\', \'', $values));
        } else {
            return null;
        }
    }

    private function getAttributes($keys, $model)
    {
        /**
         * @var Model $model
         */
        $values = [];
        foreach ($keys as $key) {
            if (! is_null($model->getAttribute($key))) {
                $values[] = $model->getAttribute($key);
            } else {
                $values = [];
                continue;
            }
        }
        return $values;
    }

    private function setBuilderByKeyPair(Builder $builder, $key1Group, $key2Group)
    {
        if (count($key1Group) !== count($key2Group))
            return $builder;
        foreach ($key1Group as $order => $key1) {
            $key2 = $key2Group[$order];
            $builder = $builder->whereColumn($key1, '=', $key2);
        }
        return $builder;
    }
}