CREATE TABLE `discussion-dev`.`notes` ( 
	`noteid` VARCHAR(32) NOT NULL , 
	`notetext` TEXT NOT NULL , 
	`registrationcode` VARCHAR(32) NOT NULL , 
	`noteorder` INT NOT NULL , 
	PRIMARY KEY (`noteid`)) ENGINE = InnoDB;

