<?php

namespace LinLancer\Laravel\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use LinLancer\Laravel\Relations\Traits\MultiKeysRelations;

class BelongsCompositeTo extends Relation
{
    use SupportsDefaultModels;
    use MultiKeysRelations;

    /**
     * The child model instance of the relation.
     */
    protected $child;

    /**
     * The foreign key of the parent model.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The associated key on the parent model.
     *
     * @var string
     */
    protected $ownerKey;

    protected $ownerKeyWithTable;

    protected $foreignKeyWithTable;
    /**
     * The name of the relationship.
     *
     * @var string
     */
    protected $relationName;

    protected $pattern = '(`table`.`%s`,`table`.`%s`)';

    /**
     * The count of self joins.
     *
     * @var int
     */
    protected static $selfJoinCount = 0;

    /**
     * Create a new belongs to relationship instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $child
     * @param  array  $foreignKey
     * @param  array  $ownerKey
     * @param  string  $relationName
     *
     * @return void
     */
    public function __construct(Builder $query, Model $child, array $foreignKey, array $ownerKey, $relationName)
    {

        // In the underlying base relationship class, this variable is referred to as
        // the "parent" since most relationships are not inversed. But, since this
        // one is we will create a "child" variable for much better readability.
        $this->child = $child;



        $relatedTable = $query->getConnection()->getTablePrefix() . $query->getModel()->getTable();
        $childTable = $query->getConnection()->getTablePrefix() . $this->child->getTable();
        $this->ownerKey = $ownerKey;
        $this->ownerKeyWithTable = $this->getKeyPattern($ownerKey, $relatedTable);
        $this->relationName = $relationName;
        $this->foreignKey = $foreignKey;
        $this->foreignKeyWithTable = $this->getKeyPattern($foreignKey, $childTable);

        parent::__construct($query, $child);

    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        if (is_null($this->child->{$this->foreignKey})) {
            return $this->getDefaultFor($this->parent);
        }

        return $this->query->first() ?: $this->getDefaultFor($this->parent);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            // For belongs to relationships, which are essentially the inverse of has one
            // or has many relationships, we need to actually query on the primary key
            // of the related models matching on the foreign key that's on a parent.
            $this->query->whereRaw("{$this->ownerKeyWithTable} = {$this->foreignKeyWithTable}");
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        // We'll grab the primary key name of the related models since it could be set to
        // a non-standard name and not "id". We will then construct the constraint for
        // our eagerly loading query so it returns the proper models from execution.
        $key = $this->ownerKeyWithTable;
        $pattern = '%s IN (%s)';
        $raw = sprintf($pattern, $key, implode(',', $this->getEagerModelKeys($models)));
        $this->query->whereRaw($raw);
    }

    /**
     * Gather the keys from an array of related models.
     *
     * @param  array  $models
     * @return array
     */
    protected function getEagerModelKeys(array $models)
    {
        $keys = [];

        // First we need to gather all of the keys from the parent models so we know what
        // to query for via the eager loading query. We will add them to an array then
        // execute a "where in" statement to gather up all of those related records.
        foreach ($models as $model) {
            $foreignKeys = $this->getKeyValueFromModel($this->foreignKey, $model);
            if (!empty($foreignKeys)) {
                $keys[] = $foreignKeys;
            }

        }

        sort($keys);

        return array_values(array_unique($keys));
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
            $model->setRelation($relation, $this->getDefaultFor($model));
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
        $foreign = $this->foreignKey;

        $owner = $this->ownerKey;

        // First we will get to build a dictionary of the child models by their primary
        // key of the relationship, then we can easily match the children back onto
        // the parents using that dictionary and the primary key of the children.
        $dictionary = [];

        foreach ($results as $result) {
            $attrKeys = $this->getAttributes($owner, $result);
            if (!empty($attrKeys)) {
                $attrKey = implode($attrKeys);
                $dictionary[$attrKey] = $result;
            }
        }

        // Once we have the dictionary constructed, we can loop through all the parents
        // and match back onto their children using these keys of the dictionary and
        // the primary key of the children to map them onto the correct instances.
        foreach ($models as $model) {
            $attrKeys = $this->getAttributes($foreign, $model);
            if (!empty($attrKeys)) {
                $attrKey = implode($attrKeys);
                if (isset($dictionary[$attrKey])) {
                    $model->setRelation($relation, $dictionary[$attrKey]);
                }
            }
        }

        return $models;
    }

