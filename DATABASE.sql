
CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(32) NULL,
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_persian_ci NULL,
  `email` varchar(32) CHARACTER SET utf8 COLLATE utf8_persian_ci NULL,
  `price` varchar(32) NULL,
  `detail` varchar(128) CHARACTER SET utf8 COLLATE utf8_persian_ci NULL,
  `date` varchar(32) NULL,
  `step` varchar(4) NULL,
  `res1` varchar(64) NULL,
  `res2` varchar(64) NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
