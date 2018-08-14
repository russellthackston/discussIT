-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 14, 2018 at 02:14 PM
-- Server version: 5.6.40
-- PHP Version: 7.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `IT3234-2018`
--

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `attachmentid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `filename` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `attachmenttypes`
--

CREATE TABLE `attachmenttypes` (
  `attachmenttypeid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `name` varchar(50) NOT NULL,
  `extension` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `attachmenttypes`
--

INSERT INTO `attachmenttypes` (`attachmenttypeid`, `name`, `extension`) VALUES
('1a4a32c2ea1780c455f0c5c9e1f9d0fb', 'PDF', 'pdf'),
('3aac35979991de9d56e5b5a10bac8615', 'Word Document', 'doc'),
('3c010dbff6fcdd006c4ad76a6252b6b5', 'Excel', 'xls'),
('5cc455b30cb3d45a778b7d9f8f80a4ed', 'PNG', 'png'),
('65eb214553b95cc80716ac4ec7faf5ce', 'GIF', 'gif'),
('6e0a407db43040a51c8afcecafe41d4a', 'Excel', 'xlsx'),
('91c72b0a592196f16a2971a209af9d59', 'Word Document', 'docx'),
('afc5af33e349b4d074e970bdf367c075', 'JPG', 'jpeg'),
('bfa3852be3f32586467e5e9f9ee1ff55', 'JPG', 'jpg');

-- --------------------------------------------------------

--
-- Table structure for table `auditlog`
--

