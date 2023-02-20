DROP TABLE IF EXISTS `test`;

CREATE TABLE IF NOT EXISTS `test` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `title` varchar(32) DEFAULT NULL,
    `desc` varchar(255) DEFAULT NULL,
    `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;