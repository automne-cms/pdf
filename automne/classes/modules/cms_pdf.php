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
  * Codename of the module
  */
define("MOD_CMS_PDF_CODENAME", "cms_pdf");

/**
  * Class CMS_module_cms_pdf
  *
  * represent the PDF module.
  *
  * @package CMS
  * @subpackage module
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */
class CMS_module_cms_pdf extends CMS_moduleValidation
{
	const PDF_EXPORT_FILE_PATH_WR = '/pdf/pdf.php';
	const MESSAGE_MOD_CMS_PDF_EXPLANATION = 5;
	
	/**
	  * Module autoload handler
	  *
	  * @param string $classname the classname required for loading
	  * @return string : the file to use for required classname
	  * @access public
	  */
	function load($classname) {
		static $classes;
		if (!isset($classes)) {
			$classes = array(
				/**
				 * Module main classes
				 */
				'cms_pdf_link' 						=> PATH_MODULES_FS.'/'.MOD_CMS_PDF_CODENAME.'/link.php',
				'cms_pdf' 							=> PATH_MODULES_FS.'/'.MOD_CMS_PDF_CODENAME.'/pdf.php',
			);
		}
		$file = '';
		if (isset($classes[io::strtolower($classname)])) {
			$file = $classes[io::strtolower($classname)];
		}
		return $file;
	}
	
	/** 
	  * Get the tags to be treated by this module for the specified treatment mode, visualization mode and object.
	  * @param integer $treatmentMode The current treatment mode (see constants on top of CMS_modulesTags class for accepted values).
	  * @param integer $visualizationMode The current visualization mode (see constants on top of cms_page class for accepted values).
	  * @return array of tags to be treated.
	  * @access public
	  */
	function getWantedTags($treatmentMode, $visualizationMode) 
	{
		$return = array();
		switch ($treatmentMode) {
			case MODULE_TREATMENT_PAGECONTENT_TAGS :
				switch ($visualizationMode) {
					default:
						$return = array (
							"atm-pdf-link" 			=> array("selfClosed" => false, "parameters" => array()),
							"atm-pdf-skip" 			=> array("selfClosed" => false, "parameters" => array()),
						);
					break;
				}
			break;
		}
		return $return;
	}
	
	/** 
	  * Treat given content tag by this module for the specified treatment mode, visualization mode and object.
	  *
	  * @param string $tag The CMS_XMLTag.
	  * @param string $tagContent previous tag content.
	  * @param integer $treatmentMode The current treatment mode (see constants on top of CMS_modulesTags class for accepted values).
	  * @param integer $visualizationMode The current visualization mode (see constants on top of cms_page class for accepted values).
	  * @param object $treatedObject The reference object to treat.
	  * @param array $treatmentParameters : optionnal parameters used for the treatment. Usually an array of objects.
	  * @return string the tag content treated.
	  * @access public
	  */
	function treatWantedTag(&$tag, $tagContent, $treatmentMode, $visualizationMode, &$treatedObject, $treatmentParameters)
	{
		switch ($treatmentMode) {
			case MODULE_TREATMENT_PAGECONTENT_TAGS:
				if (!is_a($treatedObject,"CMS_page")) {
					$this->raiseError('$treatedObject must be a CMS_page object');
					return false;
				}
				switch ($tag->getName()) {
					case 'atm-pdf-skip':
						if ($visualizationMode == PAGE_VISUALMODE_HTML_PUBLIC_INDEXABLE) {
							return '';
						} else {
							return $tag->getInnerContent();
						}
					break;
					case 'atm-pdf-link':
						$linkAttributes = $tag->getAttributes();
						//set default page attribute if not exists
						if (!isset($linkAttributes['page'])) {
							$linkAttributes['page'] = $treatedObject->getID();
						}
						//set default PDF subtitle if not exists
						if (!isset($linkAttributes['subtitle'])) {
							$linkAttributes['subtitle'] = $treatedObject->getURL();
						}
						//set default PDF language if not exists
						if (!isset($linkAttributes['language'])) {
							$linkAttributes['language'] = $treatedObject->getLanguage(true);
						}
						
						//create link reference from attributes
						$ref = md5(serialize($linkAttributes));
						
						//search existing link for reference
						$search = new CMS_pdf_link();
						$results = $search->search(array('ref' => $ref), array(), true, true);
						if ($results) {
							//link already exists, use it
							$link = array_shift($results);
						} else {
							//link does not exists, create it
							$link = new CMS_pdf_link();
							$link->setReference($ref);
							$link->setAttributes($linkAttributes);
							$link->writeToPersistence();
						}
						$tagID = $link->getID();
						//get link template
						$template = $tag->getInnerContent();
						//get page website
						$website = $treatedObject->getWebsite();
						//get website url
						$url = $website->getURL();
						//build pdf url
						$url .= self::PDF_EXPORT_FILE_PATH_WR.'?pdf='.$tagID;
						//store link in module usage
						CMS_module::moduleUsage($treatedObject->getID(), $this->_codename, array('links' => array('id' => $tagID)));
						//create link
						if ($tag->getAttribute("keeprequest") == 'true') {
							return '<?php echo \''.str_replace("{{href}}", $url.'\'.($_SERVER["QUERY_STRING"] ? \'&amp;\'.$_SERVER["QUERY_STRING"] : \'\').\'', str_replace("\\\\'", "\'", str_replace("'", "\'", $template))).'\' ?>';
						} else {
							return str_replace("{{href}}", $url, $template);
						}
					break;
				}
			break;
		}
		return $tagContent;
	}
	
	/**
	  * Return the module code for the specified treatment mode, visualization mode and object.
	  * 
	  * @param mixed $modulesCode the previous modules codes (usually string)
	  * @param integer $treatmentMode The current treatment mode (see constants on top of this file for accepted values).
	  * @param integer $visualizationMode The current visualization mode (see constants on top of cms_page class for accepted values).
	  * @param object $treatedObject The reference object to treat.
	  * @param array $treatmentParameters : optionnal parameters used for the treatment. Usually an array of objects.
	  *
	  * @return string : the module code to add
	  * @access public
	  */
	function getModuleCode($modulesCode, $treatmentMode, $visualizationMode, &$treatedObject, $treatmentParameters) {
		switch ($treatmentMode) {
			case MODULE_TREATMENT_ROWS_EDITION_LABELS :
				$modulesCode[MOD_CMS_PDF_CODENAME] = $treatmentParameters["language"]->getMessage(self::MESSAGE_MOD_CMS_PDF_EXPLANATION, false, MOD_CMS_PDF_CODENAME);
				return $modulesCode;
			break;
			case MODULE_TREATMENT_TEMPLATES_EDITION_LABELS :
				$modulesCode[MOD_CMS_PDF_CODENAME] = $treatmentParameters["language"]->getMessage(self::MESSAGE_MOD_CMS_PDF_EXPLANATION, false, MOD_CMS_PDF_CODENAME);
				return $modulesCode;
			break;
		}
		return $modulesCode;
	}
}
?>