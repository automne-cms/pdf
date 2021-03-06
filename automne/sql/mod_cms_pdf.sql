##
## Contains declaration for module cms_pdf installation : 
## All table creation (mandatory)
##

# --------------------------------------------------------

--
-- Structure de la table `mod_cms_pdf`
--

DROP TABLE IF EXISTS `mod_cms_pdf`;
CREATE TABLE `mod_cms_pdf` (
  `id_pdf` int(11) unsigned NOT NULL auto_increment,
  `ref_pdf` varchar(255) NOT NULL,
  `attributes_pdf` text NOT NULL,
  PRIMARY KEY  (`id_pdf`),
  KEY `ref_pdf` (`ref_pdf`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

#
# Contenu de la table `modules`
#

INSERT INTO `modules` (`id_mod`, `label_mod`, `codename_mod`, `administrationFrontend_mod`, `hasParameters_mod`, `isPolymod_mod`) VALUES ('', 1, 'cms_pdf', '', 0, 0);