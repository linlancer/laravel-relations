<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/4/21
 * Time: 18:21
 */
namespace LinLancer\Laravel\Tests\TestModels;

use Illuminate\Database\Eloquent\Model;

class SourceModel extends Model
{
    public $connection = 'default';

    public $table = 'source';

    public $timestamps = false;

    public function relations()
    {
        return $this->hasManyFromStr(RelationModel::class, 'id', 'ids');
    }

    public function compositeRelation()
    {
        return $this->hasOneComposite(RelationModel::class, ['name', 'code'], ['name', 'code']);
    }
    public function compositeRelations()
    {
        return $this->hasManyComposite(RelationModel::class, ['name', 'code'], ['name', 'code']);
    }
}