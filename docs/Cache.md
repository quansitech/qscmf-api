# 缓存机制

```
同一个Api，处理相同的请求参数,在数据没有变动的情况下返回值应该是不变的。
使用缓存可以减轻数据库、服务器压力，提高系统响应速度。

根据请求参数设置缓存策略，灵活处理接口的缓存数据。
当数据有变动时，自动清理相关接口的缓存。
```

**当接口返回的数据与用户态相关时，不应该使用缓存，否则会出现用户数据错乱的情况。**

#### 用法

+ 缓存类型*DATA_CACHE_TYPE*需设置为*Redis*

+ 将Api缓存机制开关打开，详情请看对应的Api文档

+ 配置接口缓存策略，符合策略则设置缓存，[查看缓存策略说明](https://github.com/quansitech/qscmf-api/blob/master/docs/Cache.md#缓存策略说明)
  
  ```php
  // 不同版本的接口需要不同的缓存策略
  protected array $cache_strategy = [
      'gets' => [['type' => 'not_exists', 'strategy' => 'id', 'cache' => 3600]],
      'gets_v2' => [['type' => 'all', 'cache' => 3600]],
  ];
  ```

+ 接口需返回 *\QscmfApiCommon\Cache\Response* 对象
  
  ```php
  public function gets(){
      // 业务代码
      return new \QscmfApiCommon\Cache\Response('成功', 1, $res);
  }
  ```
  
  + *\QscmfApiCommon\Cache\Response* 对象说明
    
    + 实例化对象属性说明
      
      | 属性名称           | 类型           | 是否必填 | 说明      | 默认值 |
      |:-------------- |:------------ | ---- |:------- | --- |
      | message        | string       | 是    | 返回信息    |     |
      | status         | int I string | 是    | 接口状态    |     |
      | data           | mixed        | 否    | 返回数据    | ''  |
      | code           | int I string | 否    | 返回请求状态码 | 200 |
      | extra_res_data | array        | 否    | 额外合并的数据 | []  |
      
      ```php
      new Response($message,$status,$data,$code,(array)$extra_res_data);
      ```
    
    + toArray 将对象转为数组，键为所有属性
    
    + toJson 将对象转为json字符串，类属性即json属性

+ 配置模型层*relate_api_controllers*属性，数据变动时清空相关接口的缓存数据
  
  ```php
  // 在ActivityModel中配置public属性relate_api_controllers
  // 当Activity数据有新增、更新、删除时，都删除对应接口的数据
  public array $relate_api_controllers = [
      'insert' => ActivityController::class,
      'update' => [
          ActivityController::class,
          SchoolActivityController::class,
          ClassController::class,
          ReadRecordController::class
      ],
      'delete' => [
          ActivityController::class,
       ]
  ];
  ```

#### 缓存策略说明

| 设置值      | 类型            | 说明        |
|:-------- |:------------- |:--------- |
| type     | string        | 策略类型      |
| strategy | string\|array | 参数字段      |
| cache    | int           | 缓存时间，单位为秒 |

##### 类型说明

+ **all** 所有情况都设置缓存
  
  ```php
  protected array $cache_strategy = [
      'gets' => [['type' => 'all','cache' => 3600]],
  ];
  ```

+ **in** 仅参数字段符合配置则设置缓存
  
  ```php
  // 请求参数只存在id，才会设置缓存
  protected array $cache_strategy = [
      'gets' => [['type' => 'in','strategy' => 'id','cache' => 3600]],
  ];
  ```

+ **exists** 参数存在某个字段则设置缓存
  
  ```php
  protected array $cache_strategy = [
      'gets' => [
          // 请求参数只要存在id，就会设置缓存
          ['type' => 'exists', 'strategy' => 'id', 'cache' => 3600],
          // 请求参数只要存在name，就会设置缓存
          ['type' => 'exists', 'strategy' => 'name', 'cache' => 3600],
      ],
  ];
  
  // 效果与以上一致
  protected array $cache_strategy = [
      'gets' => [
          // 请求参数只要存在id或者name，就会设置缓存
          ['type' => 'exists', 'strategy' => ['field' => ['id','name'], 'logic' => 'or'], 'cache' => 3600],
      ],
  ];
  ```

+ **not_exists** 参数不存在某个字段则设置缓存
  
  ```php
  // 请求参数只要不存在id，就会设置缓存
  protected array $cache_strategy = [
      'gets' => [['type' => 'not_exists','strategy' => 'id','cache' => 3600]],
  ];
  ```

缓存数据的数据结构为*hash*，根据接口分组，不同的请求参数为一个*member*

```php
// 只有参数有值时有效，如以下url实际为同一个member

"http://qscmf.qs.com/IntranetApi/Demo?id=1&name=&nick_name=&page=&per_page="

"http://qscmf.qs.com/IntranetApi/Demo?id=1"
```

如缓存前缀*prefix*为*qs_cmf*，模块为*Api*，控制器*DemoController*的缓存键值：
*qs_cmf_Api_Controller_DemoController*


#### 设置值

设置值可以在 app/Common/Conf/config.php 里设置

| 设置值                      | 说明               | 默认值                                                       |
| :-------------------------- |:-----------------| :----------------------------------------------------------- |
| QSCMFAPI_CACHE_DIR        | 记录使用了缓存机制Api的文件夹 | [ROOT_PATH . '/app/Api/Controller'] |


#### 手动清空缓存值
```text
清除设置值 QSCMFAPI_CACHE_DIR 的目录下的所有缓存值 
```
```php
// 需在 CLI Mode 执行脚本
// php www/index.php /ExtendApi/ClearApiCache/clear
```