-- MySQL dump 10.13  Distrib 5.6.20, for Win64 (x86_64)
--
-- Host: localhost    Database: analytics
-- ------------------------------------------------------
-- Server version	5.6.20-log

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
-- Temporary view structure for view `all_requests`
--

DROP TABLE IF EXISTS `all_requests`;
/*!50001 DROP VIEW IF EXISTS `all_requests`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `all_requests` AS SELECT
 1 AS `mytime`,
 1 AS `weekday`,
 1 AS `total_requests`,
 1 AS `total_bad_requests`,
 1 AS `total_good_requests`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `apache_cron_runs`
--

DROP TABLE IF EXISTS `apache_cron_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apache_cron_runs` (
  `final_offset` int(10) unsigned NOT NULL,
  `final_ctime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `datatable`
--

DROP TABLE IF EXISTS `datatable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datatable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dimensions` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `observations` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `instance`
--

DROP TABLE IF EXISTS `instance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `instance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `servername` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `install_class` enum('prod','test','dev') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `servername` (`servername`)
) ENGINE=InnoDB AUTO_INCREMENT=166 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instance`
--

LOCK TABLES `instance` WRITE;
/*!40000 ALTER TABLE `instance` DISABLE KEYS */;
INSERT INTO `instance` VALUES (1,'defrancisco','defrancisco.crm.nysenate.gov','prod'),(2,'golden','golden.crm.nysenate.gov','prod'),(3,'robach','robach.crm.nysenate.gov','prod'),(4,'fuschillo','fuschillo.crm.nysenate.gov','prod'),(5,'ritchie','ritchie.crm.nysenate.gov','prod'),(6,'hannon','hannon.crm.nysenate.gov','prod'),(7,'skelos','skelos.crm.nysenate.gov','prod'),(8,'seward','seward.crm.nysenate.gov','prod'),(9,'young','young.crm.nysenate.gov','prod'),(10,'farley','farley.crm.nysenate.gov','prod'),(11,'martins','martins.crm.nysenate.gov','prod'),(12,'flanagan','flanagan.crm.nysenate.gov','prod'),(13,'griffo','griffo.crm.nysenate.gov','prod'),(14,'bonacic','bonacic.crm.nysenate.gov','prod'),(15,'sd99','sd99.crm.nysenate.gov','prod'),(16,'ball','ball.crm.nysenate.gov','prod'),(17,'valesky','valesky.crm.nysenate.gov','prod'),(18,'little','little.crm.nysenate.gov','prod'),(19,'nozzolio','nozzolio.crm.nysenate.gov','prod'),(20,'maziarz','maziarz.crm.nysenate.gov','prod'),(21,'sd83','sd83.crm.nysenate.gov','prod'),(22,'boyle','boyle.crm.nysenate.gov','prod'),(23,'sd97','sd97.crm.nysenate.gov','prod'),(24,'3rdpartystatewide','3rdpartystatewide.crm.nysenate.gov','prod'),(25,'marchione','marchione.crm.nysenate.gov','prod'),(26,'addabbo','addabbo.crm.nysenate.gov','prod'),(27,'dilan','dilan.crm.nysenate.gov','prod'),(28,'gallivan','gallivan.crm.nysenate.gov','prod'),(29,'ranzenhofer','ranzenhofer.crm.nysenate.gov','prod'),(30,'stewartcousins','stewartcousins.crm.nysenate.gov','prod'),(31,'matrins','matrins.crm.nysenate.gov','prod'),(32,'gipson','gipson.crm.nysenate.gov','prod'),(33,'hoylman','hoylman.crm.nysenate.gov','prod'),(34,'ruralresources','ruralresources.crm.nysenate.gov','prod'),(35,'avella','avella.crm.nysenate.gov','prod'),(36,'training1','training1.crm.nysenate.gov','prod'),(37,'diaz','diaz.crm.nysenate.gov','prod'),(38,'smith','smith.crm.nysenate.gov','prod'),(39,'squadron','squadron.crm.nysenate.gov','prod'),(40,'stavisky','stavisky.crm.nysenate.gov','prod'),(41,'lanza','lanza.crm.nysenate.gov','prod'),(42,'lavalle','lavalle.crm.nysenate.gov','prod'),(43,'krueger','krueger.crm.nysenate.gov','prod'),(44,'peralta','peralta.crm.nysenate.gov','prod'),(45,'obrien','obrien.crm.nysenate.gov','prod'),(46,'zeldin','zeldin.crm.nysenate.gov','prod'),(47,'alesi','alesi.crm.nysenate.gov','prod'),(48,'sampson','sampson.crm.nysenate.gov','prod'),(49,'omara','omara.crm.nysenate.gov','prod'),(50,'none','none.crm.nysenate.gov','prod'),(51,'sanders','sanders.crm.nysenate.gov','prod'),(52,'rivera','rivera.crm.nysenate.gov','prod'),(53,'larkin','larkin.crm.nysenate.gov','prod'),(54,'demo','demo.crm.nysenate.gov','prod'),(55,'3rdparty','3rdparty.crm.nysenate.gov','prod'),(56,'latimer','latimer.crm.nysenate.gov','prod'),(57,'marcellino','marcellino.crm.nysenate.gov','prod'),(58,'ojohnson','ojohnson.crm.nysenate.gov','prod'),(59,'carlucci','carlucci.crm.nysenate.gov','prod'),(60,'felder','felder.crm.nysenate.gov','prod'),(61,'oppenheimer','oppenheimer.crm.nysenate.gov','prod'),(62,'saland','saland.crm.nysenate.gov','prod'),(63,'mcdonald','mcdonald.crm.nysenate.gov','prod'),(64,'grisanti','grisanti.crm.nysenate.gov','prod'),(65,'felde','felde.crm.nysenate.gov','prod'),(66,'parker','parker.crm.nysenate.gov','prod'),(67,'montgomery','montgomery.crm.nysenate.gov','prod'),(68,'kennedy','kennedy.crm.nysenate.gov','prod'),(69,'klein','klein.crm.nysenate.gov','prod'),(70,'breslin','breslin.crm.nysenate.gov','prod'),(71,'hassellthompson','hassellthompson.crm.nysenate.gov','prod'),(72,'serrano','serrano.crm.nysenate.gov','prod'),(73,'training2','training2.crm.nysenate.gov','prod'),(74,'gianaris','gianaris.crm.nysenate.gov','prod'),(75,'adams','adams.crm.nysenate.gov','prod'),(76,'perkins','perkins.crm.nysenate.gov','prod'),(77,'neison','neison.crm.nysenate.gov','prod'),(78,'training','training.crm.nysenate.gov','prod'),(79,'blah','blah.crm.nysenate.gov','prod'),(80,'maziaraz','maziaraz.crm.nysenate.gov','prod'),(81,'espaillat','espaillat.crm.nysenate.gov','prod'),(82,'huntley','huntley.crm.nysenate.gov','prod'),(83,'template','template.crm.nysenate.gov','prod'),(84,'libous','libous.crm.nysenate.gov','prod'),(85,'savino','savino.crm.nysenate.gov','prod'),(86,'tkaczyk','tkaczyk.crm.nysenate.gov','prod'),(87,'training23','training23.crm.nysenate.gov','prod'),(88,'training3','training3.crm.nysenate.gov','prod'),(89,'training4','training4.crm.nysenate.gov','prod'),(90,'123click','123click.crm.nysenate.gov','prod'),(91,'sd95','sd95.crm.nysenate.gov','prod'),(92,'sd98','sd98.crm.nysenate.gov','prod'),(93,'mincomms','mincomms.crm.nysenate.gov','prod'),(94,'nonneenwlkwelhwelkdslfshlkdhs','nonneenwlkwelhwelkdslfshlkdhs.crm.nysenate.gov','prod'),(95,'obrein','obrein.crm.nysenate.gov','prod'),(96,'marchoine','marchoine.crm.nysenate.gov','prod'),(97,'tkazcyk','tkazcyk.crm.nysenate.gov','prod'),(98,'nozollio','nozollio.crm.nysenate.gov','prod'),(99,'richie','richie.crm.nysenate.gov','prod'),(100,'kruger','kruger.crm.nysenate.gov','prod'),(101,'zledin','zledin.crm.nysenate.gov','prod'),(102,'nozzollio','nozzollio.crm.nysenate.gov','prod'),(103,'quadron','quadron.crm.nysenate.gov','prod'),(104,'gball','gball.crm.nysenate.gov','prod'),(105,'common','common.crm.nysenate.gov','prod'),(106,'fushillo','fushillo.crm.nysenate.gov','prod'),(107,'test','test.crm.nysenate.gov','prod'),(108,'espailait','espailait.crm.nysenate.gov','prod'),(109,'espailat','espailat.crm.nysenate.gov','prod'),(110,'fuschill','fuschill.crm.nysenate.gov','prod'),(111,'marchinoe','marchinoe.crm.nysenate.gov','prod'),(112,'teach1','teach1.crm.nysenate.gov','prod'),(113,'zelden','zelden.crm.nysenate.gov','prod'),(114,'lav%0d%0aalle','lav%0d%0aalle.crm.nysenate.gov','prod'),(115,'lavalle=images','lavalle=images.crm.nysenate.gov','prod'),(116,'gibson','gibson.crm.nysenate.gov','prod'),(117,'flannagan','flannagan.crm.nysenate.gov','prod'),(118,'statewide','statewide.crm.nysenate.gov','prod'),(119,'sd99staging','sd99staging.crm.nysenate.gov','prod'),(120,'sample','sample.crm.nysenate.gov','prod'),(121,'%0dnozzolio','%0dnozzolio.crm.nysenate.gov','prod'),(122,'trianing1','trianing1.crm.nysenate.gov','prod'),(123,'senatedev','senatedev.crm.nysenate.gov','prod'),(124,'tkcazyk','tkcazyk.crm.nysenate.gov','prod'),(125,'images','images.crm.nysenate.gov','prod'),(126,'squdron','squdron.crm.nysenate.gov','prod'),(127,'golden%0d%0a','golden%0d%0a.crm.nysenate.gov','prod'),(128,'tkacyzk','tkacyzk.crm.nysenate.gov','prod'),(129,'stewart-cousins','stewart-cousins.crm.nysenate.gov','prod'),(130,'nozzoli','nozzoli.crm.nysenate.gov','prod'),(131,'cardillo','cardillo.crm.nysenate.gov','prod'),(132,'zeldon','zeldon.crm.nysenate.gov','prod'),(133,'farely','farely.crm.nysenate.gov','prod'),(134,'sewards','sewards.crm.nysenate.gov','prod'),(135,'sd99test','sd99test.crm.nysenate.gov','prod'),(136,'hassellt','hassellt.crm.nysenate.gov','prod'),(137,'bo','bo.crm.nysenate.gov','prod'),(138,'sd08','sd08.crm.nysenate.gov','prod'),(139,'district8','district8.crm.nysenate.gov','prod'),(140,'kims','kims.crm.nysenate.gov','prod'),(141,'dhill','dhill.crm.nysenate.gov','prod'),(142,'loss','loss.crm.nysenate.gov','prod'),(143,'dmv','dmv.crm.nysenate.gov','prod'),(144,'defranciso','defranciso.crm.nysenate.gov','prod'),(145,'machione','machione.crm.nysenate.gov','prod'),(146,'ranzenhoffer','ranzenhoffer.crm.nysenate.gov','prod'),(147,'fuschilo','fuschilo.crm.nysenate.gov','prod'),(148,'sendgrid','sendgrid.crm.nysenate.gov','prod'),(149,'analytics','analytics.crm.nysenate.gov','prod'),(150,'klien','klien.crm.nysenate.gov','prod'),(151,'mazirz','mazirz.crm.nysenate.gov','prod'),(152,'crmtest','crmtest.crm.nysenate.gov','prod'),(153,'sd99','sd99.crmtest.crm.nysenate.gov','test'),(154,'traingin1','traingin1.crm.nysenate.gov','prod'),(155,'marcinone','marcinone.crm.nysenate.gov','prod'),(156,'fuchillo','fuchillo.crm.nysenate.gov','prod'),(157,'duane','duane.crm.nysenate.gov','prod'),(158,'takczyk','takczyk.crm.nysenate.gov','prod'),(159,'tranining1','tranining1.crm.nysenate.gov','prod'),(160,'balls','balls.crm.nysenate.gov','prod'),(161,'bluebird','bluebird.crm.nysenate.gov','prod'),(162,'sd09','sd09.crm.nysenate.gov','prod'),(163,'sd9','sd9.crm.nysenate.gov','prod'),(164,'example','example.crm.nysenate.gov','prod'),(165,'ranzenhoeffer','ranzenhoeffer.crm.nysenate.gov','prod');
/*!40000 ALTER TABLE `instance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `location`
--

DROP TABLE IF EXISTS `location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ipv4_start` int(10) unsigned DEFAULT NULL,
  `ipv4_end` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `location__ipv4_range` (`ipv4_start`,`ipv4_end`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location`
--

LOCK TABLES `location` WRITE;
/*!40000 ALTER TABLE `location` DISABLE KEYS */;
INSERT INTO `location` VALUES
(1,'Not Found',NULL,NULL),
(2,'LOB Fl B3',168493850,168494078),
(3,'LOB Fl B2',168559386,168559614),
(4,'LOB Fl 1',168625178,168625662),
(5,'LOB Fl 2',168690458,168690686),
(6,'LOB Fl 3',168755994,168756222),
(7,'LOB Fl 4',168821530,168821758),
(8,'LOB Fl 5',168887066,168887294),
(9,'LOB Fl 6',168952602,168952830),
(10,'LOB Fl 7',169018138,169018366),
(11,'LOB Fl 8',169083930,169084414),
(12,'LOB Fl 9',169149466,169149950),
(13,'LOB 250 Broadway',169607962,169608190),
(14,'A.E.S. Fl 13',169280282,169280510),
(15,'A.E.S. Fl 14',169280538,169280766),
(16,'A.E.S. Fl 15',169280794,169281022),
(17,'A.E.S. Fl 16',169281050,169281278),
(18,'A.E.S. Fl 24',169281306,169281534),
(19,'A.E.S. Fl 25',169281562,169281790),
(20,'A.E.S. Fl 26',169281818,169282046),
(21,'A.E.S. Basement',169282074,169282302),
(22,'Corporate Woods',169804570,169804798),
(23,'Capitol West',169346074,169346558),
(24,'Capitol East Fl 3',169411354,169411582),
(25,'Capitol East Fl 4',169411610,169411838),
(26,'Capitol East Fl 5',169411866,169412094),
(27,'Agency-4 Fl 2 & Fl 11',169476890,169477118),
(28,'Agency-4 Fl 16 & Fl 17',169477146,169477374),
(29,'Agency-4 Fl 18',169477402,169477630),
(30,'Satellite Offices',174260480,174270718),
(31,'VPN User',174284800,174285055),
(32,'VPN ?',174285056,174285296),
(33,'VPN Sfms',174285297,174285310),
(34,'VPN Telecom Vendor',174285286,174285295),
(35,'VPN Asax',174285312,174285567),
(36,'District Offices',170459136,170524671),
(37,'District Offices',170524672,170590207),
(38,'District Offices',2886860800,2886926335),
(39,'District Offices',2887516160,2887581695),
(40,'District Offices Visitor',2886926336,2886991871),
(41,'District Offices Visitor',2887581696,2887647231),
(42,'Wireless',167971329,167971583),
(43,'Wireless LOB',167971840,167972095),
(44,'Wireless Agency-4',167972096,167972351),
(45,'Wireless A.E.S.',167972352,167972607),
(46,'Wireless Capitol',167972608,167972863),
(47,'Wireless C.Woods',167972864,167973119),
(48,'Wireless District Offices',167973120,167973375),
(49,'Wireless LOB-Top-Fls',167973376,167973631),
(50,'Wireless Visitor',174278144,174278654),
(51,'Wireless Visitor LOB',174278656,174278911),
(52,'Wireless Visitor Agency-4',174278912,174279167),
(53,'Wireless Visitor A.E.S.',174279168,174279423),
(54,'Wireless Visitor Capitol',174279424,174279679),
(55,'Wireless Visitor C.Woods',174279680,174279935),
(56,'Wireless Visitor District Offices',174279936,174280191),
(57,'Wireless Visitor LOB-Top-Fls',174280192,174280447),
(58,'Serverfarm 1',167838465,167838494),
(59,'Serverfarm 1',167838721,167838974),
(60,'Serverfarm 2',167838497,167838526),
(61,'Serverfarm 2',167838977,167839230),
(62,'Serverfarm 3',167904001,167904030),
(63,'Serverfarm 3',167839233,167839486),
(64,'Serverfarm 4',167904033,167904062),
(65,'Serverfarm 4',167839489,167839742),
(66,'Serverfarm 5',167904065,167904126),
(67,'AVAYA',167838593,167838718),
(68,'AVAYA',167839745,167839998);
/*!40000 ALTER TABLE `location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request`
--

DROP TABLE IF EXISTS `request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `request` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `remote_ip` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans_ip` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `url_id` int(10) unsigned DEFAULT NULL,
  `response_code` int(10) unsigned DEFAULT NULL,
  `response_time` int(10) unsigned DEFAULT NULL,
  `transfer_rx` int(10) unsigned DEFAULT NULL,
  `transfer_tx` int(10) unsigned DEFAULT NULL,
  `method` enum('GET','POST','HEAD','OPTION') COLLATE utf8_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `query` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `instance_id` (`instance_id`),
  KEY `request__trans_ip` (`trans_ip`),
  KEY `request__location_id` (`location_id`),
  KEY `request__url_id` (`url_id`),
  CONSTRAINT `request_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14133771 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER*/ /*!50003 TRIGGER `request_before_insert` BEFORE INSERT ON `request` FOR EACH ROW BEGIN

  SET @t_loc_id = NULL;
  SET @t_url_id = NULL;

  SET NEW.trans_ip = INET_ATON(NEW.remote_ip);

  SELECT id INTO @t_loc_id FROM location WHERE NEW.trans_ip BETWEEN ipv4_start AND ipv4_end;
  SET NEW.location_id = IFNULL(@t_loc_id,1);

  SET @clean_url = preg_replace('([a-z]+),.*/', '$1',
                    preg_replace('/(_vti).*/', '$1',
                     preg_replace('/(\\/user\\/)[0-9]+/', '$1',
                      preg_replace('/^(.+)\\/$/', '$1',
                       preg_replace('/\\/[0-9]+$|\\/[0-9]+\\,.*|\\&.*/', '', NEW.path)
                      )
                     )
                    )
                   );
  SELECT id INTO @t_url_id
    FROM url
    WHERE
      (match_full = 0 AND path=@clean_url)
      OR (match_full = 1 AND path=@clean_url AND preg_match(search, NEW.query))
    ORDER BY match_full DESC, path
    LIMIT 1;
  SET NEW.url_id = IFNULL(@t_url_id,1);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `summary_15m`
