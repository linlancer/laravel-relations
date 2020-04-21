<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/4/21
 * Time: 17:41
 */

use LinLancer\Laravel\ExtraRelationsServiceProvider;

class ExtraRelationsServiceProviderTest extends \PHPUnit\Framework\TestCase
{
    public function init()
    {
        $manager = new \Illuminate\Database\Capsule\Manager;
        $options = [
            'driver'    => 'mysql',
            'host'      => '192.168.66.33',
            'database'  => 'purchase',
            'username'  => 'root',
            'password'  => 'Hexin2007',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => 'hp_',
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
}
