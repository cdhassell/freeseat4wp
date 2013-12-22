CREATE TABLE IF NOT EXISTS `postpay_keys` (
  `randkey` int(10) unsigned NOT NULL DEFAULT '0',
  `email` varchar(254) NOT NULL DEFAULT '',
  `expiration` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`randkey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
