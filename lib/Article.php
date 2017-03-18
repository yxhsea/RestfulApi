<?php
//引入错误码类
require_once __DIR__."/ErrorCode.php";

/**
 * 文章类
 */
class Article{
	/**
	 * 数据库句柄
	 */
	private $_db;

	/**
	 * 构造函数,数据库连接句柄
	 *
	 * @param      <type>  $_db    The database
	 */
	public function __construct($_db){
		$this->_db = $_db;
	}

	/**
	 * 创建文章
	 *
	 * @param      <type>  $title    The title
	 * @param      <type>  $content  The content
	 * @param      <type>  $userId   The user identifier
	 */
	public function create($title,$content,$userId){
		if(empty($title)){
			throw new Exception("文章标题不能为空", ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
		}
		if(empty($content)){
			throw new Exception("文章内容不能为空", ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY);
		}
		$addtime = time();
		//数据写入
		$sql = 'INSERT INTO `article` (`title`,`content`,`addtime`,`user_id`) VALUES(:title,:content,:addtime,:user_id)';
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':title',$title);
		$stmt->bindparam(':content',$content);
		$stmt->bindparam(':addtime',$addtime);
		$stmt->bindparam(':user_id',$userId);
		if(!$stmt->execute()){
			throw new Exception("发布文章失败", ErrorCode::ARTICLE_CREATE_FAIL);
		}
		return [
			'articleId' => $this->_db->lastInsertId(),
			'title' 	=> $title,
			'content' 	=> $content,
			'addtime' 	=> $addtime,
			'userId' 	=> $userId
		];
	}

	/**
	 * 查看文章
	 *
	 * @param      <type>     $articleId  The article identifier
	 *
	 * @throws     Exception  (description)
	 */
	public function view($articleId){
		if(empty($articleId)){
			throw new Exception("文章ID不能为空", ErrorCode::ARTICLE_ID_CANNOT_EMPTY);
		}
		//查询数据
		$sql = 'SELECT * FROM `article` WHERE `id` = :id';
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':id',$articleId);
		$stmt->execute();
		$article = $stmt->fetch(PDO::FETCH_ASSOC);
		if(empty($article)){
			throw new Exception("文章不存在", ErrorCode::ARTICLE_NOT_FOUND);
		}
		return $article;
	}

	/**
	 * 编辑文章
	 *
	 * @param      <type>  $articleId  The article identifier
	 * @param      <type>  $title      The title
	 * @param      <type>  $content    The content
	 * @param      <type>  $userId     The user identifier
	 */
	public function edit($articleId,$title,$content,$userId){
		$article = $this->view($articleId);
		if($article['user_id'] !== $userId){
			throw new Exception("你无权编辑文章", ErrorCode::PERMISSION_DENIED);
		}
		$title = empty($title) ? $article['title'] : $title;
		$content = empty($content) ? $article['content'] : $content;
		if($title === $article['title'] && $content === $article['content']){
			return $article;
		}
		//更新数据
		$sql = 'UPDATE `article` SET `title` = :title,`content` = :content WHERE `id` = :id';
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':title',$title);
		$stmt->bindParam(':content',$content);
		$stmt->bindParam(':id',$articleId);
		if(!$stmt->execute()){
			throw new Exception("编辑失败", ErrorCode::ARTICLE_EDIT_FAIL);
		}
		return [
			'id'      => $articleId,
			'title'   => $title,
			'content' => $content,
			'addtime' => $article['addtime'],
			'userId'  => $userId
		];
	}

	/**
	 * 删除文章
	 *
	 * @param      <type>  $userId     The user identifier
	 * @param      <type>  $articleId  The article identifier
	 */
	public function delete($userId,$articleId){
		$article = $this->view($articleId);
		if($article['user_id'] !== $userId){
			throw new Exception("您无权操作", ErrorCode::PERMISSION_DENIED);
		}
		//删除文章
		$sql = 'DELETE FROM `article` WHERE `id` = :id AND `user_id` = :user_id';
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':id',$articleId);
		$stmt->bindParam(':user_id',$userId);
		if(!$stmt->execute()){
			throw new Exception("文章删除失败", ErrorCode::ARTICLE_DELETE_FAIL);
		}
		return $stmt->execute();
	}

	/**
	 * 读取文章列表
	 *
	 * @param      <type>   $userId  The user identifier
	 * @param      integer  $page    The page
	 * @param      integer  $size    The size
	 */
	public function getList($userId,$page=1,$size=10){
		if($size > 100){
			throw new Exception("分页大小最大为100", ErrorCode::PAGE_SIZE_TO_BIG);
		}
		//查询数据
		$sql = 'SELECT * FROM `article` WHERE user_id = :user_id LIMIT :limit,:offset';
		$limit = ($page - 1) * $size;
		$limit = $limit < 0 ? 0 : $limit;
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':user_id',$userId);
		$stmt->bindParam(':limit',$limit);
		$stmt->bindParam(':offset',$size);
		if(!$stmt->execute()){
			throw new Exception("Error Processing Request", 1);
		}
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}
}