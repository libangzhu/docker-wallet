-- MySQL dump 10.13  Distrib 5.7.34, for Linux (x86_64)
--
-- Host: localhost    Database: p_coin
-- ------------------------------------------------------
-- Server version	5.7.34

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `app_version`
--

DROP TABLE IF EXISTS `app_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(3) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `version` varchar(17) COLLATE utf8_unicode_ci NOT NULL,
  `version_code` int(11) DEFAULT NULL COMMENT '版本代码',
  `download_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `log` json DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `app_version_version` (`version`) USING BTREE,
  KEY `app_version_type` (`type`) USING BTREE,
  KEY `app_version_status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_version`
--

LOCK TABLES `app_version` WRITE;
/*!40000 ALTER TABLE `app_version` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article`
--

DROP TABLE IF EXISTS `article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '文章标题',
  `source` varchar(50) NOT NULL DEFAULT '' COMMENT '文章来源',
  `logo_pic` varchar(200) NOT NULL DEFAULT '' COMMENT '文章图片',
  `img_url` varchar(200) NOT NULL DEFAULT '',
  `content` text NOT NULL COMMENT '文章内容',
  `top` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 非置顶\n1 置顶',
  `highlight` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 正常\n1 高亮',
  `platform_id` int(11) NOT NULL DEFAULT '1',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `id_UNIQUE` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文章表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article`
--

LOCK TABLES `article` WRITE;
/*!40000 ALTER TABLE `article` DISABLE KEYS */;
/*!40000 ALTER TABLE `article` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill`
--

DROP TABLE IF EXISTS `bill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `txid` varchar(255) NOT NULL DEFAULT '' COMMENT 'hashid',
  `chain` varchar(32) NOT NULL DEFAULT '' COMMENT '主链',
  `coinname` varchar(32) NOT NULL DEFAULT '' COMMENT '币种名称',
  `from` varchar(255) NOT NULL DEFAULT '' COMMENT '3、from（发币人地址）：',
  `to` varchar(255) NOT NULL DEFAULT '' COMMENT '转入地址',
  `value` float NOT NULL DEFAULT '0' COMMENT '交易金额',
  `fee` float NOT NULL DEFAULT '0' COMMENT '矿工费',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '失败：-1，确认中:0,成功：1',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '发币-1或1收币',
  `height` int(11) NOT NULL DEFAULT '0' COMMENT '区块高度',
  `note` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `blocktime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `bill_txid_uindex` (`txid`) USING BTREE,
  KEY `bill_from_index` (`from`) USING BTREE,
  KEY `bill_to_index` (`to`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill`
--

LOCK TABLES `bill` WRITE;
/*!40000 ALTER TABLE `bill` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text COMMENT '反馈内容',
  `connect` varchar(255) DEFAULT NULL COMMENT '联系方式',
  `type` tinyint(4) DEFAULT NULL COMMENT '反馈类型',
  `create_at` datetime DEFAULT NULL COMMENT '反馈时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_user`
--

DROP TABLE IF EXISTS `game_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '用户地址',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='游戏用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_user`
--

LOCK TABLES `game_user` WRITE;
/*!40000 ALTER TABLE `game_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `game_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_user_address`
--

DROP TABLE IF EXISTS `game_user_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_user_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `id_UNIQUE` (`id`) USING BTREE,
  UNIQUE KEY `idx_address` (`address`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_user_address`
--

LOCK TABLES `game_user_address` WRITE;
/*!40000 ALTER TABLE `game_user_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `game_user_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investment`
--

DROP TABLE IF EXISTS `investment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `coin` varchar(16) DEFAULT NULL COMMENT '币种名称',
  `count` decimal(14,6) DEFAULT NULL COMMENT '数量',
  `tid` int(11) DEFAULT NULL COMMENT '类型id',
  `status` int(11) DEFAULT NULL COMMENT '状态:1处理，2未处理',
  `address` text COMMENT '地址',
  `create_at` datetime DEFAULT NULL COMMENT '添加时间',
  `update_at` datetime DEFAULT NULL COMMENT '更改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `investment_id_uindex` (`id`) USING BTREE,
  KEY `investment_investment_type_id_fk` (`tid`) USING BTREE,
  KEY `investment_member_uid_fk` (`uid`) USING BTREE,
  CONSTRAINT `investment_investment_type_id_fk` FOREIGN KEY (`tid`) REFERENCES `investment_type` (`id`),
  CONSTRAINT `investment_member_uid_fk` FOREIGN KEY (`uid`) REFERENCES `member` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='空投记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investment`
--

LOCK TABLES `investment` WRITE;
/*!40000 ALTER TABLE `investment` DISABLE KEYS */;
/*!40000 ALTER TABLE `investment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investment_type`
--

DROP TABLE IF EXISTS `investment_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investment_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `create_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='空投类型表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investment_type`
--

LOCK TABLES `investment_type` WRITE;
/*!40000 ALTER TABLE `investment_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `investment_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `realname` varchar(64) DEFAULT NULL,
  `nickname` varchar(64) DEFAULT NULL,
  `phone` varchar(16) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `capital` decimal(14,6) DEFAULT NULL,
  `register_at` datetime DEFAULT NULL,
  `recommend_id` int(11) DEFAULT NULL,
  `recommend_number` int(11) DEFAULT '0',
  `account_address` text,
  PRIMARY KEY (`uid`) USING BTREE,
  UNIQUE KEY `member_username_uindex` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member`
--

LOCK TABLES `member` WRITE;
/*!40000 ALTER TABLE `member` DISABLE KEYS */;
/*!40000 ALTER TABLE `member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_asset`
--

DROP TABLE IF EXISTS `member_asset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_asset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `public_key` varchar(256) DEFAULT NULL COMMENT '钱包公钥',
  `asset` json DEFAULT NULL COMMENT '资产信息',
  `create_at` datetime DEFAULT NULL,
  `update_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `member_asset_member_uid_fk` (`uid`) USING BTREE,
  CONSTRAINT `member_asset_member_uid_fk` FOREIGN KEY (`uid`) REFERENCES `member` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户资产表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_asset`
--

LOCK TABLES `member_asset` WRITE;
/*!40000 ALTER TABLE `member_asset` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_asset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '快讯标题',
  `content` text NOT NULL COMMENT '快讯内容',
  `top` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 非置顶\n1 置顶',
  `highlight` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 正常\n1 高亮',
  `platform_id` int(11) NOT NULL DEFAULT '1',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `id_UNIQUE` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文章表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notice`
--

DROP TABLE IF EXISTS `notice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公告id',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '公告标题',
  `content` text COMMENT '公告内容',
  `author` varchar(32) NOT NULL DEFAULT '' COMMENT '公告作者',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '公告状态\n0 全部  \n1 发布 \n2 失效',
  `is_top` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 普通\n1 置顶',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1 滚动\n2 弹窗\n',
  `platform_id` int(10) unsigned NOT NULL DEFAULT '0',
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='公告表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notice`
--

LOCK TABLES `notice` WRITE;
/*!40000 ALTER TABLE `notice` DISABLE KEYS */;
/*!40000 ALTER TABLE `notice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallet_chain`
--

DROP TABLE IF EXISTS `wallet_chain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallet_chain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `platform` varchar(32) NOT NULL COMMENT '平台',
  `address` varchar(255) NOT NULL COMMENT '地址',
  `private_key` varchar(255) NOT NULL COMMENT '私钥',
  `exer` varchar(255) NOT NULL DEFAULT '' COMMENT '执行器',
  `fee` decimal(10,3) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet_chain`
--

LOCK TABLES `wallet_chain` WRITE;
/*!40000 ALTER TABLE `wallet_chain` DISABLE KEYS */;
/*!40000 ALTER TABLE `wallet_chain` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-06  6:50:38
