<?php

namespace LinLancer\Laravel\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

class HasManyFromStr extends HasOneOrMany
{
    protected $separator = ',';

    public function __construct(Builder $query, Model $parent, string $foreignKey, string $localKey, $separator)
    {
        parent::__construct($query, $parent, $foreignKey, $localKey);
        $this->separator = $separator;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return ! is_null($this->getParentKey())
            ? $this->query->get()
            : $this->related->newCollection();
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array   $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * 重写匹配方法
     * @param array      $models
     * @param Collection $results
     * @param string     $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            $keys = $model->getAttribute($this->localKey);
            $keys = explode($this->separator, $keys);
            $keys = array_unique(array_filter($keys));
            $type = 'one';
            $relationResults = [];
            foreach ($keys as $key) {
                if (isset($dictionary[$key])) {
                    $temp = $this->getRelationValue($dictionary, $key, $type);
                    $relationResults[] = $temp;
                }
            }
            $model->setRelation(
                $relation, collect($relationResults)
            );
        }

        return $models;
    }

    /**
     * Get all of the primary keys for an array of models.
     *
     * @param  array   $models
     * @param  string  $key
     * @return array
     */
    protected function getKeys(array $models, $key = null)
    {
        $keysArr = [];
        collect($models)->map(function ($value) use ($key, &$keysArr) {
            $result = $key ? $value->getAttribute($key) : $value->getKey();
            $keysArr = array_merge($keysArr, explode(',', $result));
        });
        return collect($keysArr)->values()->filter()->unique()->sort()->all();
    }
}
