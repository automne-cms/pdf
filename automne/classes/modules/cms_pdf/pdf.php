<?php
// +----------------------------------------------------------------------+
// | Automne (TM)														  |
// +----------------------------------------------------------------------+
// | Copyright (c) 2000-2010 WS Interactive								  |
// +----------------------------------------------------------------------+
// | Automne is subject to version 2.0 or above of the GPL license.		  |
// | The license text is bundled with this package in the file			  |
// | LICENSE-GPL, and is available through the world-wide-web at		  |
// | http://www.gnu.org/copyleft/gpl.html.								  |
// +----------------------------------------------------------------------+
// | Author: Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>      |
// +----------------------------------------------------------------------+

/**
  * CMS_pdf Class extends TCPDF
  * Extend the TCPDF class to create custom Header and Footer
  *
  * @package CMS
  * @subpackage CMS_resource
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

require_once(PATH_MAIN_FS.'/tcpdf/config.php');
require_once(PATH_MAIN_FS.'/tcpdf/config/lang/en.php');
require_once(PATH_MAIN_FS.'/tcpdf/tcpdf.php');

class CMS_pdf extends TCPDF {
	const MESSAGE_FOOTER_PAGE = 2;
	const MESSAGE_CREATED_ON = 4;
	
	/**
	  * PDF language
	  * @var CMS_language
	  * @access private
	  */
	var $_language;
	
	/**
	  * Constructor.
	  * initializes PDF object. Extends TCPDF constructor to add language
	  *
	  * @return void
	  * @access public
	  */
	public function __construct($language, $orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false) {
		$this->_language = $language;
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);
	}
	
	/**
	  * Set PDF footer
	  *
	  * @return void
	  * @access public
	  */
	public function Footer() {
		if (is_object($this->_language)) {
			// Position at 1.5 cm from bottom
			$this->SetY(-15);
			// Set font
			$this->SetFont('helvetica', 'I', 8);
			// Page number
			$this->Cell(0, 10, $this->_language->getMessage(self::MESSAGE_FOOTER_PAGE, false, MOD_CMS_PDF_CODENAME).' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
			// Set font
			$this->SetFont('helvetica', 'I', 6);
			$this->Ln(5);
			$this->Cell(0, 10, $this->_language->getMessage(self::MESSAGE_CREATED_ON, array(date($this->_language->getDateFormat().' H:i:s')), MOD_CMS_PDF_CODENAME), 0, 0, 'R');
		}
	}
}
?>