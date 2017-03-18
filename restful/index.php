<?php
require __DIR__.'/../lib/User.php';
require __DIR__.'/../lib/Article.php';
$pdo = require __DIR__.'/../lib/db.php';

class Restful{
	/**
	 * User
	 */
	private $_user;

	/**
	 * Article
	 */
	private $_article;

	/**
	 * 请求方法
	 */
	private $_requestMethod;

	/**
	 * 资源名称
	 */
	private $_resourceName;

	/**
	 * 请求ID
	 */
	private $_id;

	/**
	 * 允许请求的资源列表
	 *
	 * @var        array
	 */
	private $_allowResources = ['Users','Articles'];

	/**
	 * 允许请求的方法
	 *
	 * @var        array
	 */
	private $_allowRequestMethods = ['GET','POST','PUT','DELETE','OPTIONS'];

	/**
	 * http请求状态码
	 *
	 * @var        array
	 */
	private $_statusCodes = [
		200 => 'OK',
		204 => 'No Content',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		500 => 'Server Internet Error'
 	];

	/**
	 * 构造方法
	 *
	 * @param      User     $_user     The user
	 * @param      Article  $_article  The article
	 */
	public function __construct(User $_user,Article $_article){
		$this->_user 	= $_user;
		$this->_article = $_article;
	}

	public function run(){
		try{
			$this->_setupRequestMethod();
			$this->_setupResource();
			$this->_setId();
			if($this->_resourceName == 'Users'){
				return $this->_json($this->_handleUser());
			}else{
				return $this->_json($this->_handleArticle());
			}
		}catch(Exception $e){
			$this->_json(['error'=>$e->getMessage()],$e->getCode());
		}

	}

	/**
	 * 初始化请求方法
	 */
	private function _setupRequestMethod(){
		$this->_requestMethod = $_SERVER['REQUEST_METHOD'];
		if(!in_array($this->_requestMethod, $this->_allowRequestMethods)){
			throw new Exception("请求方法不被允许", 405);
		}
	}

	/**
	 * 初始化请求资源
	 */
	private function _setupResource(){
		$path = $_SERVER['PATH_INFO'];
		$params = explode('/',$path);
		$this->_resourceName = $params[1];
		if(!in_array($this->_resourceName, $this->_allowResources)){
			throw new Exception("请求资源不被允许", 405);
		}
		if(!empty($params[2])){
			$this->_id = $params[2];
		}
	}

	/**
	 * 初始化资源标识符
	 */
	private function _setId(){

	}

	/**
	 * 输出json
	 *
	 * @param      array  $array  The array
	 */
	private function _json($array,$code=0){
		if($array === null && $code === 0){
			$code = 204;
		}
		if($array !== null && $code === 0){
			$code = 200;
		}
		header("HTTP/1.1 ".$code."  ".$this->_statusCodes[$code]);
		header("Content-Type=application/json;charset=UTF-8 ");
		if($array !== null){
			echo  json_encode($array,JSON_UNESCAPED_UNICODE);
		}
		exit();
	}

	/**
	 * 请求用户资源
	 *
	 * @throws     Exception  (description)
	 *
	 * @return     <type>     ( description_of_the_return_value )
	 */
	private function _handleUser(){
		if($this->_requestMethod != 'POST'){
			throw new Exception("请求方法不被允许", 405);
		}
		$body = $this->_getBodyParams();
		if(empty($body['username'])){
			throw new Exception("用户名不能为空", 400);
		}
		if(empty($body['password'])){
			throw new Exception("密码不能为空", 400);
		}
		return $this->_user->register($body['username'],$body['password']);
	}

	/**
	 * 请求文章资源
	 */
	private function _handleArticle(){
		switch ($this->_requestMethod){
			case 'POST':
				return $this->_handleArticleCreate();
			case 'PUT':
				return $this->_handleArticleEdit();
			case 'DELETE':
				return $this->_handleArticleDelete();
			case 'GET':
				if(empty($this->_id)){
					return $this->_handleArticleList();
				}else{
					return $this->_handleArticleView();
				}
			default:
				throw new Exception("请求方法不被允许", 405);	
		}
	}

