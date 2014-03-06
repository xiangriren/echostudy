<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20140102104518 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->addSQL("
				CREATE TABLE IF NOT EXISTS `test_result` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`itemId` int(11) NOT NULL COMMENT '试卷题目id',
		`testId` int(11) NOT NULL,
		`userId` int(11) NOT NULL,
		`score` float(10,1) NOT NULL DEFAULT '0.0',
		`answer` text NOT NULL,
		`teacherSay` text NOT NULL,
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
    		");

    }

    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs

    }
}