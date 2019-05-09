CREATE TABLE `player`(
  `name` VARCHAR(2) PRIMARY KEY,
  `full` VARCHAR(45),
  `season` INTEGER
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;
CREATE TABLE `team`(
  `id` INTEGER PRIMARY KEY AUTO_INCREMENT,
  `defender` VARCHAR(2),
  `attacker` VARCHAR(2),
  FOREIGN KEY(`defender`) REFERENCES `player`(`name`),
  FOREIGN KEY(`attacker`) REFERENCES `player`(`name`)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;
CREATE TABLE `match`(
  `id` INTEGER PRIMARY KEY AUTO_INCREMENT,
  `white_team` INTEGER,
  `red_team` INTEGER,
  `white_score` INTEGER,
  `red_score` INTEGER,
  `season` INTEGER,
  `date` TEXT,
  FOREIGN KEY(`white_team`) REFERENCES `team`(`id`),
  FOREIGN KEY(`red_team`) REFERENCES `team`(`id`)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;
