<?php

/*
 * This file is part of the watchern/php-snowflake.
 *
 * (c) Charles.YK <charles@reepan.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */
class Snowflake
{
    private static $nodeId;
    private static $sequence = 0;
    private static $lastTimestamp = -1;
    private static $self = null;

    const MAX_NODE_ID = 1023; // 最大的机器节点, 2^10 - 1
    const MAX_SEQUENCE = 4095; // 最大的序列节点, 2^12 - 1
    const MIN_TIMESTAMP = 1099511627775; //
    const SHIFT_NODE_ID = 12; // 机器ID左移位数,63 - 51
    const SHIFT_TIMESTAMP = 22; // 毫秒时间戳左移位数,63 - 41
    const EPOCH = 1616308934598; // 开始时间,固定一个小于当前时间的毫秒数

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (self::$self == null) {
            self::$self = new self();
        }
        return self::$self;
    }

    public function setNodeId($nodeId)
    {
        if ($nodeId > self::MAX_NODE_ID || $nodeId < 0) {
            throw new \Exception("Node Id can't be greater than ".self::MAX_NODE_ID." or less than 0");
        }
        self::$nodeId = $nodeId;
        return self::$self;
    }

    private function timeGen(): string
    {
        //获得当前时间戳
        $time = explode(' ', microtime());
        $time2 = substr($time[0], 2, 3);
        return $time[1] . $time2;
    }

    private function tilNextMillis($lastTimestamp): string
    {
        $timestamp = $this->timeGen();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->timeGen();
        }
        return $timestamp;
    }

    public function nextId(): int
    {
        if (PHP_INT_SIZE === 4) {
            return 0;
        }
        $timestamp = $this->timeGen();

        //如果存在并发调用，则自增sequence
        if (self::$lastTimestamp == $timestamp) {
            self::$sequence = (self::$sequence + 1) & self::MAX_SEQUENCE;

            //如果sequence自增到4095，也就是4096 & 4095 = 0，重新取时间戳
            if (self::$sequence == 0) {
                $timestamp = $this->tilNextMillis(self::$lastTimestamp);
            }
        } else {
            self::$sequence = 0;
        }

        if ($timestamp < self::$lastTimestamp) {
            throw new \Exception("Clock moved backwards.  Refusing to generate id for " . (self::$lastTimestamp - $timestamp) . " milliseconds");
        }

        self::$lastTimestamp = $timestamp;

        // ID偏移组合生成最终的ID，并返回ID
        return ((sprintf('%.0f', $timestamp+self::MIN_TIMESTAMP) - sprintf('%.0f', self::EPOCH)) << self::SHIFT_TIMESTAMP)
            | (self::$nodeId << self::SHIFT_NODE_ID)
            | self::$sequence;

    }

    public function decodeFromId($id,$json=0) {
        $id = decbin($id);
        $ts = bindec(substr($id,0,41))- self::MIN_TIMESTAMP + self::EPOCH;
        $nid = bindec(substr($id,41,10));
        $seq = bindec(substr($id,51,12));
        $data = [
            "ts"=>$ts,
            'nid'=>$nid,
            "seq"=>$seq
        ];
        if($json){
            return json_encode($data);
        }
        return $data;
    }
}