<?php
/**
 * 连接数据库并返回连接句柄
 */
$pdo = new PDO('mysql:host=localhost;dbname=test','root','1234',array(PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8",PDO::ATTR_EMULATE_PREPARES=>false));

return $pdo;
