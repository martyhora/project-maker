CREATE TABLE `user` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `password` CHAR(60) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `username` (`username`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

INSERT INTO `user` (`id`, `username`, `password`, `name`) VALUES
    (1, 'admin', '$2a$07$4$$$$$$$$$$$$$$$$$$$$.9HtHO1j5P16O6kyrKLlZ2iwOVgDsKba', 'Administrátor');