	/**
	 * 创建文章
	 *
	 * @throws     Exception  (description)
	 *
	 * @return     <type>     ( description_of_the_return_value )
	 */
	private function _handleArticleCreate(){
		$body = $this->_getBodyParams();
		if(empty($body['title'])){
			throw new Exception("文章标题不能为空", 400);
		}
		if(empty($body['content'])){
			throw new Exception("文章内容不能为空", 400);
		}
		$user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
		try {
			$article = $this->_article->create($body['title'],$body['content'],$user['id']);
			return $article;
		} catch (Exception $e) {
			if(!in_array($e->getMessage(), [
                   ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY,
                   ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY
				])
			){
				throw new Exception($e->getMessage(), 400);
				
			}
			throw new Exception($e->getMessage(), 500);
		}
	}
	
	/**
	 * 编辑文章
	 *
	 * @throws     Exception  (description)
	 *
	 * @return     <type>     ( description_of_the_return_value )
	 */
	private function _handleArticleEdit(){
		$user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
		try {
			$article = $this->_article->view($this->_id);
			if($article['user_id'] != $user['id']){
				throw new Exception("您无权编辑", 403);
			}
			$body = $this->_getBodyParams();
			$title = empty($body['title']) ? $article['title'] : $body['title'];
			$content = empty($body['content']) ? $article['content'] : $body['content'];
			if($title == $article['title'] && $content == $article['content']){
				return $article;
			}
			return $this->_article->edit($article['id'],$title,$content,$user['id']);
		} catch (Exception $e) {
			if($e->getCode() < 100){
				if($e->getCode() == ErrorCode::ARTICLE_NOT_FOUND){
					throw new Exception($e->getMessage(), 404);
				}else{
					throw new Exception($e->getMessage(), 400);
				}
			}else{
				throw $e;
			}
		}
	}
	
	/**
	 * 删除文章
	 *
	 * @throws     Exception  (description)
	 *
	 * @return     <type>     ( description_of_the_return_value )
	 */
	private function _handleArticleDelete(){
		$user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
		try {
			$article = $this->_article->view($this->_id);
			if($article['user_id'] != $user['id']){
				throw new Exception("您无权操作", 403);
			}
			$this->_article->delete($user['id'],$article['id']);
			return null;
		} catch (Exception $e) {
			if($e->getCode() < 100){
				if($e->getCode() == ErrorCode::ARTICLE_NOT_FOUND){
					throw new Exception($e->getMessage(), 404);
				}else{
					throw new Exception($e->getMessage(), 400);
				}
			}else{
				throw $e;
			}
		}
	}
	
	/**
	 * 获取文章列表
	 *
	 * @throws     Exception  (description)
	 *
	 * @return     <type>     ( description_of_the_return_value )
	 */
	private function _handleArticleList(){
		$user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$size = isset($_GET['size']) ? $_GET['size'] : 10;
		if($size > 100){
			throw new Exception("分页不能大于100", 400);
		}
		return $this->_article->getList($user['id'],$page,$size);
	}

	/**
	 * 查看文章
	 *
	 * @throws     Exception  (description)
	 *
	 * @return     <type>     ( description_of_the_return_value )
	 */
	private function _handleArticleView(){
		try {
			return $this->_article->view($this->_id);
		} catch (Exception $e) {
			if($e->getMessage() == ErrorCode::ARTICLE_NOT_FOUND){
				throw new Exception($e->getMessage(), 404);
			}else{
				throw new Exception($e->getMessage(), 500);
			}
		}
	}

	/**
	 * 用户登录
	 *
	 * @param      <type>  $PHP_AUTH_USER  The php auth user
	 * @param      <type>  $PHP_AUTH_PW    The php auth pw
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	private function _userLogin($PHP_AUTH_USER,$PHP_AUTH_PW){
		try {
			return $this->_user->login($PHP_AUTH_USER,$PHP_AUTH_PW);
		} catch (Exception $e) {
			if(in_array($e->getCode(),[
					ErrorCode::USERNAME_CANNOT_EMPTY,
					ErrorCode::PASSWORD_CANNOT_EMPTY,
					ErrorCode::USERNAME_OR_PASSWORD_INVALID
				])){
				throw new Exception($e->getMessage(), 400);
			}
			throw new Exception($e->getMessage(), 500);
		}
	}

	/**
	 * 获取请求体参数
	 *
	 * @throws     Exception  (description)
	 *
	 * @return     <type>     The body parameters.
	 */
	private function _getBodyParams(){
		$raw = file_get_contents('php://input');
		if(empty($raw)){
			throw new Exception("请求参数错误", 400);
		}
		return json_decode($raw,true);
	}
}

$user = new User($pdo);
$article = new Article($pdo);
$restful = new Restful($user,$article);
$restful->run();