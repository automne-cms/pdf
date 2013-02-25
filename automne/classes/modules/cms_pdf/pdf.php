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
	const PDF_DEFAULT_CACHE_LENGTH = PDF_DEFAULT_CACHE_LENGTH;
	
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
			$footerPage = $this->_language->getMessage(self::MESSAGE_FOOTER_PAGE, false, MOD_CMS_PDF_CODENAME);
			$footerDate = $this->_language->getMessage(self::MESSAGE_CREATED_ON, array(date($this->_language->getDateFormat().' H:i:s')), MOD_CMS_PDF_CODENAME);
			if (strtolower(APPLICATION_DEFAULT_ENCODING) != 'utf-8') {
				$footerPage = utf8_encode($footerPage);
				$footerDate = utf8_encode($footerDate);
			}
			
			// Position at 1.5 cm from bottom
			$this->SetY(-15);
			// Set font
			$this->SetFont('helvetica', 'I', 8);
			// Page number
			$this->Cell(0, 10, $footerPage.' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
			// Set font
			$this->SetFont('helvetica', 'I', 6);
			$this->Ln(5);
			$this->Cell(0, 10, $footerDate, 0, 0, 'R');
		}
	}
	
	/**
		* This method is automatically called in case of fatal error; it simply outputs the message and halts the execution. An inherited class may override it to customize the error handling but should always halt the script, or the resulting document would probably be invalid.
		* 2004-06-11 :: Nicola Asuni : changed bold tag with strong
		* @param string $msg The error message
		* @access public
		* @since 1.0
		*/
	public function Error($msg) {
		CMS_grandFather::raiseError($msg);
		
		// unset all class variables
		//$this->_destroy(true);
		// exit program and print error
		//die('<strong>TCPDF ERROR: </strong>'.$msg);
	}
}
?>