    /**
     * Update the parent model on the relationship.
     *
     * @param  array  $attributes
     * @return mixed
     */
    public function update(array $attributes)
    {
        return $this->getResults()->fill($attributes)->save();
    }

    /**
     * Associate the model instance to the given parent.
     *
     * @param  \Illuminate\Database\Eloquent\Model|int|string  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function associate($model)
    {
        $ownerKey = $model instanceof Model ? $model->getAttribute($this->ownerKey) : $model;

        $this->child->setAttribute($this->foreignKey, $ownerKey);

        if ($model instanceof Model) {
            $this->child->setRelation($this->relationName, $model);
        } elseif ($this->child->isDirty($this->foreignKey)) {
            $this->child->unsetRelation($this->relationName);
        }
        return $this->child;
    }

    /**
     * Dissociate previously associated model from the given parent.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function dissociate()
    {
        $this->child->setAttribute($this->foreignKey, null);

        return $this->child->setRelation($this->relationName, null);
    }

    /**
     * Add the constraints for a relationship query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($parentQuery->getQuery()->from == $query->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }
        $key1 = $this->getQualifiedForeignKeyName();
        $key2 = $this->qualifyColumns($this->ownerKey, $query->getModel());
        return $this->setBuilderByKeyPair($query->select($columns), $key1, $key2);
    }

    /**
     * Add the constraints for a relationship query on the same table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->select($columns)->from(
            $query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash()
        );

        $query->getModel()->setTable($hash);
        $foreignKeys = $this->getQualifiedForeignKeyName();
        $ownerKeys = array_map(function ($value) use ($hash) {
            return $hash . '.' . $value;
        }, $this->ownerKey);
        return $this->setBuilderByKeyPair($query, $ownerKeys, $foreignKeys);
    }

    /**
     * @param array $columns
     * @param $model
     * @return mixed
     */
    public function qualifyColumns(array $columns, $model)
    {
        foreach ($columns as &$column) {
            if (Str::contains($column, '.')) {
                continue;
            }

            $column = $model->getTable().'.'.$column;
        }
        return $columns;
    }
    /**
     * Get a relationship join table hash.
     *
     * @return string
     */
    public function getRelationCountHash()
    {
        return 'laravel_reserved_'.static::$selfJoinCount++;
    }

    /**
     * Determine if the related model has an auto-incrementing ID.
     *
     * @return bool
     */
    protected function relationHasIncrementingId()
    {
        return $this->related->getIncrementing() &&
            $this->related->getKeyType() === 'int';
    }

    /**
     * Make a new related instance for the given model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function newRelatedInstanceFor(Model $parent)
    {
        return $this->related->newInstance();
    }

    /**
     * Get the child of the relationship.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * Get the foreign key of the relationship.
     *
     * @return string
     */
    public function getForeignKeyName()
    {
        return $this->foreignKey;
    }

    /**
     * Get the fully qualified foreign key of the relationship.
     *
     * @return array
     */
    public function getQualifiedForeignKeyName()
    {
        return $this->qualifyColumns($this->foreignKey, $this->child);
    }

    /**
     * Get the associated key of the relationship.
     *
     * @return string
     */
    public function getOwnerKeyName()
    {
        return $this->ownerKey;
    }

    /**
     * Get the fully qualified associated key of the relationship.
     *
     * @return array
     */
    public function getQualifiedOwnerKeyName()
    {
        return $this->qualifyColumns($this->ownerKey, $this->related);
    }

    /**
     * Get the name of the relationship.
     *
     * @return string
     */
    public function getRelationName()
    {
        return $this->relationName;
    }

    /**
     * Get the name of the relationship.
     *
     * @return string
     * @deprecated The getRelationName() method should be used instead. Will be removed in Laravel 6.0.
     */
    public function getRelation()
    {
        return $this->relationName;
    }
}
