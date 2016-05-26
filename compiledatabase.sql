/*
Navicat MySQL Data Transfer

Source Server         : localwebexci
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : webexci

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2016-05-26 16:54:10
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ec_job
-- ----------------------------
DROP TABLE IF EXISTS `ec_job`;
CREATE TABLE `ec_job` (
  `JOBID` varchar(40) NOT NULL,
  `JOBNAME` varchar(100) DEFAULT NULL,
  `COMPONENTNAME` varchar(100) DEFAULT NULL,
  `BUILDNUMBER` varchar(20) DEFAULT NULL,
  `VERSION` varchar(10) DEFAULT NULL,
  `BUILDOPTION` int(11) DEFAULT NULL,
  `WAITTIME` time DEFAULT NULL,
  `COMMITID` varchar(50) DEFAULT NULL,
  `STASHPROJECT` varchar(60) DEFAULT NULL,
  `REPOSITORY` varchar(60) DEFAULT NULL,
  `BRANCH` varchar(60) DEFAULT NULL,
  `FEATUREID` varchar(20) DEFAULT NULL,
  `STATUS` int(11) DEFAULT NULL,
  `LAUNCHUSER` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`JOBID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ec_job
-- ----------------------------

-- ----------------------------
-- Table structure for ec_job_log
-- ----------------------------
DROP TABLE IF EXISTS `ec_job_log`;
CREATE TABLE `ec_job_log` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `JOBID` varchar(40) NOT NULL,
  `LOGTIME` datetime DEFAULT NULL,
  `LOG` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `FK_EC_JOB_L_REFERENCE_EC_JOB` (`JOBID`),
  CONSTRAINT `FK_EC_JOB_L_REFERENCE_EC_JOB` FOREIGN KEY (`JOBID`) REFERENCES `ec_job` (`JOBID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ec_job_log
-- ----------------------------

-- ----------------------------
-- Table structure for ec_jobstep
-- ----------------------------
DROP TABLE IF EXISTS `ec_jobstep`;
CREATE TABLE `ec_jobstep` (
  `STEPID` varchar(40) NOT NULL,
  `JOBID` varchar(40) NOT NULL,
  `STEPSEQUENCEID` int(10) unsigned NOT NULL,
  `WAITTIME` time DEFAULT NULL,
  `RESOURCENAME` varchar(200) DEFAULT NULL,
  `STATUS` int(11) DEFAULT NULL,
  PRIMARY KEY (`JOBID`,`STEPSEQUENCEID`),
  KEY `FK_EC_STEPJ_REFERENCE_EC_JOB` (`JOBID`),
  CONSTRAINT `FK_EC_STEPJ_REFERENCE_EC_JOB` FOREIGN KEY (`JOBID`) REFERENCES `ec_job` (`JOBID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ec_jobstep
-- ----------------------------

-- ----------------------------
-- Table structure for ec_jobstep_log
-- ----------------------------
DROP TABLE IF EXISTS `ec_jobstep_log`;
CREATE TABLE `ec_jobstep_log` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `JOBID` varchar(40) NOT NULL,
  `STEPSEQUENCEID` int(10) unsigned NOT NULL,
  `LOGTIME` datetime DEFAULT NULL,
  `LOG` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `FK_EC_STEPJ_REFERENCE_EC_STEPJ` (`JOBID`),
  CONSTRAINT `FK_EC_STEPJ_REFERENCE_EC_STEPJ` FOREIGN KEY (`JOBID`) REFERENCES `ec_jobstep` (`JOBID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ec_jobstep_log
-- ----------------------------

-- ----------------------------
-- Table structure for ec_stepsequence
-- ----------------------------
DROP TABLE IF EXISTS `ec_stepsequence`;
CREATE TABLE `ec_stepsequence` (
  `STEPSEQUENCEID` int(10) unsigned NOT NULL,
  `STEPINDEX` int(10) unsigned DEFAULT NULL,
  `STEPNAME` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`STEPSEQUENCEID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ec_stepsequence
-- ----------------------------
INSERT INTO `ec_stepsequence` VALUES ('1', '1', 'checkout');
INSERT INTO `ec_stepsequence` VALUES ('2', '2', 'Build');
INSERT INTO `ec_stepsequence` VALUES ('3', '3', 'UT');
INSERT INTO `ec_stepsequence` VALUES ('4', '4', 'TA');
INSERT INTO `ec_stepsequence` VALUES ('5', '5', 'merge');

-- ----------------------------
-- Table structure for parameters
-- ----------------------------
DROP TABLE IF EXISTS `parameters`;
CREATE TABLE `parameters` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PARAMKEY` int(11) DEFAULT NULL,
  `PARAMVALUE` varchar(50) DEFAULT NULL,
  `PARAMGROUP` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of parameters
-- ----------------------------
INSERT INTO `parameters` VALUES ('1', '1', 'open', 'SCMPullRequestStatus');
INSERT INTO `parameters` VALUES ('2', '4', 'merge', 'SCMPullRequestStatus');
INSERT INTO `parameters` VALUES ('3', '3', 'decline', 'SCMPullRequestStatus');
INSERT INTO `parameters` VALUES ('4', '2', 'approve', 'SCMPullRequestStatus');
INSERT INTO `parameters` VALUES ('5', '3', 'delete', 'SCMCommitType');
INSERT INTO `parameters` VALUES ('6', '2', 'update', 'SCMCommitType');
INSERT INTO `parameters` VALUES ('7', '1', 'add', 'SCMCommitType');
INSERT INTO `parameters` VALUES ('8', '4', 'warning', 'ECJobStepStatus');
INSERT INTO `parameters` VALUES ('9', '1', 'running', 'ECJobStepStatus');
INSERT INTO `parameters` VALUES ('10', '3', 'error', 'ECJobStepStatus');
INSERT INTO `parameters` VALUES ('11', '2', 'success', 'ECJobStepStatus');
INSERT INTO `parameters` VALUES ('12', '1', 'running', 'ECJobStatus');
INSERT INTO `parameters` VALUES ('13', '2', 'success', 'ECJobStatus');
INSERT INTO `parameters` VALUES ('14', '4', 'warning', 'ECJobStatus');
INSERT INTO `parameters` VALUES ('15', '3', 'error', 'ECJobStatus');
INSERT INTO `parameters` VALUES ('16', '2', 'feature', 'ECJobBuildOption');
INSERT INTO `parameters` VALUES ('17', '3', 'official', 'ECJobBuildOption');
INSERT INTO `parameters` VALUES ('18', '1', 'gate', 'ECJobBuildOption');
INSERT INTO `parameters` VALUES ('19', '4', 'coverage', 'ECJobBuildOption');
INSERT INTO `parameters` VALUES ('20', '5', 'coverity', 'ECJobBuildOption');
INSERT INTO `parameters` VALUES ('21', '6', 'feature_merge', 'ECJobBuildOption');

-- ----------------------------
-- Table structure for scm_commit
-- ----------------------------
DROP TABLE IF EXISTS `scm_commit`;
CREATE TABLE `scm_commit` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `COMMITID` varchar(50) DEFAULT NULL,
  `STASHPROJECT` varchar(60) DEFAULT NULL,
  `REPOSITORY` varchar(60) DEFAULT NULL,
  `BRANCH` varchar(60) DEFAULT NULL,
  `TIMESTAMP` datetime DEFAULT NULL,
  `TYPE` int(11) DEFAULT NULL,
  `AUTHOR` varchar(200) DEFAULT NULL,
  `EMAIL` varchar(200) DEFAULT NULL,
  `COMMITS` text,
  `LOGTIME` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of scm_commit
-- ----------------------------

-- ----------------------------
-- Table structure for scm_pullrequest
-- ----------------------------
DROP TABLE IF EXISTS `scm_pullrequest`;
CREATE TABLE `scm_pullrequest` (
  `COMMITID` varchar(50) NOT NULL,
  `STASHPROJECT` varchar(60) NOT NULL,
  `REPOSITORY` varchar(60) NOT NULL,
  `SOURCEBRANCH` varchar(60) DEFAULT NULL,
  `TARGETBRANCH` varchar(60) DEFAULT NULL,
  `PULLREQUESTID` int(11) NOT NULL,
  `REVIEWERS` varchar(200) DEFAULT NULL,
  `STATUS` int(11) DEFAULT NULL,
  `APPROVER` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`PULLREQUESTID`,`STASHPROJECT`,`REPOSITORY`,`COMMITID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of scm_pullrequest
-- ----------------------------

-- ----------------------------
-- Table structure for scm_pullrequest_log
-- ----------------------------
DROP TABLE IF EXISTS `scm_pullrequest_log`;
CREATE TABLE `scm_pullrequest_log` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PULLREQUESTID` int(11) NOT NULL,
  `REPOSITORY` varchar(60) NOT NULL,
  `STASHPROJECT` varchar(60) NOT NULL,
  `LOGTIME` datetime DEFAULT NULL,
  `LOG` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of scm_pullrequest_log
-- ----------------------------

-- ----------------------------
-- Table structure for sys_log
-- ----------------------------
DROP TABLE IF EXISTS `sys_log`;
CREATE TABLE `sys_log` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `KEY1` varchar(60) COLLATE latin1_general_ci DEFAULT NULL,
  `KEY2` varchar(60) COLLATE latin1_general_ci DEFAULT NULL,
  `KEY3` varchar(60) COLLATE latin1_general_ci DEFAULT NULL,
  `KEY4` varchar(60) COLLATE latin1_general_ci DEFAULT NULL,
  `LOGTIME` datetime DEFAULT NULL,
  `LOG` text COLLATE latin1_general_ci,
  `TABLENAME` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `FUNCNAME` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Records of sys_log
-- ----------------------------
