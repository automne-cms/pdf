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
  * CMS_pdf_link Class extends CMS_superResource
  * This file represent a pdf link and all associated datas
  *
  * @package CMS
  * @subpackage CMS_resource
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */
  
class CMS_pdf_link extends CMS_superResource
{
	/**
	  * DB id
	  * @var integer
	  * @access private
	  */
	protected $_ID;
	
	/**
	  * Class Name for errors display
	  * @var string
	  * @access private
	  */
	protected $_className;
	
	/**
	  * Table name for sql queries 
	  * if class use resource, Table name must be without _edited, _deleted or _public, only the prefix
	  * @var string
	  * @access private
	  */
	protected $_tableName = 	'mod_cms_pdf';
	
	/**
	  * Columns name suffix
	  * @var string
	  * @access private
	  */
	protected $_tableSufix =	'_pdf';
	
	/**
	  * the module codename
	  * @var string
	  * @access private
	  */
	protected $_moduleCodename =	'cms_pdf';
	
	/**
	  * Table(s) fieldname and type
	  * /!\ MUST BE IN THE SAME ORDER OF DATABASE COLUMNS /!\
	  * @var multidimentional array (fieldname => array (field_type, field_default_value))
	  *  - fieldname is the database columns name WITHOUT SUFFIX
	  *  - fieldtype can be : 
	  *		resource : internal useage, Automne Resource.
	  *		string : use with getString and setString
	  *		html : use with getString and setString
	  *		email : use with getString and setString
	  *		integer : use with getInteger and setInteger
	  *		positiveInteger : use with getInteger and setInteger
	  *		boolean : use with getBoolean and SetBoolean
	  *		date : use with getTheDate and setDate. Create a CMS_date object. Default value can be used to launch a method of the object.
	  *		image : use with getImagePath and setImage
	  *		file : use with getFilePath and setFile
	  *		order : use with getOrder, setOrder, getOrderMax, moveUp, moveDown, moveTo
	  *		internalLink : use with getLinkType, getLink, setLink 	/!\ fieldname must be internalSomething /!\
	  *		externalLink : use with getLinkType, getLink, setLink 	/!\ fieldname must be externalSomething /!\
	  *		linkType : use with getLinkType, getLink, setLink 		/!\ fieldname must be somethingType 	/!\
	  *		CMS_className : use with getObject and setObject. Default value can be used to launch a method of the object.
	  *  - fielvalue is the default value
	  * @access private
	  */
	  
	protected $_tableData = array(
							'ref' 			=> array("string"				,""),
							'attributes' 	=> array("html"					,""),
							);
	
	/**
	  * Constructor.
	  * initializes object if the id is given.
	  *
	  * @param integer $id DB id
	  * @param boolean $public wich type of data (default=false : edited)
	  * @return void
	  * @access public
	  */
	function __construct($id = 0,$public=false)
	{
		//set class name
		$this->_className = get_class($this);
		//initialize super-class
		parent::__construct($id,$public);
	}
	
	/**
	  * Set link reference
	  *
	  * @param string $ref the link reference to set
	  * @return boolean
	  * @access public
	  */
	function setReference($ref) {
		return $this->setString('ref', $ref);
	}
	
	/**
	  * Get link reference
	  *
	  * @return string : the link reference
	  * @access public
	  */
	function getReference() {
		return $this->getString('ref');
	}
	
	/**
	  * Set link attributes
	  *
	  * @param array $attributes the link attributes to set
	  * @return boolean
	  * @access public
	  */
	function setAttributes($attributes) {
		if (!is_array($attributes)) {
			$this->raiseError('Link attributes must be an array');
			return false;
		}
		//rewrite boolean attributes
		foreach ($attributes as $key => $value) {
			if ($value == 'true' || $value === 1 || $value == '1') {
				$attributes[$key] = true;
			}
			if ($value == 'false' || $value === 0 || $value == '0') {
				$attributes[$key] = false;
			}
		}
		return $this->setString('attributes', serialize($attributes));
	}
	
	/**
	  * Get link attributes
	  *
	  * @return array : the link attributes
	  * @access public
	  */
	function getAttributes() {
		return unserialize($this->getString('attributes'));
	}
}
?>