--

DROP TABLE IF EXISTS `summary_15m`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `summary_15m` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans_ip` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `503_errors` int(10) unsigned DEFAULT NULL,
  `500_errors` int(10) unsigned DEFAULT NULL,
  `page_views` int(10) unsigned DEFAULT NULL,
  `response_time` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`),
  KEY `instance_id` (`instance_id`),
  KEY `summary_15m__trans_ip` (`trans_ip`),
  KEY `summary_15m__location_id` (`location_id`),
  CONSTRAINT `summary_15m_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `summary_1d`
--

DROP TABLE IF EXISTS `summary_1d`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `summary_1d` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans_ip` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `503_errors` int(10) unsigned DEFAULT NULL,
  `500_errors` int(10) unsigned DEFAULT NULL,
  `page_views` int(10) unsigned DEFAULT NULL,
  `response_time` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`),
  KEY `instance_id` (`instance_id`),
  KEY `summary_1d__trans_ip` (`trans_ip`),
  KEY `summary_1d__location_id` (`location_id`),
  CONSTRAINT `summary_1d_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `summary_1h`
--

DROP TABLE IF EXISTS `summary_1h`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `summary_1h` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans_ip` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `503_errors` int(10) unsigned DEFAULT NULL,
  `500_errors` int(10) unsigned DEFAULT NULL,
  `page_views` int(10) unsigned DEFAULT NULL,
  `response_time` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`),
  KEY `instance_id` (`instance_id`),
  KEY `summary_1h__trans_ip` (`trans_ip`),
  KEY `summary_1h__location_id` (`location_id`),
  CONSTRAINT `summary_1h_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `summary_1m`
--

DROP TABLE IF EXISTS `summary_1m`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `summary_1m` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans_ip` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `503_errors` int(10) unsigned DEFAULT NULL,
  `500_errors` int(10) unsigned DEFAULT NULL,
  `page_views` int(10) unsigned DEFAULT NULL,
  `response_time` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`),
  KEY `instance_id` (`instance_id`),
  KEY `summary_1m__trans_ip` (`trans_ip`),
  KEY `summary_1m__location_id` (`location_id`),
  CONSTRAINT `summary_1m_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uniques_15m`
--

DROP TABLE IF EXISTS `uniques_15m`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uniques_15m` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans_ip` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`,`type`,`value`),
  KEY `type` (`type`),
  KEY `instance_id` (`instance_id`),
  KEY `uniques_15m__trans_ip` (`trans_ip`),
  KEY `uniques_15m__location_id` (`location_id`),
  CONSTRAINT `uniques_15m_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uniques_1d`
