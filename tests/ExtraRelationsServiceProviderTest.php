<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/4/21
 * Time: 17:41
 */
namespace LinLancer\Laravel\Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LinLancer\Laravel\ExtraRelationsServiceProvider;
use LinLancer\Laravel\Relations\HasManyFromStr;
use LinLancer\Laravel\Tests\TestModels\SourceModel;

class ExtraRelationsServiceProviderTest extends \PHPUnit\Framework\TestCase
{
    public function init()
    {
        $manager = new \Illuminate\Database\Capsule\Manager;
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
    public function testMacroOnBoot()
    {
        $this->init();
        $app = Container::getInstance();
        $providerMock = new ExtraRelationsServiceProvider($app);
        $providerMock->boot();

        $model = new SourceModel;
        $this->assertInstanceOf(Model::class, $model);

        $relation = $model->relations;
        $this->assertInstanceOf(HasManyFromStr::class, $relation);

    }
}
