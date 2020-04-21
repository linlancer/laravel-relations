<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/4/21
 * Time: 18:21
 */
namespace LinLancer\Laravel\Tests\TestModels;

class SourceModel extends \Illuminate\Database\Eloquent\Model
{
    public $connection = 'default';

    public $table = 'source';

    public $timestamps = false;

    public function relations()
    {
        return $this->hasManyFromStr(RelationModel::class, 'ids', 'id');
    }
}