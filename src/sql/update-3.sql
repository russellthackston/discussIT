CREATE TABLE rollcallarchive (
  rolltaken datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  userid varchar(32) NOT NULL COMMENT 'CSPRN',
  studentid varchar(16) DEFAULT NULL,
  studentname varchar(255),
  present int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
