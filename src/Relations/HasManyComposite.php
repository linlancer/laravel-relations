<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/2/18
 * Time: 18:13
 */

namespace LinLancer\Laravel\Relations;

use Illuminate\Database\Eloquent\Collection;

class HasManyComposite extends HasOneOrManyComposite
{
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
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        return $this->matchMany($models, $results, $relation);
    }
}