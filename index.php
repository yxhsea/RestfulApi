<?php
header("Content-type:text/html;charset=utf-8");
require __DIR__.'/lib/User.php';
require __DIR__.'/lib/Article.php';
$pdo = require __DIR__.'/lib/db.php';
$user = new User($pdo);
var_dump($user->register("admin2","admin2"));
$article = new article($pdo);
/*var_dump($article->create('文章标题1','文章内容1',1));
var_dump($article->create('文章标题2','文章内容2',1));
var_dump($article->create('文章标题3','文章内容3',1));
var_dump($article->create('文章标题4','文章内容4',1));*/
//var_dump($article->getList(1,1,1));