CREATE TABLE `auditlog` (
  `auditlogid` int(11) NOT NULL,
  `context` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `logdate` datetime NOT NULL,
  `ipaddress` varchar(15) NOT NULL,
  `userid` varchar(32) DEFAULT NULL COMMENT 'CSPRN',
  `priority` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `commentid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `commenttext` text NOT NULL,
  `commentposted` datetime NOT NULL,
  `commentuserid` varchar(32) DEFAULT NULL COMMENT 'CSPRN',
  `commentthingid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `commentattachmentid` varchar(32) DEFAULT NULL COMMENT 'CSPRN'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `critiques`
--

CREATE TABLE `critiques` (
  `critiqueid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `critiqueuserid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `critiquecommentid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `critiqueposted` datetime NOT NULL,
  `addstodiscussion` tinyint(1) NOT NULL,
  `critiquetext` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `emailvalidation`
--

CREATE TABLE `emailvalidation` (
  `emailvalidationid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `userid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `email` varchar(255) NOT NULL,
  `emailsent` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `passwordreset`
--

CREATE TABLE `passwordreset` (
  `passwordresetid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `userid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `email` varchar(255) NOT NULL,
  `expires` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `registrationcodes`
--

CREATE TABLE `registrationcodes` (
  `registrationcode` varchar(255) NOT NULL,
  `starttime` time NOT NULL,
  `endtime` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `registrationcodes`
--

INSERT INTO `registrationcodes` (`registrationcode`, `starttime`, `endtime`) VALUES
('Fall2018IT3234A', '10:10:00', '11:25:00'),
('Fall2018IT3234B', '14:30:00', '15:45:00'),
('Fall2018IT5236A', '12:20:00', '13:35:00'),
('QualityAssurance', '10:10:00', '11:25:00');

-- --------------------------------------------------------

--
-- Table structure for table `reportcodes`
--

CREATE TABLE `reportcodes` (
  `reportcodeid` int(32) NOT NULL COMMENT 'CSPRN',
  `reportcodename` varchar(50) NOT NULL,
  `moreinfoneeded` tinyint(1) NOT NULL,
  `reporteduserid` varchar(32) NOT NULL COMMENT 'CSPRN'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `reportcodes`
--

INSERT INTO `reportcodes` (`reportcodeid`, `reportcodename`, `moreinfoneeded`, `reporteduserid`) VALUES
(1, 'This is abusive or harassing', 0, ''),
(2, 'This is a duplicate comment', 0, ''),
(3, 'Other', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `rollcall`
--

CREATE TABLE `rollcall` (
  `userid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `callsubmitted` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `studentid` varchar(16) NOT NULL COMMENT 'CSPRN',
  `studentname` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `things`
--

CREATE TABLE `things` (
  `thingid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `thingname` varchar(255) NOT NULL,
  `thingdescription` text NOT NULL,
  `thingcreated` datetime NOT NULL,
  `commentsopendate` datetime NOT NULL,
  `commentsclosedate` datetime NOT NULL,
  `critiquesclosedate` datetime NOT NULL,
  `thinguserid` varchar(32) DEFAULT NULL COMMENT 'CSPRN',
  `thingattachmentid` varchar(32) DEFAULT NULL COMMENT 'CSPRN',
  `thingregistrationcode` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `userregistrations`
--

CREATE TABLE `userregistrations` (
  `userid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `registrationcode` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `userreports`
--

CREATE TABLE `userreports` (
  `userreportid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `userreportcommentid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `userreportreasoncodeid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `moreinfo` varchar(255) DEFAULT NULL,
  `userreportuserid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `reportsubmitted` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `username` varchar(255) NOT NULL,
  `studentid` varchar(16) DEFAULT NULL,
  `passwordhash` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `isadmin` tinyint(1) NOT NULL,
  `emailvalidated` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `usersessions`
--

CREATE TABLE `usersessions` (
  `usersessionid` varchar(50) NOT NULL COMMENT 'CSPRN',
  `userid` varchar(32) NOT NULL COMMENT 'CSPRN',
  `registrationcode` varchar(32) NOT NULL COMMENT 'CSPRN',
  `expires` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`attachmentid`);

--
-- Indexes for table `attachmenttypes`
--
ALTER TABLE `attachmenttypes`
  ADD PRIMARY KEY (`attachmenttypeid`);

--
-- Indexes for table `auditlog`
--
ALTER TABLE `auditlog`
  ADD PRIMARY KEY (`auditlogid`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`commentid`),
  ADD UNIQUE KEY `commentuserid_2` (`commentuserid`,`commentthingid`),
  ADD KEY `commentattachmentid` (`commentattachmentid`),
  ADD KEY `commentuserid` (`commentuserid`),
  ADD KEY `commenttopicid` (`commentthingid`);

--
-- Indexes for table `critiques`
--
ALTER TABLE `critiques`
  ADD PRIMARY KEY (`critiqueid`),
  ADD KEY `critiqueuserid` (`critiqueuserid`),
  ADD KEY `critiquecommentid` (`critiquecommentid`);

--
-- Indexes for table `emailvalidation`
--
ALTER TABLE `emailvalidation`
  ADD PRIMARY KEY (`emailvalidationid`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `passwordreset`
--
ALTER TABLE `passwordreset`
  ADD PRIMARY KEY (`passwordresetid`),
  ADD KEY `userid` (`userid`),
  ADD KEY `emailaddress` (`email`);

--
-- Indexes for table `registrationcodes`
--
ALTER TABLE `registrationcodes`
  ADD PRIMARY KEY (`registrationcode`),
  ADD KEY `registrationcode` (`registrationcode`);

--
-- Indexes for table `reportcodes`
--
ALTER TABLE `reportcodes`
  ADD PRIMARY KEY (`reportcodeid`),
  ADD UNIQUE KEY `reportcodename` (`reportcodename`);

--
-- Indexes for table `rollcall`
--
ALTER TABLE `rollcall`
  ADD PRIMARY KEY (`userid`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`studentid`);

--
-- Indexes for table `things`
--
ALTER TABLE `things`
  ADD PRIMARY KEY (`thingid`),
  ADD KEY `registrationcode` (`thingregistrationcode`),
  ADD KEY `thinguserid` (`thinguserid`),
  ADD KEY `thingattachmentid` (`thingattachmentid`);

--
-- Indexes for table `userregistrations`
--
ALTER TABLE `userregistrations`
  ADD PRIMARY KEY (`userid`,`registrationcode`),
  ADD KEY `registrationcode` (`registrationcode`);

--
-- Indexes for table `userreports`
--
ALTER TABLE `userreports`
  ADD PRIMARY KEY (`userreportid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userid`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `usersessions`
--
ALTER TABLE `usersessions`
  ADD PRIMARY KEY (`usersessionid`,`userid`),
  ADD UNIQUE KEY `usersessionid` (`usersessionid`),
  ADD KEY `userid` (`userid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auditlog`
--
ALTER TABLE `auditlog`
  MODIFY `auditlogid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22755;

--
-- AUTO_INCREMENT for table `reportcodes`
--
ALTER TABLE `reportcodes`
  MODIFY `reportcodeid` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`commentuserid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`commentattachmentid`) REFERENCES `attachments` (`attachmentid`) ON UPDATE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`commentthingid`) REFERENCES `things` (`thingid`) ON UPDATE CASCADE;

--
-- Constraints for table `critiques`
--
ALTER TABLE `critiques`
  ADD CONSTRAINT `critiques_ibfk_1` FOREIGN KEY (`critiquecommentid`) REFERENCES `comments` (`commentid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `passwordreset`
--
ALTER TABLE `passwordreset`
  ADD CONSTRAINT `passwordreset_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE,
  ADD CONSTRAINT `passwordreset_ibfk_2` FOREIGN KEY (`email`) REFERENCES `users` (`email`) ON UPDATE CASCADE;

--
-- Constraints for table `things`
--
ALTER TABLE `things`
  ADD CONSTRAINT `things_ibfk_1` FOREIGN KEY (`thinguserid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE,
  ADD CONSTRAINT `things_ibfk_2` FOREIGN KEY (`thingattachmentid`) REFERENCES `attachments` (`attachmentid`) ON UPDATE CASCADE,
  ADD CONSTRAINT `things_ibfk_3` FOREIGN KEY (`thingregistrationcode`) REFERENCES `registrationcodes` (`registrationcode`) ON UPDATE CASCADE;

--
-- Constraints for table `userregistrations`
--
ALTER TABLE `userregistrations`
  ADD CONSTRAINT `userregistrations_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `userregistrations_ibfk_2` FOREIGN KEY (`registrationcode`) REFERENCES `registrationcodes` (`registrationcode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `usersessions`
--
ALTER TABLE `usersessions`
  ADD CONSTRAINT `usersessions_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
