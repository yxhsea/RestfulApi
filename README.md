#### Restful的本质
> 一种软件架构风格

**核心**   

- 面向资源


**解决的问题**

- 降低开发的复杂性
- 提供系统的可伸缩性


**设计概念和准则**

- 网络上的所有事物都可以被抽象为资源
- 每一个资源都由唯一的资源标识,对资源的操作不会改变这些标识
- 所有的操作都是无状态的

#### http协议-url
> http是一个属于应用层的协议,特点是简捷、快速。

**schema://host[:port]/path[?query-string][#anchor]**
- ==scheme==   指定低层使用的的协议(例如：http,https,ftp)
- ==host==  服务器的IP地址或域名
- ==port==  服务器端口,默认为80
- ==path==  访问资源的路径
- ==query-string== 发送给http服务器的数据
- ==anchor== 锚

----

#### http协议-请求
> 组成格式：请求行、消息报头、请求报文

**请求行**
- 格式如下：Method Request-URL HTTP-Version CRLF

**举例**
- GET / HTTP/1.1 CRLF

**请求方法**
- ==GET== 请求获取Requesr-URI所标识的资源
- ==POST== 在Request-URI所标识的资源后附加薪的数据
- ==HEAD== 请求获取由Request-URI所标识的资源的响应消息报头
- ==PUT== 请求服务器存储一个资源,并用Request-URI作为其标识
- ==DELETE== 请求服务器删除Request-URI所标识的资源
- ==OPTIONS== 请求查询服务器的性能,或者查询与资源相关的选项和需求

----

#### http协议-响应
> 组成格式：状态行、消息报头、响应正文

**状态行**
- HTTP-Version Status-Code Reason-Phrase CRLF
- 例如：HTTP /1.1 200 OK

**常用状态码**
- 200 OK    //客户端请求成功
- 400 Bad Request   //客户端请求有语法错误，不能被服务器所理解
- 401 Unauthorized  //服务器收到请求,但是拒绝提供服务
- 404 Not Found //请求资源不存在
- 500 Internal Server Error //服务器发送不可预期的错误
- 503 Server Unavailable //服务器当前不能处理客户端的请求