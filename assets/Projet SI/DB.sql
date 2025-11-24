CREATE DATABASE IF NOT EXISTS `gestion_events` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gestion_events`;

CREATE TABLE `utilisateurs` (
  `id_user` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `mdp` VARCHAR(255) NOT NULL,
  `role` ENUM('user','admin','organisateur') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `evenements` (
  `id_event` INT(11) NOT NULL AUTO_INCREMENT,
  `titre` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `date_event` DATETIME NOT NULL,
  `lieu` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `inscriptions` (
  `id_inscription` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) NOT NULL,
  `id_event` INT(11) NOT NULL,
  PRIMARY KEY (`id_inscription`),
  KEY `fk_user_inscrit` (`id_user`),
  KEY `fk_event_inscrit` (`id_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `organisateurs_evenements` (
  `id_user` INT(11) NOT NULL COMMENT 'Clé étrangère vers utilisateurs',
  `id_event` INT(11) NOT NULL COMMENT 'Clé étrangère vers evenements',
  PRIMARY KEY (`id_user`, `id_event`) COMMENT 'Clé primaire composite'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contraintes pour `inscriptions`
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `fk_event_inscrit` FOREIGN KEY (`id_event`) REFERENCES `evenements` (`id_event`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_inscrit` FOREIGN KEY (`id_user`) REFERENCES `utilisateurs` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Contraintes pour la nouvelle table `organisateurs_evenements`
ALTER TABLE `organisateurs_evenements`
  ADD CONSTRAINT `fk_event_organisateur` FOREIGN KEY (`id_event`) REFERENCES `evenements` (`id_event`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_organisateur` FOREIGN KEY (`id_user`) REFERENCES `utilisateurs` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;