--

DROP TABLE IF EXISTS `uniques_1d`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uniques_1d` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans_ip` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`,`type`,`value`),
  KEY `type` (`type`),
  KEY `instance_id` (`instance_id`),
  KEY `uniques_1d__trans_ip` (`trans_ip`),
  KEY `uniques_1d__location_id` (`location_id`),
  CONSTRAINT `uniques_1d_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uniques_1h`
--

DROP TABLE IF EXISTS `uniques_1h`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uniques_1h` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans_ip` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`,`type`,`value`),
  KEY `type` (`type`),
  KEY `instance_id` (`instance_id`),
  KEY `uniques_1h__trans_ip` (`trans_ip`),
  KEY `uniques_1h__location_id` (`location_id`),
  CONSTRAINT `uniques_1h_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uniques_1m`
--

DROP TABLE IF EXISTS `uniques_1m`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uniques_1m` (
  `time` datetime DEFAULT NULL,
  `remote_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans_ip` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `instance_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `time` (`time`,`instance_id`,`remote_ip`,`type`,`value`),
  KEY `type` (`type`),
  KEY `instance_id` (`instance_id`),
  KEY `uniques_1m__trans_ip` (`trans_ip`),
  KEY `uniques_1m__location_id` (`location_id`),
  CONSTRAINT `uniques_1m_ibfk_1` FOREIGN KEY (`instance_id`) REFERENCES `instance` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `url`
--

