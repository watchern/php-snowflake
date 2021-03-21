<?php

$node_id = rand(1, 1023);//随机数
$id = Snowflake::getInstance()->setNodeId(1)->nextId();
echo $id.'==='.json_encode(Snowflake::getInstance()->decodeFromId($id));