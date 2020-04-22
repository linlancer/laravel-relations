<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/4/21
 * Time: 17:41
 */
namespace LinLancer\Laravel\Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LinLancer\Laravel\ExtraRelationsServiceProvider;
use LinLancer\Laravel\Relations\HasManyComposite;
use LinLancer\Laravel\Relations\HasManyFromStr;
use LinLancer\Laravel\Relations\HasOneComposite;
use LinLancer\Laravel\Tests\TestModels\RelationModel;
use LinLancer\Laravel\Tests\TestModels\SourceModel;
use PHPUnit\Framework\TestCase;

class ExtraRelationsServiceProviderTest extends TestCase
{
    public function init()
    {
        $manager = new Manager;
        $options = [
            'driver'    => 'mysql',
            'host'      => '127.0.0.1',
            'database'  => 'test',
            'username'  => 'root',
            'password'  => 'root1234',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => 't_',
        ];
        $manager->addConnection($options);
        $manager->setAsGlobal();
        $manager->bootEloquent();
    }

    public function testShouldSubClassServiceProviderClass()
    {
        $rc = new \ReflectionClass(ExtraRelationsServiceProvider::class);

        $this->assertTrue($rc->isSubclassOf(\Illuminate\Support\ServiceProvider::class));
    }

    public function testHasManyFromStrMacroOnBoot()
    {
        $this->init();
        $app = Container::getInstance();
        $providerMock = new ExtraRelationsServiceProvider($app);
        $providerMock->boot();

        $model = new SourceModel;
        $find = $model->first();
        $this->assertInstanceOf(Model::class, $find);
        $relations = $find->relations;
        $this->assertInstanceOf(Collection::class, $relations);
        foreach ($relations as $relation) {
            $this->assertInstanceOf(RelationModel::class, $relation);
        }

    }

    public function testHasManyFromStrMacroOnWith()
    {
        $this->init();
        $app = Container::getInstance();
        $providerMock = new ExtraRelationsServiceProvider($app);
        $providerMock->boot();

        $model = new SourceModel;
        $find = $model->with('relations')->first();
        $this->assertInstanceOf(Model::class, $find);
        $relations = $find->relations;
        $this->assertInstanceOf(Collection::class, $relations);
        foreach ($relations as $relation) {
            $this->assertInstanceOf(RelationModel::class, $relation);
        }

    }

    public function testHasOneCompositeMacroOnBoot()
    {
        $this->init();
        $app = Container::getInstance();
        $providerMock = new ExtraRelationsServiceProvider($app);
        $providerMock->boot();

        $model = new SourceModel;
        $find = $model->first();
        $this->assertInstanceOf(Model::class, $find);
        $relation = $find->compositeRelation;
        $this->assertInstanceOf(RelationModel::class, $relation);

    }

    public function testHasOneCompositeMacroOnWith()
    {
        $this->init();
        $app = Container::getInstance();
        $providerMock = new ExtraRelationsServiceProvider($app);
        $providerMock->boot();

        $model = new SourceModel;
        $find = $model->with('compositeRelation')->first();
        $this->assertInstanceOf(Model::class, $find);
        $relation = $find->compositeRelation;
        $this->assertInstanceOf(RelationModel::class, $relation);

    }

    public function testHasManyCompositeMacroOnBoot()
    {
        $this->init();
        $app = Container::getInstance();
        $providerMock = new ExtraRelationsServiceProvider($app);
        $providerMock->boot();

        $model = new SourceModel;
        $find = $model->first();
        $this->assertInstanceOf(Model::class, $model);
        $relations = $find->compositeRelations;
        $this->assertInstanceOf(Collection::class, $relations);
        foreach ($relations as $relation) {
            $this->assertInstanceOf(RelationModel::class, $relation);
        }
    }


    public function testHasManyCompositeMacroOnWith()
    {
        $this->init();
        $app = Container::getInstance();
        $providerMock = new ExtraRelationsServiceProvider($app);
        $providerMock->boot();

        $model = new SourceModel;
        $find = $model->with('compositeRelations')->first();
        $this->assertInstanceOf(Model::class, $model);
        $relations = $find->compositeRelations;
        $this->assertInstanceOf(Collection::class, $relations);
        foreach ($relations as $relation) {
            $this->assertInstanceOf(RelationModel::class, $relation);
        }
    }
}