DROP TABLE IF EXISTS `url`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `url` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `match_full` bit(1) DEFAULT NULL,
  `action` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `search` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=369 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `url`
--

LOCK TABLES `url` WRITE;
/*!40000 ALTER TABLE `url` DISABLE KEYS */;
INSERT INTO `url` VALUES (1,'Not Found','\0','read',NULL,NULL),(2,'Contact Add','\0','read','/civicrm/contact',NULL),(3,'Contact View','\0','read','/civicrm/contact/view',NULL),(4,'Contact View','\0','read','/civicrm/contact/view/rel',NULL),(5,'Contact View Ajax','\0','read','/civicrm/ajax/contact',NULL),(6,'Contact Delete','\0','delete','/civicrm/contact/view/delete',NULL),(7,'Contact Merge','\0','update','/civicrm/contact/merge',NULL),(8,'Contact Merge','\0','read','/civicrm/contact/map',NULL),(9,'Contact Dedupe','\0','read','/civicrm/contact/dedupefind',NULL),(10,'Contact Dedupe','\0','read','/civicrm/ajax/dedupefind',NULL),(11,'Contact Print','\0','read','/civicrm/contact/view/print',NULL),(12,'Contact Log View','\0','read','/civicrm/contact/view/log',NULL),(13,'Contact Mailing','\0','read','/civicrm/ajax/contactmailing',NULL),(14,'Contact Mailing','\0','read','/civicrm/contact/view/mailing',NULL),(15,'Contact Tags','\0','read','/civicrm/contact/view/tag',NULL),(16,'Contact Cases View','\0','read','/civicrm/contact/view/case',NULL),(17,'Contact Cases Edit','\0','read','/civicrm/contact/view/case/editClient',NULL),(18,'Contact Tag Create','\0','create','/civicrm/ajax/entity_tag/create',NULL),(19,'Contact Tag Process','\0','create','/civicrm/ajax/processTags',NULL),(20,'Contact Tag View','\0','read','/civicrm/ajax/entity_tag/get',NULL),(21,'Contact Hover Details','\0','read','/civicrm/profile/view',NULL),(22,'Contact Groups View','\0','read','/civicrm/contact/view/group',NULL),(23,'Contact View','\0','read','/civicrm/contact/view',NULL),(24,'Contact Changelog Count','\0','read','/civicrm/ajax/count/changelog',NULL),(25,'Contact Activity Count','\0','read','/civicrm/ajax/count/activity',NULL),(26,'Contact Inline Edit','\0','update','/civicrm/ajax/inline',NULL),(27,'Contact Tag Tree','\0','update','/civicrm/ajax/tag/tree',NULL),(28,'Contact Relationships','\0','read','/civicrm/ajax/globalrelationships',NULL),(29,'Contact Relationships','\0','read','/civicrm/ajax/relation',NULL),(30,'Contact Relationships','\0','read','/civicrm/ajax/relationshipcontacts',NULL),(31,'Contact Relationships','\0','read','/civicrm/ajax/relationshipContactTypeList',NULL),(32,'Contact Relationships','\0','read','/civicrm/ajax/contactrelationships',NULL),(33,'Contact Relationships','\0','read','/civicrm/ajax/clientrelationships',NULL),(34,'Contact Smartgroup View','\0','read','/civicrm/contact/view/smartgroup',NULL),(35,'Contact Note View','\0','read','/civicrm/contact/view/note',NULL),(36,'Contact Note Add','\0','create','/civicrm/contact/addnode',NULL),(37,'Contact Changelog View','\0','read','/civicrm/ajax/changelog',NULL),(38,'Contact Activity View','\0','read','/civicrm/contact/view/activity',NULL),(39,'Contact Vcard','\0','read','/civicrm/contact/view/vcard',NULL),(40,'Contact Election District','\0','read','/civicrm/ajax/ed',NULL),(41,'Contact Update Script','\0','update','/civicrm/scripts/updateAddresses.php',NULL),(42,'Contact Inline Edit','\0','update','/civicrm/ajax/inlinenode/1,destination=civicrm/ajax/inlinenode',NULL),(43,'Contact Inline Edit','\0','update','/civicrm/ajax/inlinenode',NULL),(44,'Contact Cases','\0','read','/civicrm/contact/view/casenode',NULL),(45,'Activity View','\0','read','/civicrm/imap/ajax/getActivityDetails',NULL),(46,'Activity View','\0','read','/civicrm/ajax/activity',NULL),(47,'Activity View','\0','read','/civicrm/activity/view',NULL),(48,'Activity Create','\0','read','/civicrm/activity',NULL),(49,'Activity Update','\0','update','/civicrm/case/activity',NULL),(50,'Activity Convert','\0','update','/civicrm/ajax/activity/convert',NULL),(51,'Activity Types','\0','read','/civicrm/admin/options/activity_type',NULL),(52,'Activity Email Add','\0','create','/civicrm/activity/email/add',NULL),(53,'Activity Add Activity','\0','create','/civicrm/activity/pdf/add',NULL),(54,'Activity Types','\0','read','/civicrm/ajax/subtype',NULL),(55,'Activity New','\0','read','/civicrm/activity/addnode',NULL),(56,'Activity Add','\0','create','/civicrm/activity/add',NULL),(57,'Case View','\0','read','/civicrm/case',NULL),(58,'Case Add','\0','create','/civicrm/case/add',NULL),(59,'Case List','\0','read','/civicrm/case/ajax/unclosed',NULL),(60,'Case Details','\0','read','/civicrm/case/details',NULL),(61,'Case Details Ajax','\0','read','/civicrm/case/ajax/details',NULL),(62,'Case Add To','\0','update','/civicrm/case/addToCase',NULL),(63,'Case Roles','\0','read','/civicrm/ajax/caseroles',NULL),(64,'Case','\0','read','/civicrm/case/activity/view',NULL),(65,'Case Report Print','\0','read','/civicrm/case/report/print',NULL),(66,'Case Status Update','\0','update','/civicrm/case/changeactivitystatus',NULL),(67,'Case Delete Role','\0','delete','/civicrm/ajax/delcaserole',NULL),(68,'InboundEmail Contact Search','\0','read','/civicrm/imap/ajax/searchContacts',NULL),(69,'InboundEmail Contact Create','\0','create','/civicrm/imap/ajax/createNewContact',NULL),(70,'InboundEmail Contact Search','\0','read','/civicrm/imap/ajax/contact/search',NULL),(71,'InboundEmail Contact Add Email','\0','create','/civicrm/imap/ajax/addEmail',NULL),(72,'InboundEmail Contact Add Email','\0','create','/civicrm/imap/ajax/contact/addEmail',NULL),(73,'InboundEmail Contact Add','\0','create','/civicrm/imap/ajax/contact/add',NULL),(74,'InboundEmail Matched List','\0','read','/civicrm/imap/matched',NULL),(75,'InboundEmail Matched List Ajax','\0','read','/civicrm/imap/ajax/listMatchedMessages',NULL),(76,'InboundEmail Matched List Ajax','\0','read','/civicrm/imap/ajax/matched/list',NULL),(77,'InboundEmail Matched Clear','\0','read','/civicrm/imap/ajax/matched/clear',NULL),(78,'InboundEmail Matched Clear','\0','update','/civicrm/imap/ajax/untagActivity',NULL),(79,'InboundEmail Matched Delete','\0','read','/civicrm/imap/ajax/matched/delete',NULL),(80,'InboundEmail Matched Details','\0','read','/civicrm/imap/ajax/matched/details',NULL),(81,'InboundEmail Matched Reassign','\0','read','/civicrm/imap/ajax/matched/reassign',NULL),(82,'InboundEmail Matched Reassign','\0','read','/civicrm/imap/ajax/matched/edit',NULL),(83,'InboundEmail Unmatched List','\0','read','/civicrm/imap/unmatched',NULL),(84,'InboundEmail Unmatched List Ajax','\0','read','/civicrm/imap/ajax/listUnmatchedMessages',NULL),(85,'InboundEmail Unmatched List Ajax','\0','read','/civicrm/imap/ajax/unmatched/list',NULL),(86,'InboundEmail Unmatched Assign','\0','update','/civicrm/imap/ajax/unmatched/assign',NULL),(87,'InboundEmail Unmatched Details','\0','read','/civicrm/imap/ajax/unmatched/details',NULL),(88,'InboundEmail Unmatched Delete','\0','read','/civicrm/imap/ajax/unmatched/delete',NULL),(89,'InboundEmail Message Details','\0','read','/civicrm/imap/ajax/getMessageDetails',NULL),(90,'InboundEmail Assign Message','\0','update','/civicrm/imap/ajax/assignMessage',NULL),(91,'InboundEmail Delete Message','\0','delete','/civicrm/imap/ajax/deleteMessage',NULL),(92,'InboundEmail Delete Message','\0','delete','/civicrm/imap/ajax/deleteActivity',NULL),(93,'InboundEmail Reassign Message','\0','update','/civicrm/imap/ajax/reassignActivity',NULL),(94,'InboundEmail Tag Assign Issuecode','\0','update','/civicrm/imap/ajax/issuecode',NULL),(95,'InboundEmail Tag Search','\0','read','/civicrm/imap/ajax/searchTags',NULL),(96,'InboundEmail Tag Search','\0','read','/civicrm/imap/ajax/tag/search',NULL),(97,'InboundEmail Tag Add','\0','create','/civicrm/imap/ajax/addTags',NULL),(98,'InboundEmail Tag Add','\0','create','/civicrm/imap/ajax/tag/add',NULL),(99,'InboundEmail Report','\0','read','/civicrm/imap/reports',NULL),(100,'InboundEmail Report List','\0','read','/civicrm/imap/ajax/reports/list',NULL),(101,'InboundEmail File Bug','\0','create','/civicrm/imap/ajax/fileBug',NULL),(102,'Dashlet News','\0','read','/civicrm/dashlet/news',NULL),(103,'Dashlet My Cases','\0','read','/civicrm/dashlet/myCases',NULL),(104,'Dashlet All Cases','\0','read','/civicrm/dashlet/allCases',NULL),(105,'Dashlet Activites','\0','read','/civicrm/dashlet/activity',NULL),(106,'Dashlet Report','\0','read','/civicrm/report/instance',NULL),(107,'Dashlet Activity View','\0','read','/civicrm/ajax/contactactivity',NULL),(108,'Dashlet Group List','\0','read','/civicrm/ajax/grouplist',NULL),(109,'Dashlet Twitter','\0','read','/civicrm/dashlet/twitter',NULL),(110,'Dashlet Report','\0','read','/civicrm/dashlet/districtstats',NULL),(111,'Dashlet Report','\0','read','/civicrm/dashlet',NULL),(112,'Dashlet Report','\0','read','/civicrm/dashlet/districtstatsnode',NULL),(113,'User Login','\0','update','/login',NULL),(114,'User Login','\0','update','/user/login',NULL),(115,'User Login','\0','read','/node',NULL),(116,'User Logout','\0','update','/user/logout',NULL),(117,'User Logout','\0','update','/logoutenter',NULL),(118,'User Logout','\0','update','/civicrm/logoutenter',NULL),(119,'User Logout','\0','update','/logout%20enter',NULL),(120,'User Logout','\0','update','/logout',NULL),(121,'User Logout','\0','update','/logoff',NULL),(122,'User Logout','\0','update','/signout',NULL),(123,'User Logout','\0','update','/exit',NULL),(124,'User Logout','\0','update','/civicrm/logout',NULL),(125,'User Logout','\0','update','/civicrm/dashboardlogoutenter',NULL),(126,'User Logout','\0','update','/civicrm/logout,enter',NULL),(127,'User Logout','\0','update','/logout',NULL),(128,'User Logout','\0','update','/logoff',NULL),(129,'User Logout','\0','update','/logout,enter.',NULL),(130,'User Logout','\0','update','/logout,enter',NULL),(131,'User Logout','\0','update','/logoutnode',NULL),(132,'User Logout','\0','update','/civicrm/dashboard/logout',NULL),(133,'Dashboard','\0','read','/civicrm/dashboard',NULL),(134,'Dashboard','\0','read','/civicrm/ajax/dashboard',NULL),(135,'Dashboard','\0','read','/',NULL),(136,'Dashboard','\0','update','/civicrm',NULL),(137,'Dashboard','\0','read','/node',NULL),(138,'Dashboard','\0','read','N',NULL),(139,'Dashboard','\0','read','/index.php',NULL),(140,'Dashboard','\0','read','/%22/icons/graycol.gif/%22',NULL),(141,'Dashboard','\0','read','/civicrm/%5C',NULL),(142,'Dashboard','\0','update','/civicrm/logoutenterdashboard',NULL),(143,'Dashboard','\0','read','/node/1/edit',NULL),(144,'Dashboard','\0','read','/]',NULL),(145,'Search Case','\0','read','/civicrm/case/search',NULL),(146,'Search Group','\0','read','/civicrm/group/search',NULL),(147,'Search Custom Select','\0','update','/civicrm/ajax/markSelection',NULL),(148,'Search Setup','\0','read','/civicrm/contact/search/custom',NULL),(149,'Search Basic','\0','read','/civicrm/contact/search/basic',NULL),(150,'Search Advanced','\0','read','/civicrm/contact/search/advanced',NULL),(151,'Search Activity','\0','read','/civicrm/activity/search',NULL),(152,'Search Contact','\0','read','/civicrm/contact/search',NULL),(153,'Search Builder','\0','read','/civicrm/contact/search/builder',NULL),(154,'Search Contact','\0','read','/civicrm/contact/search/basicnode',NULL),(155,'Search Contact','\0','read','/civicrm/contact/search/',NULL),(156,'Search Advanced','\0','read','/civicrm/contact/search/advanced',NULL),(157,'Search Group','\0','read','/civicrm/group/search/advanced',NULL),(158,'Search Custom','\0','read','civicrm/contact/search/custom',NULL),(159,'Search Contact','\0','read','/civicrm/contact/search/search',NULL),(160,'Tag Case','\0','update','/civicrm/case/ajax/processtags',NULL),(161,'Tag Add','\0','create','/civicrm/ajax/tag/create',NULL),(162,'Tag Delete','\0','delete','/civicrm/ajax/tag/delete',NULL),(163,'Tag Manage','\0','read','/civicrm/admin/tag',NULL),(164,'Tag Delete','\0','delete','/civicrm/ajax/entity_tag/delete',NULL),(165,'Tag Update','\0','update','/civicrm/ajax/tag/update',NULL),(166,'Tags Merge','\0','update','/civicrm/ajax/mergeTags',NULL),(167,'Mailing Ajax','\0','read','/civicrm/NYSS/AJAX/Mailing',NULL),(168,'Mailing Update','\0','update','/civicrm/mailing/component',NULL),(169,'Mailing View','\0','read','/civicrm/mailing/dilan.nysenate.gov',NULL),(170,'Mailing View','\0','read','/civicrm/mailing',NULL),(171,'Mailing View','\0','read','/civicrm/mailing/view',NULL),(172,'Mailing View Scheduled','\0','read','/civicrm/mailing/browse',NULL),(173,'Mailing View Scheduled','\0','read','/civicrm/mailing/browse/scheduled',NULL),(174,'Mailing Greeting','\0','update','/civicrm/admin/options/postal_greeting',NULL),(175,'Mailing Greeting Settings','\0','read','/civicrm/admin/options/email_greeting',NULL),(176,'Mailing From Settings','\0','read','/civicrm/admin/options/from_email_address',NULL),(177,'Mailing From Settings','\0','read','/civicrm/admin/options/from_email',NULL),(178,'Mailing Archive','\0','update','/civicrm/mailing/browse/archived',NULL),(179,'Mailing Signature ajax','\0','read','/civicrm/ajax/signature',NULL),(180,'Mailing Signature','\0','read','/civicrm/mailing/signiture',NULL),(181,'Mailing Addressee','\0','read','/civicrm/admin/options/addressee',NULL),(182,'Mailing Browse','\0','read','/civicrm/mailing/browse/unscheduled',NULL),(183,'Mailing Report','\0','read','/civicrm/mailing/report',NULL),(184,'Mailing','\0','read','/civicrm/admin/mail',NULL),(185,'Mailing','\0','read','/civicrm/admin/mailSettings',NULL),(186,'Mailing Send','\0','update','/civicrm/mailing/send',NULL),(187,'Mailing Preview','\0','read','/civicrm/mailing/preview',NULL),(188,'Mailing Report ','\0','read','/civicrm/mailing/report/event',NULL),(189,'Mailing Approve ','\0','update','/civicrm/mailing/approve',NULL),(190,'Mailing Body Content','\0','read','/0',NULL),(191,'Mailing View','\0','read','/civicrm/mailing/%3Ciframe%20width=%22420%22%20height=%22315%22%20src=%22//www.youtube.com/embed/lI_zjRSffO0%22%20frameborder=%220%22%20allowfullscreen%3E%3C/iframe%3E',NULL),(192,'Mailing View','\0','read','/civicrm/mailing/GOLDEN%20GATHERING%20SENIOR%20HEALTH%20FAIR:%20This%20Friday,%20October%2018th',NULL),(193,'Mailing View','\0','read','/civicrm/mailing/white',NULL),(194,'Mailing View','\0','read','/civicrm/mailing/sendnode',NULL),(195,'Mailing View','\0','read','/civicrm/mailing/STAND%20UP%20FOR%20REPOWERING%20NRG!%20%20Forward%20to%20Friends,%20Family,%20and%20Neighbors!%20%20Monday,%20July%2015,%202013%206:00%20PM%20%20SUNY%20Fredonia%20Williams%20Center%20280%20Central%20Avenue%20Fredonia,%20New%20York%20%20Pu',NULL),(196,'Mailing View','\0','read','/civicrm/mailing/www.facebook.com/SenatorBettyLittle',NULL),(197,'Mailing View','\0','read','/civicrm/mailing/%3Ciframe%20width=%22560%22%20height=%22315%22%20src=%22//www.youtube.com/embed/1Zup63UYb34%22%20frameborder=%220%22%20allowfullscreen%3E%3C/iframe%3E',NULL),(198,'Mailing Opt-out','\0','update','/civicrm/mailing/optout',NULL),(199,'Mailing Unsubscribe','\0','update','/civicrm/mailing/unsubscribe',NULL),(200,'Mailing View','\0','read','/civicrm/mailing/www.donotcall.gov',NULL),(201,'Mailing View','\0','read','/civicrm/mailing/Bike%20Safety.docx',NULL),(202,'Mailing View','\0','read','/civicrm/mailing/www.savethecenter.net',NULL),(203,'Admin Dashboard','\0','read','/admin',NULL),(204,'Admin Menu','\0','read','/civicrm/ajax/menu',NULL),(205,'Admin Menu Tree','\0','read','/civicrm/ajax/menutree',NULL),(206,'Admin Menu Rebuild','\0','read','/civicrm/admin/menu',NULL),(207,'Admin My Contacts','\0','read','/civicrm/user',NULL),(208,'Admin Workflow Rules','\0','update','/admin/config/workflow/rules',NULL),(209,'Admin Workflow Reaction','\0','update','/admin/config/workflow/rules/reaction/manage',NULL),(210,'Admin Workflow Reaction','\0','update','/admin/config/workflow/rules/reaction/manage/rules_notify_approvers_of_submission/edit/',NULL),(211,'Admin Workflow Rules','\0','update','/admin/config/workflow/rules',NULL),(212,'Admin User Protect','\0','update','/admin/config/people/userprotect',NULL),(213,'Admin User Menu','\0','read','/admin/user/user',NULL),(214,'Admin User Create','\0','create','/civicrm/profile/create',NULL),(215,'Admin User Update','\0','update','/user//edit',NULL),(216,'Admin User','\0','read','/user',NULL),(217,'Admin User Manage','\0','read','/manage/users',NULL),(218,'Admin User List','\0','read','/admin/users',NULL),(219,'Admin User List','\0','read','/people/users',NULL),(220,'Admin User Reset Password ','\0','read','/user/password',NULL),(221,'Admin User Assign Roles','\0','update','/admin/people/permissions/roleassign',NULL),(222,'Admin User Roles List','\0','read','/admin/people/permissions/roles',NULL),(223,'Admin User View','\0','read','/user/',NULL),(224,'Admin User Delete','\0','delete','/user//delete',NULL),(225,'Admin User Delete','\0','delete','/user//delete',NULL),(226,'Admin Users Manage','\0','read','/admin/people',NULL),(227,'Admin User Permissions','\0','read','/admin/people/permissions',NULL),(228,'Admin User Permissions Edit','\0','update','/admin/people/permissions/roles/edit',NULL),(229,'Admin User Permissions','\0','read','/admin/user/permissions',NULL),(230,'Admin User Confirm Delete','\0','read','/user//cancel',NULL),(231,'Admin User Account Settings','\0','read','/admin/config/people/accounts',NULL),(232,'Admin User Settings','\0','read','/admin/config/people',NULL),(233,'Admin User Permissions','\0','read','/admin/people/permissions',NULL),(234,'Admin User Field Settings','\0','read','/admin/config/people/accounts/fields',NULL),(235,'Admin User Display Settings','\0','read','/admin/config/people/accounts/display',NULL),(236,'Admin LDAP Help','\0','read','/admin/config/people/ldap/help',NULL),(237,'Admin LDAP Status','\0','read','/admin/config/people/ldap/help/status',NULL),(238,'Admin LDAP Issues','\0','read','/admin/config/people/ldap/help/issues',NULL),(239,'Admin LDAP Servers','\0','read','/admin/config/people/ldap/servers',NULL),(240,'Admin LDAP Servers edit','\0','update','/admin/config/people/ldap/servers/edit/nyss_ldap',NULL),(241,'Admin LDAP Drupal Role','\0','update','/admin/config/people/ldap/authorization/edit/drupal_role',NULL),(242,'Admin LDAP Users','\0','read','/admin/config/people/ldap',NULL),(243,'Admin LDAP Authorization','\0','read','/admin/config/people/ldap/authorization',NULL),(244,'Admin LDAP Authentication','\0','read','/admin/config/people/ldap/authentication',NULL),(245,'Admin LDAP Config','\0','read','/admin/config/people/ldap/help/watchdog',NULL),(246,'Admin Get Template','\0','read','/civicrm/ajax/template',NULL),(247,'Admin Maintence Mode','\0','read','/admin/config/development/maintenance',NULL),(248,'Admin Load Data','\0','read','/civicrm/nyss/getoutput',NULL),(249,'Admin Load Data','\0','create','/civicrm/nyss/loaddata',NULL),(250,'Admin Load Sample Data','\0','create','/civicrm/nyss/loadsampledata',NULL),(251,'Admin Empty Trash','\0','delete','/civicrm/nyss/deletetrashed',NULL),(252,'Admin Process Trash','\0','delete','/civicrm/nyss/processtrashed',NULL),(253,'Admin Subscription View','\0','delete','/civicrm/nyss/subscription/view',NULL),(254,'Admin Subscription Manage','\0','delete','/civicrm/nyss/subscription/manage',NULL),(255,'Admin Dedupe Rules','\0','read','/civicrm/ajax/dedupeRules',NULL),(256,'Admin Dedupe Contact','\0','read','/civicrm/contact/deduperules',NULL),(257,'Admin Dedupe Address','\0','update','/civicrm/dedupe/dupeaddress',NULL),(258,'Admin Batch','\0','update','/batch',NULL),(259,'Admin Backup','\0','read','/civicrm/admin/job',NULL),(260,'Admin Upgrade','\0','read','/civicrm/upgrade',NULL),(261,'Admin Import/Export Mappings','\0','read','/civicrm/admin/mapping',NULL),(262,'Admin Settings Caches','\0','update','/civicrm/admin/setting/updateConfigBackend',NULL),(263,'Admin Settings SMTP','\0','read','/civicrm/admin/setting/smtp',NULL),(264,'Admin Settings Misc','\0','read','/civicrm/admin/setting/misc',NULL),(265,'Admin Settings Path','\0','read','/civicrm/admin/setting/path',NULL),(266,'Admin Settings Component','\0','read','/civicrm/admin/setting/component',NULL),(267,'Admin Settings Debug','\0','read','/civicrm/admin/setting/debug',NULL),(268,'Admin Settings Maintenance Mode','\0','update','/admin/settings/site-maintenance',NULL),(269,'Admin Settings Drupal Integration','\0','read','/civicrm/admin/setting/uf',NULL),(270,'Admin Settings Relationships','\0','read','/civicrm/admin/setting/mapping',NULL),(271,'Admin Settings Mailing','\0','read','/civicrm/admin/setting/preferences/mailing',NULL),(272,'Admin Settings Appearance ','\0','read','/admin/appearance/settings/Bluebird',NULL),(273,'Admin Message Templates','\0','read','/civicrm/admin/messageTemplates',NULL),(274,'Admin Performance','\0','read','/admin/config/development/performance',NULL),(275,'Admin Config','\0','read','/admin/config',NULL),(276,'Admin Modules','\0','read','/admin/modules',NULL),(277,'Admin File System Config','\0','read','/admin/config/media/file-system',NULL),(278,'Admin Logging','\0','update','/admin/config/development/logging',NULL),(279,'Admin Reports','\0','read','/admin/reports',NULL),(280,'Admin','\0','read','/civicrm/admin',NULL),(281,'Admin Appearance Component','\0','read','/civicrm/admin/component',NULL),(282,'Admin Appearance','\0','read','/admin/appearance',NULL),(283,'Admin Report Job','\0','read','/civicrm/admin/joblog',NULL),(284,'Admin Modules List','\0','read','/admin/modules/list/confirm',NULL),(285,'Admin Report','\0','read','/admin/reports/event',NULL),(286,'Admin Report Status','\0','read','/admin/reports/status',NULL),(287,'Admin Groups','\0','read','/group',NULL),(288,'Admin Block Structure','\0','read','/admin/structure',NULL),(289,'Admin','\0','read','/admin/index',NULL),(290,'Admin Modules','\0','read','/admin/build/modules',NULL),(291,'Admin Modules Remove','\0','delete','/admin/modules/uninstall',NULL),(292,'Admin Report Errors','\0','read','/admin/reports/civicrm_error',NULL),(293,'Report List Template','\0','read','/civicrm/admin/report/template/list',NULL),(294,'Report List Template','\0','read','/civicrm/admin/reports/civicrm_error',NULL),(295,'Report List','\0','read','/civicrm/admin/report',NULL),(296,'Report List','\0','read','/civicrm/admin/reports',NULL),(297,'Report List','\0','read','/civicrm/report',NULL),(298,'Report List','\0','read','/civicrm/report/list',NULL),(299,'Report','\0','read','/civicrm/imap/ajax/getReports',NULL),(300,'Report Proofing','\0','read','/civicrm/logging/proofingreport',NULL),(301,'Report Activity Summary','\0','read','/civicrm/report/activitySummary',NULL),(302,'Report Activity','\0','read','/civicrm/report/activity',NULL),(303,'Report Activity Tag','\0','update','/civicrm/report/activity/tag',NULL),(304,'Report Case Summary','\0','read','/civicrm/report/case/summary',NULL),(305,'Report Case Detail','\0','read','/civicrm/report/case/detail',NULL),(306,'Report Contact Log','\0','read','/civicrm/report/contact/log',NULL),(307,'Report Contact Detail','\0','read','/civicrm/report/contact/detail',NULL),(308,'Report Contact Log Summary','\0','read','/civicrm/report/logging/contact/summary',NULL),(309,'Report Contact Summary','\0','read','/civicrm/report/contact/summary',NULL),(310,'Report DB Errors','\0','read','/admin/reports/dblog',NULL),(311,'Report Signup','\0','read','/signupreports',NULL),(312,'Report Signup Download','\0','read','/signupreports_download',NULL),(313,'Report Mailing Opened','\0','read','/civicrm/report/Mailing/opened',NULL),(314,'Report Mailing Bounce','\0','read','/civicrm/report/Mailing/bounce',NULL),(315,'Report Mailing Summary','\0','read','/civicrm/report/Mailing/summary',NULL),(316,'Report Apachesolr','\0','read','/admin/reports/apachesolr',NULL),(317,'Report Apachesolr','\0','read','/admin/reports/apachesolr/solr',NULL),(318,'Report Error PHP','\0','read','/admin/reports/status/php',NULL),(319,'Report Mailing Detail','\0','read','/civicrm/report/mailing/detail',NULL),(320,'Report','\0','read','/civicrm/report/template/list',NULL),(321,'Report Demographics','\0','read','/civicrm/report/case/demographics',NULL),(322,'Report Timespent','\0','read','/civicrm/report/case/timespent',NULL),(323,'Check Email','\0','read','/civicrm/ajax/checkemail',NULL),(324,'Group Manage','\0','read','/civicrm/group',NULL),(325,'Group Add','\0','create','/civicrm/group/add',NULL),(326,'Group Custom Data','\0','read','/civicrm/admin/custom/group',NULL),(327,'Group Extra Field','\0','read','/civicrm/admin/custom/group/field',NULL),(328,'Group Extra Field','\0','update','/civicrm/admin/custom/group/field/update',NULL),(329,'File Create','\0','create','/file',NULL),(330,'File View ','\0','read','/civicrm/file',NULL),(331,'File Delete','\0','delete','/civicrm/file/delete',NULL),(332,'File Print','\0','read','/nyss_getfile',NULL),(333,'File','\0','read','/civicrm/eee55631bd4b3dd1f8d05bd472985ae3/Body/M2.1.2,OpenElement&cid=image001.jpg@01CE2099.4D3977A0',NULL),(334,'File','\0','read','/civicrm/0a5642c4dc5e579f90e1105fb697a3d3/Body/M1.2,OpenElement&cid=image001.jpg@01CE8DDC.30E94AC0',NULL),(335,'File','\0','read','/civicrm/ajax/pdfFormat',NULL),(336,'Backup Data','\0','create','/backupdata',NULL),(337,'Backup Data','\0','create','/backupData',NULL),(338,'Backup','\0','create','/backup',NULL),(339,'Backup NYSS','\0','create','/nyss_backup',NULL),(340,'Import Data','\0','create','/importData',NULL),(341,'Import Activity','\0','create','/civicrm/import/activity',NULL),(342,'Import Contact','\0','create','/civicrm/import/contact',NULL),(343,'Import','\0','create','/civicrm/import',NULL),(344,'State Counties','\0','read','/civicrm/ajax/jqCounty',NULL),(345,'Get All Cases','\0','read','/civicrm/ajax/getallcases',NULL),(346,'Print Paper Size','\0','read','/civicrm/ajax/paperSize',NULL),(347,'Export Permissions','\0','read','/civicrm/nyss/exportpermissions',NULL),(348,'Status Message','\0','read','/civicrm/ajax/statusmsg',NULL),(349,'Dedupe','\0','update','/civicrm/dedupe/exception',NULL),(350,'No idea','\0','read','/_vti',NULL),(351,'iNotes Proxy','\0','read','/iNotes/Forms85.nsf/iNotes/Proxy',NULL),(352,'iNotes Proxy','\0','read','/iNotes/Forms9.nsf/iNotes/Proxy',NULL),(353,'iNotes Proxy','\0','read','/iNotes/Proxy',NULL),(354,'iNotes Welcome','\0','read','/iNotes/Welcome',NULL),(355,'processDupes','','read','/civicrm/ajax/rest','processDupes'),(356,'Search Auto-Dropdown','','read','/civicrm/ajax/rest','getContactList'),(357,'location','','read','/civicrm/ajax/rest','location'),(358,'Activity Create','','read','/civicrm/ajax/rest','activity'),(359,'Contact Create','','read','/civicrm/ajax/rest','contact'),(360,'Tag Keyword Search','','read','/civicrm/ajax/taglist','296'),(361,'Tag Position Search','','read','/civicrm/ajax/taglist','292'),(362,'Mailing List','','delete','/civicrm/mailing/browse','delete'),(363,'Report Delete','','read','/civicrm/report/instance/','delete'),(364,'Admin User Update','','update','/civicrm/admin/options/from_email_address','update'),(365,'Contact Edited','','create','/civicrm/contact/add','update'),(366,'Contact Create','','create','/civicrm/contact/add','Individual'),(367,'Contact Create Household','','create','/civicrm/contact/add','Household'),(368,'Contact Create Organization','','create','/civicrm/contact/add','Organization');
/*!40000 ALTER TABLE `url` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'analytics'
--
/*!50003 DROP PROCEDURE IF EXISTS `nyss_debug_log` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER PROCEDURE `nyss_debug_log`(IN msg TEXT)
BEGIN
	IF @nyss_debug_flag IS NOT NULL THEN
		BEGIN
			SET @nyss_debug_function_thismsg = IFNULL(msg,'');
			IF @nyss_debug_function_thismsg = '' THEN
				SET @nyss_debug_function_thismsg='No Message Provided';
			END IF;
			SELECT COUNT(*) INTO @nyss_debug_function_table_count
				FROM information_schema.tables
				WHERE table_schema = DATABASE() AND table_name = 'nyss_debug';
			IF IFNULL(@nyss_debug_function_table_count,0) < 1 THEN
				BEGIN
					DROP TABLE IF EXISTS nyss_debug;
				  CREATE TABLE nyss_debug (
						id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
						msg TEXT,
						ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP
					);
				END;
			END IF;
			INSERT INTO nyss_debug (msg) VALUES (@nyss_debug_function_thismsg);
			SET @nyss_debug_function_thismsg = NULL;
			SET @nyss_debug_function_table_count = NULL;
		END;
	END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `upgrade_11` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER PROCEDURE `upgrade_11`()
BEGIN
  DECLARE cur_table_name VARCHAR(255);
  DECLARE cursor_done TINYINT DEFAULT FALSE;
  DECLARE cur1 CURSOR FOR
    SELECT table_name FROM information_schema.tables
    WHERE table_schema=database()
          AND (table_name LIKE 'summary%' OR table_name LIKE 'uniques%' OR table_name='request');
  DECLARE CONTINUE HANDLER FOR 1060 BEGIN CALL nyss_debug_log('Column already exists'); END;
  DECLARE CONTINUE HANDLER FOR 1064 BEGIN CALL nyss_debug_log('Column was not found'); END;
  DECLARE CONTINUE HANDLER FOR 1061 BEGIN CALL nyss_debug_log('Index already exists'); END;
  DECLARE CONTINUE HANDLER FOR 1091 BEGIN CALL nyss_debug_log('Index was not found'); END;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET cursor_done=1;


  SET @nyss_old_debug_value = IFNULL(@nyss_debug_flag,0);

  SET @nyss_debug_flag = 1;


  CALL nyss_debug_log('Opening cursor to add fields location_id and trans_ip');
  OPEN cur1;
  drop_location_loop: LOOP
    FETCH cur1 INTO cur_table_name;
    IF cursor_done THEN LEAVE drop_location_loop; END IF;
    IF IFNULL(cur_table_name,'') != '' THEN
      BEGIN

        CALL nyss_debug_log(CONCAT('Adding field trans_ip on table ',cur_table_name));
        SET @newstmt=CONCAT('ALTER TABLE ',cur_table_name,' ADD trans_ip INT UNSIGNED DEFAULT NULL AFTER remote_ip');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;

        CALL nyss_debug_log(CONCAT('Adding field location_id on table ',cur_table_name));
        SET @newstmt=CONCAT('ALTER TABLE ',cur_table_name,' ADD location_id INT UNSIGNED DEFAULT NULL AFTER trans_ip');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;

        CALL nyss_debug_log(CONCAT('Populating field trans_ip on table ',cur_table_name));
        SET @newstmt=CONCAT('UPDATE ',cur_table_name,' SET trans_ip=INET_ATON(remote_ip)');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;

        CALL nyss_debug_log(CONCAT('Drop index remote_ip on table ',cur_table_name));
        SET @newstmt=CONCAT('ALTER TABLE ',cur_table_name,' DROP INDEX remote_ip');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;

        CALL nyss_debug_log(CONCAT('Add index trans_ip on table ',cur_table_name));
        SET @newstmt=CONCAT('ALTER TABLE ',cur_table_name,' ADD INDEX ',cur_table_name,'__trans_ip (trans_ip)');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;

        CALL nyss_debug_log(CONCAT('Populating field location_id on table ',cur_table_name));
        SET @newstmt=CONCAT('UPDATE ',cur_table_name,
                            ' a, location b SET a.location_id=IFNULL(b.id,1) ',
                            'WHERE a.trans_ip BETWEEN b.ipv4_start AND b.ipv4_end');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;

        CALL nyss_debug_log(CONCAT('Add index location_id on table ',cur_table_name));
        SET @newstmt=CONCAT('ALTER TABLE ',cur_table_name,' ADD INDEX ',cur_table_name,'__location_id (location_id)');
        PREPARE stmt FROM @newstmt;
        EXECUTE stmt;
        DROP PREPARE stmt;
      END;
    END IF;
  END LOOP;
  CLOSE cur1;


  CALL nyss_debug_log('Adding field url_id to table request');
  ALTER TABLE request ADD url_id int unsigned DEFAULT NULL AFTER location_id;
  CALL nyss_debug_log('Adding index on url_id to table request');
  ALTER TABLE request ADD INDEX request__url_id (url_id);

  CALL nyss_debug_log('SP upgrade_11 complete');


  SET @nyss_debug_flag = @nyss_old_debug_value;
  SET @nyss_old_debug_value = NULL;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `all_requests`
--

/*!50001 DROP VIEW IF EXISTS `all_requests`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY DEFINER */
/*!50001 VIEW `all_requests` AS select cast(`a`.`time` as date) AS `mytime`,date_format(`a`.`time`,'%a') AS `weekday`,count(0) AS `total_requests`,sum(if((`a`.`response_code` like '5%'),1,0)) AS `total_bad_requests`,sum(if((not((`a`.`response_code` like '5%'))),1,0)) AS `total_good_requests` from `request` `a` where (`a`.`instance_id` <> 153) group by `mytime` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-09-30 11:34:07
