DROP table `callouts` ;
DROP table `finished_callouts` ;
CREATE TABLE `callouts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `number_to_dial` varchar(256) default NULL,
  `identity`  varchar(256) default NULL,
  `card_no`   varchar(256) default NULL,
  `scheduled_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dialed_on` datetime DEFAULT NULL,
  `message`   varchar(256) default NULL,
  `uniqueid`  varchar(256) default NULL,
  `tries`  int(2) unsigned NOT NULL default 0,
  `status` char(1) default 'Q',
  `answer1` varchar(256) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `finished_callouts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `number_to_dial` varchar(256) default NULL,
  `identity`  varchar(256) default NULL,
  `card_no`   varchar(256) default NULL,
  `scheduled_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dialed_on` datetime DEFAULT NULL,
  `message`   varchar(256) default NULL,
  `uniqueid`  varchar(256) default NULL,
  `tries`  int(2) unsigned NOT NULL default 0,
  `status` char(1) default 'Q',
  `answer1` varchar(256) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

