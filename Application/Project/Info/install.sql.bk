/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : haizhi

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2016-04-19 14:25:45
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for haizhi_project
-- ----------------------------
DROP TABLE IF EXISTS `haizhi_project`;
CREATE TABLE `haizhi_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `cover_img` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `status` tinyint(11) DEFAULT NULL COMMENT '项目状态： -1：删除  0：审核中  1：已发布',
  `category` int(11) DEFAULT NULL COMMENT '行业类别',
  `shortdesc` varchar(255) DEFAULT NULL COMMENT '项目简述',
  `othercoreteamers` varchar(2000) DEFAULT NULL COMMENT '其他核心成员介绍',
  `proinstruction` varchar(500) DEFAULT NULL COMMENT '产品介绍',
  `stage` varchar(255) DEFAULT NULL COMMENT '项目阶段',
  `business_image` varchar(255) DEFAULT NULL COMMENT '营业执照图片',
  `organizationcode` varchar(255) DEFAULT NULL COMMENT '组织机构代码',
  `yingyecode` varchar(255) DEFAULT NULL COMMENT '营业执照代码',
  `taxcode` varchar(255) DEFAULT NULL COMMENT '税务登记证号',
  `market_research` varchar(500) DEFAULT NULL COMMENT '市场调研',
  `competitive_edge` varchar(500) DEFAULT NULL COMMENT '竞争优势',
  `business_model` varchar(500) DEFAULT NULL COMMENT '商业模式',
  `need_res` varchar(255) DEFAULT NULL COMMENT '所需资源',
  `own_res` varchar(255) DEFAULT NULL COMMENT '已拥有资源',
  `pro_logo` varchar(255) DEFAULT NULL COMMENT '项目图标',
  `video_url` varchar(255) DEFAULT NULL COMMENT '视屏地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;


/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : haizhi

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2016-04-19 14:26:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for haizhi_project_coreteamer
-- ----------------------------
DROP TABLE IF EXISTS `haizhi_project_coreteamer`;
CREATE TABLE `haizhi_project_coreteamer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '真实姓名',
  `position` varchar(255) DEFAULT NULL COMMENT '项目中职位',
  `email` varchar(255) DEFAULT NULL COMMENT '常用邮箱',
  `address` varchar(255) DEFAULT NULL COMMENT '所在地',
  `proid` int(11) DEFAULT NULL,
  `self_instruction` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;


/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : haizhi

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2016-04-19 14:26:19
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for haizhi_project_coreteamer_education
-- ----------------------------
DROP TABLE IF EXISTS `haizhi_project_coreteamer_education`;
CREATE TABLE `haizhi_project_coreteamer_education` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `begin_time` varchar(255) DEFAULT NULL,
  `end_time` varchar(255) DEFAULT NULL,
  `school` varchar(255) DEFAULT NULL,
  `degree` varchar(255) DEFAULT NULL COMMENT '学位',
  `coreteamer_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;


/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : haizhi

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2016-04-19 14:26:30
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for haizhi_project_coreteamer_experience
-- ----------------------------
DROP TABLE IF EXISTS `haizhi_project_coreteamer_experience`;
CREATE TABLE `haizhi_project_coreteamer_experience` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company` varchar(255) DEFAULT NULL,
  `e_position` varchar(255) DEFAULT NULL,
  `coreteamer_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : haizhi

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2016-04-19 14:26:37
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for haizhi_project_event
-- ----------------------------
DROP TABLE IF EXISTS `haizhi_project_event`;
CREATE TABLE `haizhi_project_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` varchar(255) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `proid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;


/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : haizhi

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2016-04-19 14:26:59
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for haizhi_project_growdata
-- ----------------------------
DROP TABLE IF EXISTS `haizhi_project_growdata`;
CREATE TABLE `haizhi_project_growdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `data` varchar(255) DEFAULT NULL,
  `proid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;


/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : haizhi

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2016-04-19 14:27:06
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for haizhi_project_news
-- ----------------------------
DROP TABLE IF EXISTS `haizhi_project_news`;
CREATE TABLE `haizhi_project_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proid` int(11) DEFAULT NULL,
  `new_website` varchar(255) DEFAULT NULL,
  `new_title` varchar(255) DEFAULT NULL,
  `create_time` varchar(255) DEFAULT NULL,
  `application` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;


/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : haizhi

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2016-04-19 14:27:14
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for haizhi_project_producttest
-- ----------------------------
DROP TABLE IF EXISTS `haizhi_project_producttest`;
CREATE TABLE `haizhi_project_producttest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proid` int(11) DEFAULT NULL,
  `weibo` varchar(255) DEFAULT NULL COMMENT '微博',
  `wechat` varchar(255) DEFAULT NULL COMMENT '微信号',
  `website` varchar(255) DEFAULT NULL COMMENT '官网地址',
  `apk` varchar(255) DEFAULT NULL COMMENT '安卓',
  `ios` varchar(255) DEFAULT NULL COMMENT 'ios',
  `account` varchar(255) DEFAULT NULL COMMENT '测试账号',
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;



/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : haizhi

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2016-04-19 14:27:23
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for haizhi_project_teacher
-- ----------------------------
DROP TABLE IF EXISTS `haizhi_project_teacher`;
CREATE TABLE `haizhi_project_teacher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proid` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `introduction` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`,`proid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
