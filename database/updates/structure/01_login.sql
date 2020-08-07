CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `can_edit` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_EMAIL` (`email`),
  UNIQUE KEY `user_NAME` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO laingame.`user`
(email, name, password, can_edit)
VALUES('laingame.net@gmail.com', 'admin', '', 1);

ALTER TABLE laingame.translation ADD edited_by INT NOT NULL;
ALTER TABLE laingame.translation_history ADD edited_by INT NOT NULL;

ALTER TABLE laingame.translation CHANGE id_file id_block int NOT NULL;
ALTER TABLE laingame.translation_history CHANGE id_file id_block int NOT NULL;
