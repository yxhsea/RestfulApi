<?php
//引入错误码类
require_once __DIR__.'/ErrorCode.php';

/**
 * 用户类
 */
class User{
	/**
	 * 数据库连接句柄
	 */
	private $_db;

	/**
	 * 构造方法,数据库连接句柄
	 *
	 * @param      <type>  $_db    The database
	 */
	public function __construct($_db){
		$this->_db = $_db;
	}

	/**
	 * 用户登录
	 *
	 * @param      <type>  $username  The username
	 * @param      <type>  $password  The password
	 */
	public function login($username,$password){
		if(empty($username)){
			throw new Exception("用户名不能为空", ErrorCode::USERNAME_CANNOT_EMPTY);
		}
		if(empty($password)){
			throw new Exception("密码不能为空", ErrorCode::PASSWORD_CANNOT_EMPTY);
		}
		$sql = 'SELECT * FROM `user` WHERE `username` = :username AND `password` = :password';
		$password = $this->_md5($password);
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':username',$username);
		$stmt->bindParam(':password',$password);
		if(!$stmt->execute()){
			throw new Exception("服务器内部错误", ErrorCode::SERVER_INTERNAL_ERROR);
		}
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$user){
			throw new Exception("用户名或密码错误", ErrorCode::USERNAME_OR_PASSWORD_INVALID);
		}
		return $user;
	}

	/**
	 * 用户注册
	 *
	 * @param      <type>  $username  The username
	 * @param      <type>  $password  The password
	 */
	public function register($username,$password){
		if(empty($username)){
			throw new Exception("用户名不能为空", ErrorCode::USERNAME_CANNOT_EMPTY);
		}
		if(empty($password)){
			throw new Exception("密码不能为空", ErrorCode::PASSWORD_CANNOT_EMPTY);
		}
		if($this->_isUsernameExists($username)){
			throw new Exception("用户名已经存在", ErrorCode::USERNAME_EXISTS);
		}
		//写入数据
		$sql = 'INSERT INTO `user`(`username`,`password`,`addtime`) VALUES(:username,:password,:addtime)';
		$addtime = time();
		$password = $this->_md5($password);
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':username',$username);
		$stmt->bindParam(':password',$password);
		$stmt->bindParam(':addtime',$addtime);
		if(!$stmt->execute()){
			throw new Exception("注册失败", ErrorCode::REGISTER_FAIL);
		}
		return [
			'userId'	=> $this->_db->lastInsertId(),
			'username'  => $username,
			'addtime'   => $addtime
		];
	}

	/**
	 * md5加密
	 *
	 * @param      <type>  $string  The string
	 * @param      string  $key     The key
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	private function _md5($string,$key='imooc'){
		return md5($string.$key);
	}

	/**
	 * 检查用户名是否存在
	 *
	 * @param      <type>   $username  The username
	 *
	 * @return     boolean  True if username exists, False otherwise.
	 */
	private function _isUsernameExists($username){
		$exists = false;
		$sql = 'SELECT * FROM `user` WHERE `username`=:username';// ：是一个占位符，为了防止sql注入
		$stmt = $this->_db->prepare($sql);//进行预处理
		$stmt->bindParam(':username',$username);//绑定变量
		$stmt->execute();//执行查询
		$result = $stmt->fetch(PDO::FETCH_ASSOC);//以数组索引的方式返回
		return !empty($result);
	}
}