ALTER TABLE `users` ADD `timezone` VARCHAR(30) NOT NULL AFTER `emailvalidated`;
UPDATE users SET timezone = 'America/New_York';
