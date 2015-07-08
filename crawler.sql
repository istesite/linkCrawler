/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : crawler

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2015-07-08 10:18:02
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for url_list
-- ----------------------------
DROP TABLE IF EXISTS `url_list`;
CREATE TABLE `url_list` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `url` varchar(2000) DEFAULT NULL,
  `referer_url` varchar(2000) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `http_code` int(11) DEFAULT NULL,
  `indate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

