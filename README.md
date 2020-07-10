# laravel-relations

laravel扩展关联 多字段关联匹配 特殊字符分隔关联匹配
===============================

## INSTALLATION
  使用composer作为包管理引入
  ```
  composer require linlancer/laravel-relations
  ```
  在 config文件夹中 修改app.php中的 providers 在末尾中加入
  ```
        /**
         * ExtraRelations
         */
        \LinLancer\Laravel\ExtraRelationsServiceProvider::class,
  ```
  

## USAGE
  组件内实现了一对多、 一对一、一对一反向多键关联、一对一根据特殊字符分隔关联（一对多）
  * 一对多多键关联 接收三个参数 关联表类名  外键 本地键
  ```
    public function compositeRelations()
    { 
        return $this->hasManyComposite(RelationModel::class, ['name', 'code'], ['name', 'code']);
    }
  ```
  * 一对一多键关联 接收三个参数 关联表类名  外键 本地键
  ```
    public function compositeRelation()
    { 
        return $this->hasOneComposite(RelationModel::class, ['name', 'code'], ['name', 'code']);
    }
  ```
  * 一对一多键反向关联 接收三个参数 关联表类名  外键 反向关联表对应键
  ```
    public function compositeRelations()
    { 
        return $this->belongsCompositeTo(RelationModel::class, ['name', 'code'], ['name', 'code']);
    }
  ```
  * 一对一根据特殊字符分隔关联（一对多） 接收四个参数 关联表类名 外键 本地建 分隔符（默认为逗号）
  ```
    public function relations()
    {
        return $this->hasManyFromStr(RelationModel::class, 'id', 'ids');
    }
  
  ```
 

## AVAILABLE MACRO LIST

    组件中亦实现了 记录sql日志专用的toSql方法   sql 可在模型中或表中调用
    特殊条件处理方法handle() 接收的参数同where,与框架自带的方法可以无缝衔接
    具体用法可以如下
    
    
```
        $where['condition1'] = ['between', [1,10]];
        $where['condition2'] = ['in', [21,25,12]];
        $model = new Order;
        $model->where('condition3', 'aaaaabbb')
            ->whereIn('condition4', [1,2,3])
            ->handle($where)
            ->sql();
```
    
    最终生成的语句如下
    
```
        "select * from `hp_order` where `condition3` = ? and `condition4` in (?, ?, ?) and `condition1` between ? and ? and `condition2` in (?, ?, ?)"

```
