/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-03-18 23:42:33
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for article
-- ----------------------------
DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `addtime` int(11) DEFAULT NULL,
  `user_id` int(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of article
-- ----------------------------
INSERT INTO `article` VALUES ('2', '文章标题1', '文章内容1', '1489825793', '1');
INSERT INTO `article` VALUES ('3', '文章标题2', '文章内容2', '1489825793', '1');
INSERT INTO `article` VALUES ('4', '文章标题3', '文章内容3', '1489825793', '1');
INSERT INTO `article` VALUES ('5', '文章标题4', '文章内容4', '1489825793', '1');
INSERT INTO `article` VALUES ('6', 'restful', 'restful', '1489846854', '3');
INSERT INTO `article` VALUES ('7', 'restful', 'restful', '1489847137', '3');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('1', 'admin1', '454dcca79c9550d0837129152f96db4d', '0000-00-00 00:00:00');
INSERT INTO `user` VALUES ('2', 'admin2', '4fcea5ce0294f6b31c8a4de0848ab6e4', '0000-00-00 00:00:00');
INSERT INTO `user` VALUES ('3', 'abcd', 'dec0770db3be22236f802aefd0a55298', '0000-00-00 00:00:00');
