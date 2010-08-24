/* GOOGLE ANALYTICA NEW REFERRERS TABLES */

CREATE TABLE `ga_nr_report` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `account_name` varchar(255) NOT NULL default '',
    `profile_name` varchar(255) NOT NULL default '',
    `table_id` varchar(255) NOT NULL default '',
    `created_date` datetime NOT NULL default '0000-00-00 00:00:00',
    `min_traffic` int(10) unsigned NOT NULL default '0',
    `download_period` int(10) unsigned NOT NULL default '0',
    `compare_period` int(10) unsigned NOT NULL default '0',
    PRIMARY KEY  (`id`),
    KEY `IDX_CREATED_DATE` (`created_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Google Analytics New Referrers Reports';

CREATE TABLE `ga_nr_referrer` (
    `id`  int(10) unsigned NOT NULL auto_increment,
    `report_id` int(10) unsigned NOT NULL,
    `host` varchar(255) NOT NULL default '',
    `visits` int(10) unsigned NOT NULL,
    PRIMARY KEY  (`id`),
    KEY `FK_REPORT_ID` (`report_id`),
    CONSTRAINT `FK_REPORT_ID` FOREIGN KEY (`report_id`) REFERENCES ga_nr_report (id)
    ON DELETE CASCADE ON UPDATE CASCADE
)  ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Google Analytics New Referrers';