ALTER TABLE `cars`
    ADD COLUMN `fuel_type`     VARCHAR(30)      DEFAULT NULL AFTER `description`,
    ADD COLUMN `transmission`  VARCHAR(20)      DEFAULT NULL AFTER `fuel_type`,
    ADD COLUMN `mileage`       INT UNSIGNED     DEFAULT NULL AFTER `transmission`,
    ADD COLUMN `color`         VARCHAR(30)      DEFAULT NULL AFTER `mileage`,
    ADD COLUMN `engine`        VARCHAR(50)      DEFAULT NULL AFTER `color`,
    ADD COLUMN `doors`         TINYINT UNSIGNED DEFAULT NULL AFTER `engine`,
    ADD COLUMN `seats`         TINYINT UNSIGNED DEFAULT NULL AFTER `doors`,
    ADD COLUMN `car_condition` VARCHAR(20)      DEFAULT 'Rabljeno' AFTER `seats`;
