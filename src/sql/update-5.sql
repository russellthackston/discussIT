ALTER TABLE `users` ADD `testaccount` BOOLEAN NOT NULL AFTER `emailvalidated`;
UPDATE users SET testaccount = 0;
UPDATE users SET testaccount = 1 WHERE studentid < 900000000;
