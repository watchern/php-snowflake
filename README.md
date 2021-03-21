<div>
  <p align="center">An ID Generator for PHP based on Snowflake Algorithm (Twitter announced).</p>
</div>

## 说明

雪花算法的 PHP 实现

Snowflake 是 Twitter 内部的一个 ID 生算法，可以通过一些简单的规则保证在大规模分布式情况下生成唯一的 ID 号码。其组成为：

* 第一个 bit 为未使用的符号位。
* 第二部分由 41 位的时间戳（毫秒）构成，他的取值是当前时间相对于某一时间的偏移量。
* 第三部分 10 个 bit 位表示机器节点NODE，其能表示的最大值为 2^10 -1 = 1023。
* 最后部分由 12 个 bit 组成，其表示每个工作节点**每毫秒**生成的序列号 SEQ，同一毫秒内最多可生成 2^12 -1 即 4095 个 SEQ。

需要注意的是：

* 在分布式环境中，10 个 bit 位的 node 表示最多能部署 1023 台机器节点
* 41 位的二进制长度最多能表示 2^41 -1 毫秒即 69 年，所以雪花算法最多能正常使用 69 年，为了能最大限度的使用该算法，你应该为其指定一个开始时间。

> 由上可知，雪花算法生成的 ID 并不能保证唯一，如当两个不同请求同一时刻进入相同的节点时，而此时该节点生成的 sequence 又是相同时，就会导致生成的 ID 重复。

所以要想使用雪花算法生成唯一的 ID，就需要保证同一节点同一毫秒内生成的序列号自增。

## 使用

简单使用.

```php
$node_id = rand(1, 1023);//随机数
$id = Snowflake::getInstance()->setNodeId(1)->nextId();
echo $id.'==>'.json_encode(Snowflake::getInstance()->decodeFromId($id));

// 5985252473667850242==>{"ts":1616318737218,"nid":1,"seq":2}

```


## License

MIT