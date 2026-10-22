-- MySQL dump 10.13  Distrib 5.7.34, for Linux (x86_64)
--
-- Host: localhost    Database: p_manage
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
-- Table structure for table `gli_admin`
--

DROP TABLE IF EXISTS `gli_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_admin` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `bind_uid` int(11) DEFAULT NULL COMMENT '绑定UID',
  `username` char(32) NOT NULL COMMENT '用户名',
  `password` char(60) NOT NULL COMMENT '密码',
  `access_token` varchar(255) DEFAULT NULL,
  `salt` char(32) NOT NULL COMMENT '密码干扰字符',
  `reg_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `reg_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '用户状态 1正常 2禁用',
  `group` varchar(50) NOT NULL DEFAULT 'admin',
  `platform_id` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE,
  UNIQUE KEY `bind_uid` (`bind_uid`) USING BTREE,
  UNIQUE KEY `idx_token` (`access_token`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_admin`
--

LOCK TABLES `gli_admin` WRITE;
/*!40000 ALTER TABLE `gli_admin` DISABLE KEYS */;
INSERT INTO `gli_admin` VALUES (1,NULL,'mydao_wallet','$2y$13$3/nOKvefberYhcSCk5tfr.pUkIBUr2hlYfXBLj4MVH8A8WqA1LEPO','8IwFJF9TPOPOKxNamaqDzKcA-dXyqo2m','kKOQIFcBy6XZdmdJ7UdtHc_AJ_vkBqgs',1528437312,2130706433,1655952450,2130706433,0,1,'administrator',1);
/*!40000 ALTER TABLE `gli_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_admin_log`
--

DROP TABLE IF EXISTS `gli_admin_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户uid',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `controller` varchar(50) NOT NULL COMMENT '控制器',
  `action` varchar(50) NOT NULL COMMENT '动作',
  `querystring` varchar(255) NOT NULL COMMENT '查询字符串',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  `ip` varchar(15) NOT NULL DEFAULT '0.0.0.0' COMMENT 'IP',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='后台日志';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_admin_log`
--

LOCK TABLES `gli_admin_log` WRITE;
/*!40000 ALTER TABLE `gli_admin_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `gli_admin_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_admin_session`
--

DROP TABLE IF EXISTS `gli_admin_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_admin_session` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `session_token` varchar(64) NOT NULL,
  PRIMARY KEY (`session_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_admin_session`
--

LOCK TABLES `gli_admin_session` WRITE;
/*!40000 ALTER TABLE `gli_admin_session` DISABLE KEYS */;
/*!40000 ALTER TABLE `gli_admin_session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_assets_flow`
--

DROP TABLE IF EXISTS `gli_assets_flow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_assets_flow` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '自增，关联wallet的id',
  `bind_order` bigint(20) DEFAULT NULL COMMENT '绑定orderr表id',
  `bind_transfer` bigint(20) DEFAULT NULL COMMENT '绑定transfer表id',
  `bind_cash` bigint(20) DEFAULT NULL COMMENT '绑定fxee_cash_Record表id',
  `bind_coin` bigint(20) DEFAULT NULL COMMENT '绑定fxee_coin_Record表id',
  `flow_id` int(11) NOT NULL COMMENT '每个用户的自增流水id',
  `user_id` bigint(20) NOT NULL COMMENT '区块链的user_id',
  `symbol_id` smallint(6) NOT NULL COMMENT '币id',
  `symbol_name` varchar(50) DEFAULT NULL COMMENT '币名',
  `price` bigint(20) DEFAULT NULL COMMENT '价格【或 兑换比率】',
  `type` tinyint(4) NOT NULL COMMENT '类型【0：买 1：卖 ；其它类型参见程序配置】',
  `amount` bigint(20) NOT NULL COMMENT '数量',
  `addtime` bigint(20) NOT NULL COMMENT '时间',
  `remark` varchar(128) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `addtime` (`addtime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_assets_flow`
--

LOCK TABLES `gli_assets_flow` WRITE;
/*!40000 ALTER TABLE `gli_assets_flow` DISABLE KEYS */;
/*!40000 ALTER TABLE `gli_assets_flow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_assets_wallet`
--

DROP TABLE IF EXISTS `gli_assets_wallet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_assets_wallet` (
  `relation_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL COMMENT 'user_id',
  `symbol_id` smallint(6) NOT NULL,
  `amount` bigint(20) NOT NULL DEFAULT '0',
  UNIQUE KEY `id_symbol` (`relation_id`,`symbol_id`) USING BTREE,
  KEY `relation_id` (`relation_id`) USING BTREE,
  KEY `symbol` (`symbol_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_assets_wallet`
--

LOCK TABLES `gli_assets_wallet` WRITE;
/*!40000 ALTER TABLE `gli_assets_wallet` DISABLE KEYS */;
/*!40000 ALTER TABLE `gli_assets_wallet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_auth_assignment`
--

DROP TABLE IF EXISTS `gli_auth_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_auth_assignment` (
  `item_name` varchar(64) NOT NULL COMMENT '角色名称 role',
  `user_id` varchar(64) NOT NULL COMMENT '用户ID',
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`) USING BTREE,
  CONSTRAINT `fk_auth_item_4494_00` FOREIGN KEY (`item_name`) REFERENCES `gli_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_auth_assignment`
--

LOCK TABLES `gli_auth_assignment` WRITE;
/*!40000 ALTER TABLE `gli_auth_assignment` DISABLE KEYS */;
INSERT INTO `gli_auth_assignment` VALUES ('administrator','1',1524829303),('administrator','11',1534489100);
/*!40000 ALTER TABLE `gli_auth_assignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_auth_item`
--

DROP TABLE IF EXISTS `gli_auth_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_auth_item` (
  `name` varchar(64) NOT NULL COMMENT '角色或权限名称',
  `type` int(11) NOT NULL COMMENT '1角色 2权限',
  `description` text,
  `rule_name` varchar(64) DEFAULT NULL,
  `data` text,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`) USING BTREE,
  KEY `rule_name` (`rule_name`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_auth_item`
--

LOCK TABLES `gli_auth_item` WRITE;
/*!40000 ALTER TABLE `gli_auth_item` DISABLE KEYS */;
INSERT INTO `gli_auth_item` VALUES ('admin-log/index',2,NULL,'admin-log/index',NULL,1584599842,1584599842),('admin/add',2,NULL,'admin/add',NULL,1510801893,1510801893),('admin/auth',2,NULL,'admin/auth',NULL,1510801894,1510801894),('admin/edit',2,NULL,'admin/edit',NULL,1510801893,1510801893),('admin/index',2,NULL,'admin/index',NULL,1584579392,1584579392),('administrator',1,'超级管理员',NULL,NULL,1510801536,1533632838),('air-drop/list',2,NULL,'air-drop/list',NULL,1565579206,1565579206),('AISwallet',1,'AISwallet',NULL,'s:2:\"29\";',1571384078,1571384078),('app/add',2,NULL,'app/add',NULL,1529895099,1529895099),('app/delete',2,NULL,'app/delete',NULL,1529899262,1529899262),('app/index',2,NULL,'app/index',NULL,1529646117,1529646117),('app/list',2,NULL,'app/list',NULL,1529646117,1529646117),('app/upload',2,NULL,'app/upload',NULL,1532397738,1532397738),('applicate-rank/add',2,NULL,'applicate-rank/add',NULL,1555467194,1555467194),('applicate-rank/delete',2,NULL,'applicate-rank/delete',NULL,1555467210,1555467210),('applicate-rank/index',2,NULL,'applicate-rank/index',NULL,1541148409,1541148409),('applicate-rank/search-index',2,NULL,'applicate-rank/search-index',NULL,1555467179,1555467179),('applicate-recommend/add',2,NULL,'applicate-recommend/add',NULL,1555466832,1555466832),('applicate-recommend/delete',2,NULL,'applicate-recommend/delete',NULL,1555466991,1555466991),('applicate-recommend/index',2,NULL,'applicate-recommend/index',NULL,1539776801,1539776801),('applicate-recommend/set-icon',2,NULL,'applicate-recommend/set-icon',NULL,1555467145,1555467145),('application-category/add',2,NULL,'application-category/add',NULL,1564046815,1564046815),('application-category/add-and-edit',2,NULL,'application-category/add-and-edit',NULL,1555466780,1555466780),('application-category/add-banner',2,NULL,'application-category/add-banner',NULL,1555467060,1555467060),('application-category/banner-index',2,NULL,'application-category/banner-index',NULL,1555466812,1555466812),('application-category/delete',2,NULL,'application-category/delete',NULL,1555466794,1555466794),('application-category/delete-banner	',2,NULL,'application-category/delete-banner	',NULL,1555467080,1555467080),('application-category/edit',2,NULL,'application-category/edit',NULL,1564046904,1564046904),('application-category/index',2,NULL,'application-category/index',NULL,1539776707,1539776707),('application-category/list',2,NULL,'application-category/list',NULL,1539776675,1539776675),('application-category/set-enable',2,NULL,'application-category/set-enable',NULL,1555466853,1555466853),('application/add',2,NULL,'application/add',NULL,1555466869,1555466869),('application/add-category',2,NULL,'application/add-category',NULL,1555466938,1555466938),('application/add-image',2,NULL,'application/add-image',NULL,1555467023,1555467023),('application/delete',2,NULL,'application/delete',NULL,1555466903,1555466903),('application/delete-image',2,NULL,'application/delete-image',NULL,1555467038,1555467038),('application/edit',2,NULL,'application/edit',NULL,1555466888,1555466888),('application/image-index',2,NULL,'application/image-index',NULL,1555467096,1555467096),('application/list',2,NULL,'application/list',NULL,1555466763,1555466763),('application/set-enable',2,NULL,'application/set-enable',NULL,1555467006,1555467006),('application/set-sort',2,NULL,'application/set-sort',NULL,1555580174,1555580174),('auth/access',2,NULL,'auth/access',NULL,1510801894,1510801894),('auth/add',2,NULL,'auth/add',NULL,1510801894,1510801894),('auth/auth',2,NULL,'auth/auth',NULL,1510801894,1510801894),('auth/delete',2,NULL,'auth/delete',NULL,1510801893,1510801893),('auth/edit',2,NULL,'auth/edit',NULL,1510801893,1510801893),('auth/index',2,NULL,'auth/index',NULL,1510801894,1510801894),('auth/user',2,NULL,'auth/user',NULL,1510801894,1510801894),('Bavala钱包',1,'Bavala钱包',NULL,'s:2:\"26\";',1566211519,1575458088),('BLH钱包',1,'BLH钱包',NULL,'s:2:\"28\";',1569578583,1569578583),('BNwallet',1,'BNwallet',NULL,'s:2:\"17\";',1558089375,1564403826),('Cicwallet',1,'Cicwallet',NULL,'s:2:\"24\";',1563349247,1563349247),('CK钱包',1,'CK钱包',NULL,'s:2:\"30\";',1572504015,1572504015),('coin-banner/add',2,NULL,'coin-banner/add',NULL,1555467259,1555467259),('coin-banner/delete',2,NULL,'coin-banner/delete',NULL,1555467273,1555467273),('coin-banner/index',2,NULL,'coin-banner/index',NULL,1545296055,1545296055),('coin-platform-banner/index',2,NULL,'coin-platform-banner/index',NULL,1568082946,1568082946),('coin-platform-withhold/index',2,NULL,'coin-platform-withhold/index',NULL,1568082969,1568082969),('coin-publish-rule/del',2,NULL,'coin-publish-rule/del',NULL,1535598598,1535598598),('coin-publish-rule/index',2,NULL,'coin-publish-rule/index',NULL,1535598598,1535598598),('coin-publish-rule/list',2,NULL,'coin-publish-rule/list',NULL,1535598598,1535598598),('coin-publish-rule/set',2,NULL,'coin-publish-rule/set',NULL,1535598598,1535598598),('coin-publish/add',2,NULL,'coin-publish/add',NULL,1535540990,1535540990),('coin-publish/del',2,NULL,'coin-publish/del',NULL,1535544139,1535544139),('coin-publish/index',2,NULL,'coin-publish/index',NULL,1535536323,1535536323),('coin-publish/list',2,NULL,'coin-publish/list',NULL,1535536323,1535536323),('coin-recommend/add',2,NULL,'coin-recommend/add',NULL,1536741064,1536741064),('coin-recommend/del',2,NULL,'coin-recommend/del',NULL,1536741075,1536741075),('coin-recommend/index',2,NULL,'coin-recommend/index',NULL,1536659065,1536659065),('coin-release-check/check',2,NULL,'coin-release-check/check',NULL,1536286271,1536286271),('coin-release-check/check-list',2,NULL,'coin-release-check/check-list',NULL,1584600562,1584600562),('coin-release-check/check-second',2,NULL,'coin-release-check/check-second',NULL,1536235410,1536235410),('coin-release-check/index',2,NULL,'coin-release-check/index',NULL,1536143962,1536143962),('coin-release-check/search-list',2,NULL,'coin-release-check/search-list',NULL,1584600554,1584600554),('coin-release-member/data-update',2,NULL,'coin-release-member/data-update',NULL,1548309913,1548309913),('coin-release-member/implode',2,NULL,'coin-release-member/implode',NULL,1535704231,1535704231),('coin-release-member/list',2,NULL,'coin-release-member/list',NULL,1535699608,1535699608),('coin-release-member/set',2,NULL,'coin-release-member/set',NULL,1535699608,1535699608),('coin-withhold/add-token',2,NULL,'coin-withhold/add-token',NULL,1536751170,1536751170),('coin-withhold/del',2,NULL,'coin-withhold/del',NULL,1536746078,1536746078),('coin-withhold/del-token',2,NULL,'coin-withhold/del-token',NULL,1536805770,1536805770),('coin-withhold/detial',2,NULL,'coin-withhold/detial',NULL,1536746269,1536746269),('coin-withhold/index',2,NULL,'coin-withhold/index',NULL,1536740006,1536740006),('coin-withhold/set',2,NULL,'coin-withhold/set',NULL,1536746018,1536746018),('coin/add',2,NULL,'coin/add',NULL,1527751694,1527751694),('coin/delete',2,NULL,'coin/delete',NULL,1529033008,1529033008),('coin/edit',2,NULL,'coin/edit',NULL,1527761919,1527761919),('coin/get-exchange-list-by-id',2,NULL,'coin/get-exchange-list-by-id',NULL,1529043232,1529043232),('coin/index',2,NULL,'coin/index',NULL,1527731946,1527731946),('coin/package',2,NULL,'coin/package',NULL,1536821558,1536821558),('coin/package-add',2,NULL,'coin/package-add',NULL,1536827578,1536827578),('coin/package-del',2,NULL,'coin/package-del',NULL,1536824157,1536824157),('coin/platform-coin',2,NULL,'coin/platform-coin',NULL,1542357545,1542357545),('coin/upload',2,NULL,'coin/upload',NULL,1527824008,1527824008),('config/add',2,NULL,'config/add',NULL,1510801895,1510801895),('config/del',2,NULL,'config/del',NULL,1510801896,1510801896),('config/develop',2,NULL,'config/develop',NULL,1584579427,1584579427),('config/edit',2,NULL,'config/edit',NULL,1510801896,1510801896),('config/index',2,NULL,'config/index',NULL,1510801893,1510801893),('config/save',2,NULL,'config/save',NULL,1510801895,1510801895),('csla_wallet',1,'csla钱包',NULL,'s:2:\"67\";',1617699067,1617699067),('c_wallet',1,'',NULL,'s:2:\"75\";',1639469307,1639469307),('dag钱包',1,'',NULL,'s:1:\"9\";',1577522112,1577522112),('Daishuwallet',1,'Daishuwallet 钱包',NULL,'s:2:\"21\";',1561446802,1561446802),('dom_wallet',1,'',NULL,'s:2:\"62\";',1612337694,1612337694),('DQZY',1,'企业账户',NULL,NULL,1544610724,1544610724),('GBT钱包',1,'GBT钱包',NULL,'s:2:\"12\";',1558319097,1558319097),('gdy_wallet',1,'',NULL,'s:2:\"73\";',1633773437,1633773437),('ge_wallet',1,'',NULL,'s:2:\"65\";',1615799640,1615799640),('gm_wallet',1,'新敢吗钱包-东软部署',NULL,'s:1:\"8\";',1595312502,1645584574),('gwa_wallet',1,'',NULL,'s:2:\"64\";',1615779300,1615779300),('HD钱包',1,'HD钱包',NULL,'s:2:\"54\";',1605166966,1605166966),('hitoken',1,'',NULL,'s:2:\"45\";',1598259709,1598259709),('HLToken 钱包',1,'HLToken 钱包',NULL,'s:2:\"15\";',1557041099,1557041099),('HYS钱包',1,'HYS钱包',NULL,'s:2:\"31\";',1573612261,1573612261),('image/upload',2,NULL,'image/upload',NULL,1555467349,1555467349),('investment/add-one',2,NULL,'investment/add-one',NULL,1528882440,1528882440),('investment/index',2,NULL,'investment/index',NULL,1528875158,1528875158),('investment/load-file',2,NULL,'investment/load-file',NULL,1530177828,1530177828),('investment/type',2,NULL,'investment/type',NULL,1529477144,1529477144),('investment/type-add',2,NULL,'investment/type-add',NULL,1529478195,1529478195),('investment/type-delete',2,NULL,'investment/type-delete',NULL,1529481499,1529481499),('investment/type-update',2,NULL,'investment/type-update',NULL,1529483439,1529483439),('lele_wallet',1,'',NULL,'s:2:\"53\";',1604485572,1604485572),('ljz_wallet',1,'',NULL,'s:2:\"60\";',1609227098,1609493152),('Mayiwallet',1,'Mayiwallet钱包',NULL,'s:2:\"22\";',1561446880,1561446880),('Mcss钱包',1,'Mcss钱包',NULL,'s:2:\"19\";',1560754931,1560754931),('mc_wallet',1,'',NULL,'s:2:\"61\";',1611046726,1611046726),('member/edit',2,NULL,'member/edit',NULL,1528870498,1528870498),('member/index',2,NULL,'member/index',NULL,1528797002,1528797002),('member/wallet',2,NULL,'member/wallet',NULL,1530755608,1530755608),('menu/add',2,NULL,'menu/add',NULL,1510801895,1510801895),('menu/edit',2,NULL,'menu/edit',NULL,1510801895,1510801895),('menu/flush',2,NULL,'menu/flush',NULL,1513749423,1513749423),('menu/import',2,NULL,'menu/import',NULL,1510801895,1510801895),('menu/index',2,NULL,'menu/index',NULL,1510801894,1510801894),('menu/sort',2,NULL,'menu/sort',NULL,1510801895,1510801895),('menu/tree',2,NULL,'menu/tree',NULL,1510801895,1510801895),('Mimwallet',1,'Mimwallet 钱包',NULL,'s:2:\"20\";',1561446712,1561446712),('miner-fee/add',2,NULL,'miner-fee/add',NULL,1528181758,1528181758),('miner-fee/cost',2,NULL,'miner-fee/cost',NULL,1528180702,1528180702),('miner-fee/del',2,NULL,'miner-fee/del',NULL,1528184072,1528184072),('miner-fee/edit',2,NULL,'miner-fee/edit',NULL,1528184071,1528184071),('MiToken',1,'Mitoken钱包',NULL,'s:2:\"20\";',1568972388,1568972388),('M钱包',1,'M钱包',NULL,'s:2:\"29\";',1569578596,1569578596),('oss/upload',2,NULL,'oss/upload',NULL,1571385590,1571385590),('parallel-chains/index',2,NULL,'parallel-chains/index',NULL,1552460550,1552460550),('platform-coins/list',2,NULL,'platform-coins/list',NULL,1565247109,1565247109),('Poker钱包',1,'Poker钱包',NULL,'s:2:\"38\";',1585186601,1585186601),('pro_wallet',1,'',NULL,'s:2:\"72\";',1631094033,1631094033),('query/format-params',2,NULL,'query/format-params',NULL,1584599478,1584599478),('SBNY',1,'企业管理员',NULL,NULL,1552894737,1552894737),('SEC钱包',1,'SEC钱包',NULL,'s:2:\"33\";',1575458110,1575458110),('sft',1,'sft',NULL,'s:2:\"13\";',1564038315,1564038315),('site/index',2,NULL,'site/index',NULL,1510801583,1510801583),('source/article',2,NULL,'source/article',NULL,1528075562,1528075562),('source/article-add',2,NULL,'source/article-add',NULL,1528078547,1528078547),('source/article-del',2,NULL,'source/article-del',NULL,1528093542,1528093542),('source/article-edit',2,NULL,'source/article-edit',NULL,1528093542,1528093542),('source/feedback',2,NULL,'source/feedback',NULL,1528359374,1528359374),('source/index',2,NULL,'source/index',NULL,1595577698,1595577698),('source/notice',2,NULL,'source/notice',NULL,1528095270,1528095270),('source/notice-add',2,NULL,'source/notice-add',NULL,1528095270,1528095270),('source/notice-del',2,NULL,'source/notice-del',NULL,1528095271,1528095271),('source/notice-edit',2,NULL,'source/notice-edit',NULL,1528095270,1528095270),('sto',1,'STO钱包',NULL,NULL,1543572234,1543572234),('Summit钱包',1,'Summit钱包',NULL,'s:2:\"34\";',1575274599,1575274599),('Tixwallet',1,'Tixwallet',NULL,'s:0:\"\";',1563975426,1563975426),('TS钱包',1,'TS钱包',NULL,'s:2:\"10\";',1556627024,1556627024),('wallet/list',2,NULL,'wallet/list',NULL,1568083038,1568083038),('wallet/set-passwd',2,NULL,'wallet/set-passwd',NULL,1545113506,1545113506),('WT',1,'管理',NULL,NULL,1547105405,1547105405),('W钱包',1,'W钱包',NULL,NULL,1533713055,1533713055),('xyc_wallet',1,'',NULL,'s:2:\"71\";',1637288755,1637288755),('Zeus钱包',1,'Zeus钱包',NULL,'s:2:\"36\";',1578278967,1578278967),('ZYChain钱包',1,'ZYChain钱包',NULL,'s:2:\"52\";',1604892010,1605079056),('上链通钱包',1,'上链通',NULL,'s:2:\"35\";',1577156011,1577156011),('企业1',1,'企业1 用户名qiye 密码111111',NULL,NULL,1542614406,1542614406),('佰特链',1,'',NULL,'s:2:\"47\";',1598928814,1598928814),('倍多链',1,'',NULL,'s:2:\"44\";',1596457328,1596457328),('国盾',1,'国顿用户',NULL,NULL,1533713055,1533713055),('国盾tester',1,'测试',NULL,NULL,1541406405,1541406405),('太庆能源',1,'',NULL,'s:2:\"49\";',1600941515,1600941515),('巴马水钱包',1,'巴马水钱包',NULL,'s:2:\"58\";',1606289152,1606289152),('星元钱包',1,'星元钱包',NULL,NULL,1544779612,1544779643),('松勤',1,'',NULL,'s:2:\"43\";',1595240391,1595240391),('测试企业',1,'',NULL,NULL,1540450295,1540450295),('电池银行',1,'电池银行',NULL,'s:2:\"51\";',1601363677,1601363677),('贵州车秘',1,'贵州车秘',NULL,'s:2:\"41\";',1595312408,1595312408),('链销',1,'',NULL,'s:2:\"48\";',1599117117,1599117117),('青鸟',1,'',NULL,'s:2:\"50\";',1602238420,1602238420);
/*!40000 ALTER TABLE `gli_auth_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_auth_item_child`
--

DROP TABLE IF EXISTS `gli_auth_item_child`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_auth_item_child` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`) USING BTREE,
  KEY `child` (`child`) USING BTREE,
  CONSTRAINT `fk_auth_item_4718_00` FOREIGN KEY (`parent`) REFERENCES `gli_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_auth_item_4718_01` FOREIGN KEY (`child`) REFERENCES `gli_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_auth_item_child`
--

LOCK TABLES `gli_auth_item_child` WRITE;
/*!40000 ALTER TABLE `gli_auth_item_child` DISABLE KEYS */;
/*!40000 ALTER TABLE `gli_auth_item_child` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_auth_rule`
--

DROP TABLE IF EXISTS `gli_auth_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_auth_rule` (
  `name` varchar(64) NOT NULL,
  `data` text COMMENT 'serialize后的rule对象',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_auth_rule`
--

LOCK TABLES `gli_auth_rule` WRITE;
/*!40000 ALTER TABLE `gli_auth_rule` DISABLE KEYS */;
INSERT INTO `gli_auth_rule` VALUES ('admin-log/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:15:\"admin-log/index\";s:9:\"createdAt\";i:1584599842;s:9:\"updatedAt\";i:1584599842;}',1584599842,1584599842),('admin/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:9:\"admin/add\";s:9:\"createdAt\";i:1510801893;s:9:\"updatedAt\";i:1510801893;}',1510801893,1510801893),('admin/auth','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:10:\"admin/auth\";s:9:\"createdAt\";i:1510801894;s:9:\"updatedAt\";i:1510801894;}',1510801894,1510801894),('admin/edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:10:\"admin/edit\";s:9:\"createdAt\";i:1510801893;s:9:\"updatedAt\";i:1510801893;}',1510801893,1510801893),('admin/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:11:\"admin/index\";s:9:\"createdAt\";i:1584579392;s:9:\"updatedAt\";i:1584579392;}',1584579392,1584579392),('admin/nav','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:9:\"admin/nav\";s:9:\"createdAt\";i:1510801891;s:9:\"updatedAt\";i:1510801891;}',1510801891,1510801891),('air-drop/list','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:13:\"air-drop/list\";s:9:\"createdAt\";i:1565579206;s:9:\"updatedAt\";i:1565579206;}',1565579206,1565579206),('app/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:7:\"app/add\";s:9:\"createdAt\";i:1529895099;s:9:\"updatedAt\";i:1529895099;}',1529895099,1529895099),('app/delete','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:10:\"app/delete\";s:9:\"createdAt\";i:1529899262;s:9:\"updatedAt\";i:1529899262;}',1529899262,1529899262),('app/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:9:\"app/index\";s:9:\"createdAt\";i:1529646116;s:9:\"updatedAt\";i:1529646116;}',1529646116,1529646116),('app/list','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:8:\"app/list\";s:9:\"createdAt\";i:1529646117;s:9:\"updatedAt\";i:1529646117;}',1529646117,1529646117),('app/upload','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:10:\"app/upload\";s:9:\"createdAt\";i:1532397738;s:9:\"updatedAt\";i:1532397738;}',1532397738,1532397738),('applicate-rank/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:18:\"applicate-rank/add\";s:9:\"createdAt\";i:1555467194;s:9:\"updatedAt\";i:1555467194;}',1555467194,1555467194),('applicate-rank/delete','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:21:\"applicate-rank/delete\";s:9:\"createdAt\";i:1555467210;s:9:\"updatedAt\";i:1555467210;}',1555467210,1555467210),('applicate-rank/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:20:\"applicate-rank/index\";s:9:\"createdAt\";i:1541148409;s:9:\"updatedAt\";i:1541148409;}',1541148409,1541148409),('applicate-rank/search-index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:27:\"applicate-rank/search-index\";s:9:\"createdAt\";i:1555467179;s:9:\"updatedAt\";i:1555467179;}',1555467179,1555467179),('applicate-recommend/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:23:\"applicate-recommend/add\";s:9:\"createdAt\";i:1555466832;s:9:\"updatedAt\";i:1555466832;}',1555466832,1555466832),('applicate-recommend/delete','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:26:\"applicate-recommend/delete\";s:9:\"createdAt\";i:1555466991;s:9:\"updatedAt\";i:1555466991;}',1555466991,1555466991),('applicate-recommend/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:25:\"applicate-recommend/index\";s:9:\"createdAt\";i:1539776801;s:9:\"updatedAt\";i:1539776801;}',1539776801,1539776801),('applicate-recommend/set-icon','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:28:\"applicate-recommend/set-icon\";s:9:\"createdAt\";i:1555467145;s:9:\"updatedAt\";i:1555467145;}',1555467145,1555467145),('application-category/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:24:\"application-category/add\";s:9:\"createdAt\";i:1564046815;s:9:\"updatedAt\";i:1564046815;}',1564046815,1564046815),('application-category/add-and-edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:33:\"application-category/add-and-edit\";s:9:\"createdAt\";i:1555466780;s:9:\"updatedAt\";i:1555466780;}',1555466780,1555466780),('application-category/add-banner','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:31:\"application-category/add-banner\";s:9:\"createdAt\";i:1555467060;s:9:\"updatedAt\";i:1555467060;}',1555467060,1555467060),('application-category/banner-index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:33:\"application-category/banner-index\";s:9:\"createdAt\";i:1555466812;s:9:\"updatedAt\";i:1555466812;}',1555466812,1555466812),('application-category/delete','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:27:\"application-category/delete\";s:9:\"createdAt\";i:1555466794;s:9:\"updatedAt\";i:1555466794;}',1555466794,1555466794),('application-category/delete-banner	','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:35:\"application-category/delete-banner	\";s:9:\"createdAt\";i:1555467080;s:9:\"updatedAt\";i:1555467080;}',1555467080,1555467080),('application-category/edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:25:\"application-category/edit\";s:9:\"createdAt\";i:1564046904;s:9:\"updatedAt\";i:1564046904;}',1564046904,1564046904),('application-category/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:26:\"application-category/index\";s:9:\"createdAt\";i:1539776707;s:9:\"updatedAt\";i:1539776707;}',1539776707,1539776707),('application-category/list','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:25:\"application-category/list\";s:9:\"createdAt\";i:1539776675;s:9:\"updatedAt\";i:1539776675;}',1539776675,1539776675),('application-category/set-enable','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:31:\"application-category/set-enable\";s:9:\"createdAt\";i:1555466853;s:9:\"updatedAt\";i:1555466853;}',1555466853,1555466853),('application/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:15:\"application/add\";s:9:\"createdAt\";i:1555466869;s:9:\"updatedAt\";i:1555466869;}',1555466869,1555466869),('application/add-category','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:24:\"application/add-category\";s:9:\"createdAt\";i:1555466938;s:9:\"updatedAt\";i:1555466938;}',1555466938,1555466938),('application/add-image','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:21:\"application/add-image\";s:9:\"createdAt\";i:1555467023;s:9:\"updatedAt\";i:1555467023;}',1555467023,1555467023),('application/delete','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:18:\"application/delete\";s:9:\"createdAt\";i:1555466903;s:9:\"updatedAt\";i:1555466903;}',1555466903,1555466903),('application/delete-image','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:24:\"application/delete-image\";s:9:\"createdAt\";i:1555467038;s:9:\"updatedAt\";i:1555467038;}',1555467038,1555467038),('application/edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:16:\"application/edit\";s:9:\"createdAt\";i:1555466888;s:9:\"updatedAt\";i:1555466888;}',1555466888,1555466888),('application/image-index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:23:\"application/image-index\";s:9:\"createdAt\";i:1555467096;s:9:\"updatedAt\";i:1555467096;}',1555467096,1555467096),('application/list','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:16:\"application/list\";s:9:\"createdAt\";i:1555466763;s:9:\"updatedAt\";i:1555466763;}',1555466763,1555466763),('application/set-enable','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:22:\"application/set-enable\";s:9:\"createdAt\";i:1555467006;s:9:\"updatedAt\";i:1555467006;}',1555467006,1555467006),('application/set-sort','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:20:\"application/set-sort\";s:9:\"createdAt\";i:1555580174;s:9:\"updatedAt\";i:1555580174;}',1555580174,1555580174),('article/del','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:11:\"article/del\";s:9:\"createdAt\";i:1528093423;s:9:\"updatedAt\";i:1528093423;}',1528093423,1528093423),('article/edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:12:\"article/edit\";s:9:\"createdAt\";i:1528093423;s:9:\"updatedAt\";i:1528093423;}',1528093423,1528093423),('auth/access','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:11:\"auth/access\";s:9:\"createdAt\";i:1510801894;s:9:\"updatedAt\";i:1510801894;}',1510801894,1510801894),('auth/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:8:\"auth/add\";s:9:\"createdAt\";i:1510801894;s:9:\"updatedAt\";i:1510801894;}',1510801894,1510801894),('auth/auth','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:9:\"auth/auth\";s:9:\"createdAt\";i:1510801894;s:9:\"updatedAt\";i:1510801894;}',1510801894,1510801894),('auth/delete','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:11:\"auth/delete\";s:9:\"createdAt\";i:1510801893;s:9:\"updatedAt\";i:1510801893;}',1510801893,1510801893),('auth/edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:9:\"auth/edit\";s:9:\"createdAt\";i:1510801893;s:9:\"updatedAt\";i:1510801893;}',1510801893,1510801893),('auth/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:10:\"auth/index\";s:9:\"createdAt\";i:1510801894;s:9:\"updatedAt\";i:1510801894;}',1510801894,1510801894),('auth/user','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:9:\"auth/user\";s:9:\"createdAt\";i:1510801894;s:9:\"updatedAt\";i:1510801894;}',1510801894,1510801894),('coin-banner/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:15:\"coin-banner/add\";s:9:\"createdAt\";i:1555467259;s:9:\"updatedAt\";i:1555467259;}',1555467259,1555467259),('coin-banner/delete','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:18:\"coin-banner/delete\";s:9:\"createdAt\";i:1555467273;s:9:\"updatedAt\";i:1555467273;}',1555467273,1555467273),('coin-banner/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:17:\"coin-banner/index\";s:9:\"createdAt\";i:1545296055;s:9:\"updatedAt\";i:1545296055;}',1545296055,1545296055),('coin-platform-banner/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:26:\"coin-platform-banner/index\";s:9:\"createdAt\";i:1568082945;s:9:\"updatedAt\";i:1568082945;}',1568082945,1568082945),('coin-platform-withhold/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:28:\"coin-platform-withhold/index\";s:9:\"createdAt\";i:1568082969;s:9:\"updatedAt\";i:1568082969;}',1568082969,1568082969),('coin-publish-rule/del','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:21:\"coin-publish-rule/del\";s:9:\"createdAt\";i:1535598598;s:9:\"updatedAt\";i:1535598598;}',1535598598,1535598598),('coin-publish-rule/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:23:\"coin-publish-rule/index\";s:9:\"createdAt\";i:1535598598;s:9:\"updatedAt\";i:1535598598;}',1535598598,1535598598),('coin-publish-rule/list','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:22:\"coin-publish-rule/list\";s:9:\"createdAt\";i:1535598598;s:9:\"updatedAt\";i:1535598598;}',1535598598,1535598598),('coin-publish-rule/set','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:21:\"coin-publish-rule/set\";s:9:\"createdAt\";i:1535598598;s:9:\"updatedAt\";i:1535598598;}',1535598598,1535598598),('coin-publish/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:16:\"coin-publish/add\";s:9:\"createdAt\";i:1535540990;s:9:\"updatedAt\";i:1535540990;}',1535540990,1535540990),('coin-publish/del','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:16:\"coin-publish/del\";s:9:\"createdAt\";i:1535544139;s:9:\"updatedAt\";i:1535544139;}',1535544139,1535544139),('coin-publish/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:18:\"coin-publish/index\";s:9:\"createdAt\";i:1535536323;s:9:\"updatedAt\";i:1535536323;}',1535536323,1535536323),('coin-publish/list','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:17:\"coin-publish/list\";s:9:\"createdAt\";i:1535536323;s:9:\"updatedAt\";i:1535536323;}',1535536323,1535536323),('coin-recommend/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:18:\"coin-recommend/add\";s:9:\"createdAt\";i:1536741064;s:9:\"updatedAt\";i:1536741064;}',1536741064,1536741064),('coin-recommend/del','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:18:\"coin-recommend/del\";s:9:\"createdAt\";i:1536741075;s:9:\"updatedAt\";i:1536741075;}',1536741075,1536741075),('coin-recommend/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:20:\"coin-recommend/index\";s:9:\"createdAt\";i:1536659065;s:9:\"updatedAt\";i:1536659065;}',1536659065,1536659065),('coin-release-check/check','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:24:\"coin-release-check/check\";s:9:\"createdAt\";i:1536286271;s:9:\"updatedAt\";i:1536286271;}',1536286271,1536286271),('coin-release-check/check-list','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:29:\"coin-release-check/check-list\";s:9:\"createdAt\";i:1584600562;s:9:\"updatedAt\";i:1584600562;}',1584600562,1584600562),('coin-release-check/check-second','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:31:\"coin-release-check/check-second\";s:9:\"createdAt\";i:1536235410;s:9:\"updatedAt\";i:1536235410;}',1536235410,1536235410),('coin-release-check/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:24:\"coin-release-check/index\";s:9:\"createdAt\";i:1536143962;s:9:\"updatedAt\";i:1536143962;}',1536143962,1536143962),('coin-release-check/search-list','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:30:\"coin-release-check/search-list\";s:9:\"createdAt\";i:1584600554;s:9:\"updatedAt\";i:1584600554;}',1584600554,1584600554),('coin-release-member','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:19:\"coin-release-member\";s:9:\"createdAt\";i:1535697922;s:9:\"updatedAt\";i:1535697922;}',1535697922,1535697922),('coin-release-member/data-update','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:31:\"coin-release-member/data-update\";s:9:\"createdAt\";i:1548309913;s:9:\"updatedAt\";i:1548309913;}',1548309913,1548309913),('coin-release-member/implode','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:27:\"coin-release-member/implode\";s:9:\"createdAt\";i:1535704231;s:9:\"updatedAt\";i:1535704231;}',1535704231,1535704231),('coin-release-member/list','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:24:\"coin-release-member/list\";s:9:\"createdAt\";i:1535699608;s:9:\"updatedAt\";i:1535699608;}',1535699608,1535699608),('coin-release-member/set','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:23:\"coin-release-member/set\";s:9:\"createdAt\";i:1535699608;s:9:\"updatedAt\";i:1535699608;}',1535699608,1535699608),('coin-withhold/add-token','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:23:\"coin-withhold/add-token\";s:9:\"createdAt\";i:1536751170;s:9:\"updatedAt\";i:1536751170;}',1536751170,1536751170),('coin-withhold/del','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:17:\"coin-withhold/del\";s:9:\"createdAt\";i:1536746078;s:9:\"updatedAt\";i:1536746078;}',1536746078,1536746078),('coin-withhold/del-token','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:23:\"coin-withhold/del-token\";s:9:\"createdAt\";i:1536805770;s:9:\"updatedAt\";i:1536805770;}',1536805770,1536805770),('coin-withhold/detial','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:20:\"coin-withhold/detial\";s:9:\"createdAt\";i:1536746269;s:9:\"updatedAt\";i:1536746269;}',1536746269,1536746269),('coin-withhold/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:19:\"coin-withhold/index\";s:9:\"createdAt\";i:1536740006;s:9:\"updatedAt\";i:1536740006;}',1536740006,1536740006),('coin-withhold/set','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:17:\"coin-withhold/set\";s:9:\"createdAt\";i:1536746018;s:9:\"updatedAt\";i:1536746018;}',1536746018,1536746018),('coin/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:8:\"coin/add\";s:9:\"createdAt\";i:1527751694;s:9:\"updatedAt\";i:1527751694;}',1527751694,1527751694),('coin/cost','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:9:\"coin/cost\";s:9:\"createdAt\";i:1528168396;s:9:\"updatedAt\";i:1528168396;}',1528168396,1528168396),('coin/delete','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:11:\"coin/delete\";s:9:\"createdAt\";i:1529033008;s:9:\"updatedAt\";i:1529033008;}',1529033008,1529033008),('coin/edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:9:\"coin/edit\";s:9:\"createdAt\";i:1527761919;s:9:\"updatedAt\";i:1527761919;}',1527761919,1527761919),('coin/get-exchange-list-by-id','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:28:\"coin/get-exchange-list-by-id\";s:9:\"createdAt\";i:1529043232;s:9:\"updatedAt\";i:1529043232;}',1529043232,1529043232),('coin/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:10:\"coin/index\";s:9:\"createdAt\";i:1527731946;s:9:\"updatedAt\";i:1527731946;}',1527731946,1527731946),('coin/package','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:12:\"coin/package\";s:9:\"createdAt\";i:1536821558;s:9:\"updatedAt\";i:1536821558;}',1536821558,1536821558),('coin/package-add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:16:\"coin/package-add\";s:9:\"createdAt\";i:1536827578;s:9:\"updatedAt\";i:1536827578;}',1536827578,1536827578),('coin/package-del','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:16:\"coin/package-del\";s:9:\"createdAt\";i:1536824157;s:9:\"updatedAt\";i:1536824157;}',1536824157,1536824157),('coin/platform-coin','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:18:\"coin/platform-coin\";s:9:\"createdAt\";i:1542357545;s:9:\"updatedAt\";i:1542357545;}',1542357545,1542357545),('coin/upload','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:11:\"coin/upload\";s:9:\"createdAt\";i:1527824008;s:9:\"updatedAt\";i:1527824008;}',1527824008,1527824008),('config/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:10:\"config/add\";s:9:\"createdAt\";i:1510801895;s:9:\"updatedAt\";i:1510801895;}',1510801895,1510801895),('config/del','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:10:\"config/del\";s:9:\"createdAt\";i:1510801896;s:9:\"updatedAt\";i:1510801896;}',1510801896,1510801896),('config/develop','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:14:\"config/develop\";s:9:\"createdAt\";i:1584579427;s:9:\"updatedAt\";i:1584579427;}',1584579427,1584579427),('config/edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:11:\"config/edit\";s:9:\"createdAt\";i:1510801895;s:9:\"updatedAt\";i:1510801895;}',1510801895,1510801895),('config/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:12:\"config/index\";s:9:\"createdAt\";i:1510801893;s:9:\"updatedAt\";i:1510801893;}',1510801893,1510801893),('config/save','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:11:\"config/save\";s:9:\"createdAt\";i:1510801895;s:9:\"updatedAt\";i:1510801895;}',1510801895,1510801895),('image/upload','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:12:\"image/upload\";s:9:\"createdAt\";i:1555467349;s:9:\"updatedAt\";i:1555467349;}',1555467349,1555467349),('investment/add-one','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:18:\"investment/add-one\";s:9:\"createdAt\";i:1528882439;s:9:\"updatedAt\";i:1528882439;}',1528882439,1528882439),('investment/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:16:\"investment/index\";s:9:\"createdAt\";i:1528875158;s:9:\"updatedAt\";i:1528875158;}',1528875158,1528875158),('investment/load-file','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:20:\"investment/load-file\";s:9:\"createdAt\";i:1530177828;s:9:\"updatedAt\";i:1530177828;}',1530177828,1530177828),('investment/type','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:15:\"investment/type\";s:9:\"createdAt\";i:1529477144;s:9:\"updatedAt\";i:1529477144;}',1529477144,1529477144),('investment/type-add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:19:\"investment/type-add\";s:9:\"createdAt\";i:1529478195;s:9:\"updatedAt\";i:1529478195;}',1529478195,1529478195),('investment/type-delete','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:22:\"investment/type-delete\";s:9:\"createdAt\";i:1529481499;s:9:\"updatedAt\";i:1529481499;}',1529481499,1529481499),('investment/type-update','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:22:\"investment/type-update\";s:9:\"createdAt\";i:1529483439;s:9:\"updatedAt\";i:1529483439;}',1529483439,1529483439),('member/edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:11:\"member/edit\";s:9:\"createdAt\";i:1528870498;s:9:\"updatedAt\";i:1528870498;}',1528870498,1528870498),('member/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:12:\"member/index\";s:9:\"createdAt\";i:1528797002;s:9:\"updatedAt\";i:1528797002;}',1528797002,1528797002),('member/wallet','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:13:\"member/wallet\";s:9:\"createdAt\";i:1530755608;s:9:\"updatedAt\";i:1530755608;}',1530755608,1530755608),('menu/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:8:\"menu/add\";s:9:\"createdAt\";i:1510801895;s:9:\"updatedAt\";i:1510801895;}',1510801895,1510801895),('menu/edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:9:\"menu/edit\";s:9:\"createdAt\";i:1510801895;s:9:\"updatedAt\";i:1510801895;}',1510801895,1510801895),('menu/flush','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:10:\"menu/flush\";s:9:\"createdAt\";i:1513749423;s:9:\"updatedAt\";i:1513749423;}',1513749423,1513749423),('menu/import','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:11:\"menu/import\";s:9:\"createdAt\";i:1510801895;s:9:\"updatedAt\";i:1510801895;}',1510801895,1510801895),('menu/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:10:\"menu/index\";s:9:\"createdAt\";i:1510801894;s:9:\"updatedAt\";i:1510801894;}',1510801894,1510801894),('menu/sort','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:9:\"menu/sort\";s:9:\"createdAt\";i:1510801895;s:9:\"updatedAt\";i:1510801895;}',1510801895,1510801895),('menu/tree','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:9:\"menu/tree\";s:9:\"createdAt\";i:1510801894;s:9:\"updatedAt\";i:1510801894;}',1510801894,1510801894),('miner-fee/add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:13:\"miner-fee/add\";s:9:\"createdAt\";i:1528181758;s:9:\"updatedAt\";i:1528181758;}',1528181758,1528181758),('miner-fee/cost','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:14:\"miner-fee/cost\";s:9:\"createdAt\";i:1528180702;s:9:\"updatedAt\";i:1528180702;}',1528180702,1528180702),('miner-fee/del','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:13:\"miner-fee/del\";s:9:\"createdAt\";i:1528184072;s:9:\"updatedAt\";i:1528184072;}',1528184072,1528184072),('miner-fee/edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:14:\"miner-fee/edit\";s:9:\"createdAt\";i:1528184071;s:9:\"updatedAt\";i:1528184071;}',1528184071,1528184071),('oss/upload','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:10:\"oss/upload\";s:9:\"createdAt\";i:1571385590;s:9:\"updatedAt\";i:1571385590;}',1571385590,1571385590),('parallel-chains/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:21:\"parallel-chains/index\";s:9:\"createdAt\";i:1552460550;s:9:\"updatedAt\";i:1552460550;}',1552460550,1552460550),('platform-coins/list','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:19:\"platform-coins/list\";s:9:\"createdAt\";i:1565247109;s:9:\"updatedAt\";i:1565247109;}',1565247109,1565247109),('query/format-params','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:19:\"query/format-params\";s:9:\"createdAt\";i:1584599478;s:9:\"updatedAt\";i:1584599478;}',1584599478,1584599478),('site/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:10:\"site/index\";s:9:\"createdAt\";i:1510801583;s:9:\"updatedAt\";i:1510801583;}',1510801583,1510801583),('source/article','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:14:\"source/article\";s:9:\"createdAt\";i:1528075562;s:9:\"updatedAt\";i:1528075562;}',1528075562,1528075562),('source/article-add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:18:\"source/article-add\";s:9:\"createdAt\";i:1528078547;s:9:\"updatedAt\";i:1528078547;}',1528078547,1528078547),('source/article-del','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:18:\"source/article-del\";s:9:\"createdAt\";i:1528093542;s:9:\"updatedAt\";i:1528093542;}',1528093542,1528093542),('source/article-edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:19:\"source/article-edit\";s:9:\"createdAt\";i:1528093542;s:9:\"updatedAt\";i:1528093542;}',1528093542,1528093542),('source/feedback','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:15:\"source/feedback\";s:9:\"createdAt\";i:1528359374;s:9:\"updatedAt\";i:1528359374;}',1528359374,1528359374),('source/index','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:12:\"source/index\";s:9:\"createdAt\";i:1595577698;s:9:\"updatedAt\";i:1595577698;}',1595577698,1595577698),('source/notice','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:13:\"source/notice\";s:9:\"createdAt\";i:1528095270;s:9:\"updatedAt\";i:1528095270;}',1528095270,1528095270),('source/notice-add','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:17:\"source/notice-add\";s:9:\"createdAt\";i:1528095270;s:9:\"updatedAt\";i:1528095270;}',1528095270,1528095270),('source/notice-del','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:17:\"source/notice-del\";s:9:\"createdAt\";i:1528095271;s:9:\"updatedAt\";i:1528095271;}',1528095271,1528095271),('source/notice-edit','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:18:\"source/notice-edit\";s:9:\"createdAt\";i:1528095270;s:9:\"updatedAt\";i:1528095270;}',1528095270,1528095270),('wallet/list','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:11:\"wallet/list\";s:9:\"createdAt\";i:1568083038;s:9:\"updatedAt\";i:1568083038;}',1568083038,1568083038),('wallet/set-passwd','O:21:\"common\\core\\rbac\\Rule\":3:{s:4:\"name\";s:17:\"wallet/set-passwd\";s:9:\"createdAt\";i:1545113506;s:9:\"updatedAt\";i:1545113506;}',1545113506,1545113506);
/*!40000 ALTER TABLE `gli_auth_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_cache`
--

DROP TABLE IF EXISTS `gli_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_cache` (
  `key` varchar(64) CHARACTER SET utf8mb4 NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `info` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`key`) USING BTREE,
  UNIQUE KEY `key` (`key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_cache`
--

LOCK TABLES `gli_cache` WRITE;
/*!40000 ALTER TABLE `gli_cache` DISABLE KEYS */;
INSERT INTO `gli_cache` VALUES ('AssetsFlow_LastTime','1511248467','更新于:2017-11-21 19:39:05'),('weChat_tb_history','0','更新于:2018-05-16 16:32:02'),('weChat_tb_last','0','更新于:2018-05-16 16:32:02'),('weChat_tb_max','149866','更新于:2018-05-17 00:29:35'),('weChat_tb_min','0','更新于:2018-05-16 16:32:02'),('weChat_tb_open','1',NULL),('weChat_tk_history','0','更新于:2018-05-17 00:32:10'),('weChat_tk_max','0','更新于:2018-05-17 00:32:10'),('weChat_tk_min','0','更新于:2018-05-17 00:32:10'),('weChat_tk_open','1',NULL);
/*!40000 ALTER TABLE `gli_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_config`
--

DROP TABLE IF EXISTS `gli_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '配置名称',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '配置说明',
  `group` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置分组',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置类型',
  `value` text COMMENT '配置值',
  `extra` varchar(255) NOT NULL DEFAULT '' COMMENT '配置值',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '配置说明',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sort` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_name` (`name`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `group` (`group`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_config`
--

LOCK TABLES `gli_config` WRITE;
/*!40000 ALTER TABLE `gli_config` DISABLE KEYS */;
INSERT INTO `gli_config` VALUES (1,'HOME_TITLE','管理后台',1,1,'管理后台','','后台标题',1508774400,1508774400,0,1),(2,'STYLE_COLOR','后台色系',1,4,'blue_color','default_color:默认\r\nblue_color:蓝色','后台颜色风格',1508774400,1508774400,2,1);
/*!40000 ALTER TABLE `gli_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_currency`
--

DROP TABLE IF EXISTS `gli_currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_currency` (
  `id` smallint(6) NOT NULL,
  `name` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_currency`
--

LOCK TABLES `gli_currency` WRITE;
/*!40000 ALTER TABLE `gli_currency` DISABLE KEYS */;
INSERT INTO `gli_currency` VALUES (2,'BTC'),(3,'BTY'),(4,'ETH'),(15,'USDT');
/*!40000 ALTER TABLE `gli_currency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_eth`
--

DROP TABLE IF EXISTS `gli_eth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_eth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户uid',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `controller` varchar(50) NOT NULL COMMENT '控制器',
  `action` varchar(50) NOT NULL COMMENT '动作',
  `querystring` varchar(255) NOT NULL COMMENT '查询字符串',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  `ip` varchar(15) NOT NULL DEFAULT '0.0.0.0' COMMENT 'IP',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='后台日志';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_eth`
--

LOCK TABLES `gli_eth` WRITE;
/*!40000 ALTER TABLE `gli_eth` DISABLE KEYS */;
/*!40000 ALTER TABLE `gli_eth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_fxee_auth`
--

DROP TABLE IF EXISTS `gli_fxee_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_fxee_auth` (
  `user_id` varchar(255) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `refresh_token` varchar(255) NOT NULL,
  `expires` int(11) NOT NULL,
  `scope` text,
  `addtime` int(11) NOT NULL,
  PRIMARY KEY (`access_token`) USING BTREE,
  UNIQUE KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_fxee_auth`
--

LOCK TABLES `gli_fxee_auth` WRITE;
/*!40000 ALTER TABLE `gli_fxee_auth` DISABLE KEYS */;
INSERT INTO `gli_fxee_auth` VALUES ('200004','83a72f0890f54c041b1bc30cdf94e11c76d4dec3','3d531724edf0724892210230e94ec8c94618a25c',3600000,NULL,1526627669),('200003','c22d6146634cba04389967817458142ff7c8c821','d667cae1dcfce958053708c987f53adc43aea801',3600000,NULL,1527217778);
/*!40000 ALTER TABLE `gli_fxee_auth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_menu`
--

DROP TABLE IF EXISTS `gli_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档ID',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `url` char(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `hide` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `group` varchar(50) DEFAULT '' COMMENT '分组',
  `icon` varchar(50) DEFAULT '' COMMENT '分组',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pid` (`pid`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_menu`
--

LOCK TABLES `gli_menu` WRITE;
/*!40000 ALTER TABLE `gli_menu` DISABLE KEYS */;
INSERT INTO `gli_menu` VALUES (9,'系统设置',0,99,'config/index',0,'','fa fa-cogs',0),(10,'首页',0,0,'site/index',0,'','fa fa-home',0),(19,'查询格式化',9,20,'query/format-params',0,'API','',1),(20,'后台用户',9,1,'admin/index',0,'后台用户','fa fa-user',1),(21,'角色管理',9,2,'auth/index',0,'后台用户','fa fa-male',0),(25,'菜单管理',9,3,'menu/index',0,'','\r\nfa fa-sitemap',0),(26,'开发配置',9,9,'config/develop',0,'','fa fa-wrench',1),(27,'行为日志',9,10,'admin-log/index',0,'','fa fa-asterisk',0),(28,'刷新菜单',9,11,'menu/flush',0,'','fa fa-refresh',0),(30,'新增用户',20,0,'admin/add',0,'','fa fa-plus',1),(31,'更新',20,0,'admin/edit',0,'','fa fa-refresh',1),(32,'删除',20,0,'auth/delete',0,'','fa fa-remove',1),(33,'编辑',20,0,'auth/edit',0,'','fa fa-edit',1),(34,'添加',20,0,'auth/add',0,'','fa fa-plus',1),(35,'管理员授权',20,0,'admin/auth',0,'','fa fa-key',1),(36,'授权',21,0,'auth/auth',0,'','fa fa-key',1),(37,'访问授权',21,0,'auth/access',0,'','fa fa-key',1),(38,'成员授权',21,0,'auth/user',0,'','icon-key',1),(39,'菜单树',25,0,'menu/tree',0,'','fa fa-tree',1),(40,'新增',25,0,'menu/add',0,'系统设置','\r\nfa fa-plus',1),(41,'编辑',25,0,'menu/edit',0,'','fa fa-edit',1),(42,'导出',25,0,'menu/import',0,'','\r\nfa fa-external-link',1),(43,'排序',25,0,'menu/sort',1,'','fa fa-sort',1),(44,'编辑',26,0,'config/edit',0,'','\r\nfa fa-edit',1),(45,'删除',26,0,'config/del',0,'','\r\nfa fa-remove',1),(46,'新增配置',26,0,'config/add',0,'','\r\nfa fa-plus',1),(47,'保存',26,0,'config/save',0,'','fa fa-save',1),(50,'审核操作',49,0,'asset/verify-coin-out',0,'','fa fa-check',1),(57,'查看用户资产',22,0,'user/wallet-view',0,'','fa fa-eye',1),(59,'修改用户信息',22,1,'user/info-edit',0,'','fa fa-edit',1),(60,'入币操作',51,0,'asset/hand-in-coin',0,'','fa fa-bolt',1),(62,'入金操作',61,1,'asset/hand-in-cash',0,'','fa fa-bolt',1),(63,'审核操作',61,2,'asset/verify-cash-in',0,'','fa fa-check',1),(64,'最近充值记录',61,3,'asset/cash-last-record',0,'','fa fa-calendar-o',1),(72,'认证状态查询',71,0,'user/identity-status',0,'','fa fa-search',1),(73,'认证操作',71,0,'user/identity-opt',0,'','fa fa-check',1),(74,'上传图片',71,0,'user/upload-pic',0,'','fa fa-cloud-upload',1),(76,'设置导出计划',75,1,'asset/set-export-plan',0,'','',1),(77,'获取导出计划',75,2,'asset/get-export-plan',0,'','',1),(78,'删除导出计划',75,3,'asset/delete-export-plan',0,'','',1),(79,'已导出数据',75,4,'asset/get-export-data',0,'','',1),(80,'导出文件列表',75,5,'asset/show-plan',0,'','',1),(81,'匹配入金尾号',61,4,'asset/tail-number',0,'','',1),(87,'添加活动',85,0,'operations/add',0,'','',1),(88,'活动详情',85,0,'operations/detail',0,'','',1),(92,'导入',90,0,'white-list/import',0,'','',1),(93,'添加',90,0,'white-list/add',0,'','',1),(94,'删除',90,0,'white-list/delete',0,'','',1),(96,'矿机创建',95,0,'miner/add',0,'','',1),(97,'矿机详情',95,0,'miner/info',0,'','',1),(98,'矿机删除',95,0,'miner/delete',0,'','',1),(99,'矿机修改',95,0,'miner/edit',0,'','',1),(100,'矿机下线',95,0,'miner/set-offline',0,'','',1),(101,'矿机上线',95,0,'miner/set-online',0,'','',1),(105,'活动删除',85,0,'operations/delete',0,'','',1),(106,'活动修改',85,0,'operations/edit',0,'','',1),(109,'添加',108,0,'app/add',0,'','',1),(110,'详情',108,0,'app/info',0,'','',1),(111,'修改',108,0,'app/edit',0,'','',1),(113,'详情',90,0,'white-list/info',0,'','',1),(115,'收益核对api',114,0,'miner-check/item-api',0,'','',1),(116,'矿池奖励',114,1,'miner-check/initiator',0,'','',0),(117,'收益核对2',114,1,'miner-check/item2',0,'','',0),(118,'币种管理',0,1,'coin/index',0,'','',0),(119,'添加币种',118,1,'coin/add',0,'','',0),(120,'币种列表',118,0,'coin/index',0,'','',0),(121,'修改信息',118,2,'coin/edit',0,'','',1),(122,'图标上传',118,3,'coin/upload',0,'','',1),(123,'素材管理',0,2,'source/index',1,'','',1),(124,'文章列表',123,0,'source/article',0,'','',0),(125,'添加文章',123,1,'source/article-add',0,'','',0),(126,'编辑文章',123,2,'source/article-edit',0,'','',1),(127,'删除文章',123,3,'source/article-del',0,'','',1),(128,'公告列表',123,4,'source/notice',0,'','',0),(129,'添加公告',123,5,'source/notice-add',0,'','',0),(130,'编辑公告',123,6,'source/notice-edit',0,'','',1),(131,'删除公告',123,7,'source/notice-del',0,'','',1),(132,'矿工费',118,4,'miner-fee/cost',0,'','',0),(133,'添加矿工费',118,5,'miner-fee/add',0,'','',1),(135,'编辑矿工费',118,6,'miner-fee/edit',0,'','',1),(136,'删除矿工费',118,7,'miner-fee/del',0,'','',1),(137,'反馈管理',123,8,'source/feedback',0,'','',0),(138,'用户管理',0,3,'member/index',0,'','',0),(139,'用户信息编辑',138,1,'member/edit',0,'用户管理','',1),(140,'空投管理',0,4,'investment/index',0,'','',0),(141,'空投记录',140,0,'investment/index',0,'','',0),(142,'删除币种',118,8,'coin/delete',0,'','',1),(143,'根据id查找交易所',118,9,'coin/get-exchange-list-by-id',0,'','',1),(144,'空投类型',140,1,'investment/type',0,'','',0),(145,'类型管理',144,0,'investment/type',0,'','',1),(146,'添加类型',144,1,'investment/type-add',0,'','',1),(147,'删除类型',144,2,'investment/type-delete',0,'','',1),(148,'修改类型',144,3,'investment/type-update',0,'','',1),(150,'添加数据',141,0,'investment/add-one',0,'','',1),(151,'版本控制',0,5,'app/index',0,'','',0),(152,'版本控制',151,0,'app/list',0,'','',0),(153,'添加app',151,1,'app/add',0,'','',1),(154,'删除app',151,2,'app/delete',0,'','',1),(155,'导入文件',141,1,'investment/load-file',0,'','',1),(156,'钱包用户',138,2,'member/wallet',0,'钱包用户','',0),(157,'用户列表',138,0,'member/index',0,'用户管理','',0),(158,'上传app',151,3,'app/upload',0,'','',1),(159,'发币管理',0,6,'coin-publish/index',0,'','',0),(160,'管理列表',159,1,'coin-publish/list',0,'','',0),(161,'添加监控币种',159,2,'coin-publish/add',0,'','',1),(162,'删除监控币种',159,3,'coin-publish/del',0,'','',1),(163,'锁仓配置',0,7,'coin-publish-rule/index',0,'','',0),(164,'锁仓列表',163,1,'coin-publish-rule/list',0,'','',1),(165,'设置锁仓规则',163,2,'coin-publish-rule/set',0,'','',1),(166,'删除锁仓规则',163,3,'coin-publish-rule/del',0,'','',1),(167,'锁仓人员',163,4,'coin-release-member/list',0,'','',1),(168,'添加锁仓用户',163,5,'coin-release-member/set',0,'','',1),(169,'导入锁仓人员',163,6,'coin-release-member/implode',0,'','',1),(175,'提币审核',159,4,'coin-release-check/index',0,'','',0),(176,'审核列表',159,5,'coin-release-check/check-list',0,'','',1),(177,'查询列表',159,6,'coin-release-check/search-list',0,'','',1),(178,'审核申请',159,7,'coin-release-check/check',0,'','',1),(180,'推荐币种设置',118,10,'coin-recommend/index',0,'recommend','',0),(181,'添加推荐币种',180,11,'coin-recommend/add',0,'recommend','',1),(182,'删除推荐币种',180,12,'coin-recommend/del',0,'recommend','',1),(183,'币种代扣',118,13,'coin-withhold/index',0,'withhold','',0),(184,'设置代扣币种',183,1,'coin-withhold/set',0,'','',1),(185,'删除代扣币种',183,2,'coin-withhold/del',0,'','',1),(186,'代扣详情',183,3,'coin-withhold/detial',0,'','',1),(187,'设置代扣token',183,4,'coin-withhold/add-token',0,'','',1),(188,'删除代扣币种',183,5,'coin-withhold/del-token',0,'','',1),(189,'币种库配置',118,14,'coin/package',0,'','',0),(190,'删除币种库币种',189,1,'coin/package-del',0,'','',1),(191,'添加币种库币种',189,2,'coin/package-add',0,'','',1),(193,'应用分类列表',192,0,'application-category/index',0,'','',0),(194,'应用管理',0,7,'application-category/list',0,'','',0),(195,'应用分类列表',194,0,'application-category/index',0,'','',0),(196,'首页推荐列表',194,0,'applicate-recommend/index',0,'','',0),(197,'应用排行榜',194,3,'applicate-rank/index',0,'','',0),(198,'平台币图标',118,15,'coin/platform-coin',0,'','',0),(199,'钱包密码设置',159,8,'wallet/set-passwd',0,'','',0),(200,'banner管理',194,4,'coin-banner/index',0,'','',0),(201,'锁仓人员数据更新',163,7,'coin-release-member/data-update',0,'','',1),(203,'应用列表',195,1,'application/list',0,'','',1),(204,'编辑应用分类',195,2,'application-category/add-and-edit',0,'','',1),(205,'删除应用分类',195,3,'application-category/delete',0,'','',1),(206,'banner图管理',195,4,'application-category/banner-index',0,'','',1),(207,'应用首页推荐',195,5,'applicate-recommend/add',0,'','',1),(208,'应用状态操作',195,6,'application-category/set-enable',0,'','',1),(209,'添加应用',195,7,'application/add',0,'','',1),(210,'编辑应用',195,8,'application/edit',0,'','',1),(211,'删除应用',195,9,'application/delete',0,'','',1),(212,'添加应用分类',195,10,'application/add-category',0,'','',1),(213,'应用详情图管理',195,11,'application/image-index',0,'','',1),(214,'取消首页应用推荐',195,12,'applicate-recommend/delete',0,'','',1),(215,'应用状态操作2',195,23,'application/set-enable',0,'','',1),(216,'应用添加图片',195,24,'application/add-image',0,'','',1),(217,'应用图片删除',195,25,'application/delete-image',0,'','',1),(218,'应用分类图片添加',195,26,'application-category/add-banner',0,'','',1),(219,'应用分类图片删除',195,27,'application-category/delete-banner	',0,'','',1),(220,'首页推荐应用编辑图片',196,1,'applicate-recommend/set-icon',0,'','',1),(221,'应用排行榜搜索',197,1,'applicate-rank/search-index',0,'','',1),(222,'应用排行添加',197,2,'applicate-rank/add',0,'','',1),(223,'应用排行删除',197,3,'applicate-rank/delete',0,'','',1),(224,'banner添加',200,1,'coin-banner/add',0,'','',1),(225,'banner删除',200,2,'coin-banner/delete',0,'','',1),(226,'上传图片',194,5,'image/upload',0,'','',1),(227,'应用排序',195,28,'application/set-sort',0,'','',1),(228,'钱包管理',0,8,'wallet/list',0,'','',0),(229,'添加应用分类2',195,0,'application-category/add',0,'','',1),(230,'编辑应用分类2',195,0,'application-category/edit',0,'','',1),(231,'设备码查询',194,5,'platform-coins/list',0,'','',0),(232,'空投管理',140,3,'air-drop/list',0,'','',0),(233,'平行链管理',228,2,'coin-platform-withhold/index',0,'','',0),(234,'钱包banner管理',228,3,'coin-platform-banner/index',0,'','',0),(235,'钱包列表',228,1,'wallet/list',0,'','',0),(236,'OSS上传图片',194,8,'oss/upload',0,'','',1),(237,'OSS2上传图片',118,10,'oss/upload',0,'','',1);
/*!40000 ALTER TABLE `gli_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_migration`
--

DROP TABLE IF EXISTS `gli_migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_migration`
--

LOCK TABLES `gli_migration` WRITE;
/*!40000 ALTER TABLE `gli_migration` DISABLE KEYS */;
INSERT INTO `gli_migration` VALUES ('m000000_000000_base',1508228392),('m170206_022119_admin',1508228393),('m170206_022119_admin_log',1508228393),('m170206_022119_auth_assignment',1508228395),('m170206_022119_auth_item',1508228401),('m170206_022119_auth_item_child',1508228407),('m170206_022119_auth_rule',1508228412),('m170206_022119_config',1508228413),('m170206_022119_menu',1508228418),('m170206_022119_order',1508228418),('m170206_022119_picture',1508228419),('m170206_022119_position',1508228419),('m170206_022119_region',1508228421),('m170206_022119_tag',1508228422);
/*!40000 ALTER TABLE `gli_migration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_order_extra`
--

DROP TABLE IF EXISTS `gli_order_extra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_order_extra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bind_id` int(11) NOT NULL COMMENT '绑定唯一的id',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:order',
  `user_id` int(11) NOT NULL,
  `currency` tinyint(4) NOT NULL,
  `average_price` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_order_extra`
--

LOCK TABLES `gli_order_extra` WRITE;
/*!40000 ALTER TABLE `gli_order_extra` DISABLE KEYS */;
/*!40000 ALTER TABLE `gli_order_extra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_picture`
--

DROP TABLE IF EXISTS `gli_picture`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_picture` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id自增',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `md5` char(32) NOT NULL DEFAULT '' COMMENT '文件md5',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `md5` (`md5`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_picture`
--

LOCK TABLES `gli_picture` WRITE;
/*!40000 ALTER TABLE `gli_picture` DISABLE KEYS */;
/*!40000 ALTER TABLE `gli_picture` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_position`
--

DROP TABLE IF EXISTS `gli_position`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_position` (
  `position_id` int(11) NOT NULL COMMENT '推荐位ID',
  `name` char(30) NOT NULL COMMENT '推荐位名称'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='推荐位';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_position`
--

LOCK TABLES `gli_position` WRITE;
/*!40000 ALTER TABLE `gli_position` DISABLE KEYS */;
/*!40000 ALTER TABLE `gli_position` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_region`
--

DROP TABLE IF EXISTS `gli_region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_region` (
  `code` mediumint(9) NOT NULL COMMENT '行政区划代码',
  `parent_code` mediumint(9) NOT NULL COMMENT '上级行政区划',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '简称',
  `fullname` varchar(100) NOT NULL COMMENT '全称',
  `level` tinyint(1) NOT NULL COMMENT '层级',
  PRIMARY KEY (`code`) USING BTREE,
  KEY `area_type` (`level`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='中国标准行政区划，方便对接地图接口';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_region`
--

LOCK TABLES `gli_region` WRITE;
/*!40000 ALTER TABLE `gli_region` DISABLE KEYS */;
/*!40000 ALTER TABLE `gli_region` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gli_tag`
--

DROP TABLE IF EXISTS `gli_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gli_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(30) NOT NULL COMMENT '标签名',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '有该标签内容数量',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='评论';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gli_tag`
--

LOCK TABLES `gli_tag` WRITE;
/*!40000 ALTER TABLE `gli_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `gli_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wechat_token`
--

DROP TABLE IF EXISTS `wechat_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wechat_token` (
  `corp_id` varchar(32) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `expires_in` int(11) NOT NULL,
  `apply_time` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`corp_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wechat_token`
--

LOCK TABLES `wechat_token` WRITE;
/*!40000 ALTER TABLE `wechat_token` DISABLE KEYS */;
INSERT INTO `wechat_token` VALUES ('ww27d15692a6e514bc','0QIIc2be7u7i_5e_LgbKFK0aFPoiNoTbibz7ZoEBpreDo8EPoYZ4NwE6XQ7lWlTjP1zvmspIMHtRqwX6SW4Hm0Wqt-6ie8fS1-8nXIoPvZkInQTwp80p2LJeoJEe-6vBWWWhxSFkyzLXWs-sUdHo3PzbR3Tli4lVgNPoAamCBksnvzhsvxR8JooypxtUU2qhErOs-1PiNvlSYKd0nx_Wlg',7200,1526459989,'2018-05-02 15:06:50','2018-05-16 16:40:09'),('wwa54b55ddca830a01','Bj1kO5M-qt67Ivmr7gNhpA1O32BJGNSiKtclBncF_xtKL0ISb2SUCVRiqcnk3cwDN1BT0QUZZyaMFJkIs2096a5kHboZnAAcw8r4x6A-TFpc5eRl1s712BeyHIawbJYUeWIW7EUuaDObJ5z3p7O6mYyWZoA2RDLop5bIBIUYg6oqlboWnrTDjeLvQNZYOgi6ECz1r_DNNYKCzrYFmmhGkw',7200,1526481421,'2018-05-16 17:42:26','2018-05-16 22:37:21');
/*!40000 ALTER TABLE `wechat_token` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-06  6:50:54
