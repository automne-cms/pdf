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
//

/**
  * TCPDF config file
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

define('K_TCPDF_EXTERNAL_CONFIG', true);
define ('K_PATH_MAIN', $_SERVER['DOCUMENT_ROOT'].'/automne/tcpdf/');
define ('K_PATH_URL', '/automne/tcpdf/');
define ('K_PATH_FONTS', K_PATH_MAIN.'fonts/');
define ('K_PATH_CACHE', K_PATH_MAIN.'cache/');
define ('K_PATH_URL_CACHE', K_PATH_URL.'cache/');
define ('K_PATH_IMAGES', K_PATH_MAIN.'images/');
define ('K_BLANK_IMAGE', K_PATH_IMAGES.'_blank.png');

/**
 * page format
 */
if (!defined('PDF_PAGE_FORMAT')) {
	define ('PDF_PAGE_FORMAT', 'A4');
}
/**
 * page orientation (P=portrait, L=landscape)
 */
if (!defined('PDF_PAGE_ORIENTATION')) {
	define ('PDF_PAGE_ORIENTATION', 'P');
}
/**
 * document creator
 */
if (!defined('PDF_CREATOR')) {
	define ('PDF_CREATOR', CMS_grandFather::SYSTEM_LABEL);
}
/**
 * document author
 */
if (!defined('PDF_AUTHOR')) {
	define ('PDF_AUTHOR', CMS_grandFather::SYSTEM_LABEL);
}
/**
 * image logo
 */
if (!defined('PDF_HEADER_LOGO')) {
	define ('PDF_HEADER_LOGO', '../../../img/sydom/logoPDF.gif');
}
/**
 * header logo image width [mm]
 */
if (!defined('PDF_HEADER_LOGO_WIDTH')) {
	define ('PDF_HEADER_LOGO_WIDTH', 12);
}
/**
 *  document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch]
 */
if (!defined('PDF_UNIT')) {
	define ('PDF_UNIT', 'mm');
}
/**
 * header margin
 */
if (!defined('PDF_MARGIN_HEADER')) {
	define ('PDF_MARGIN_HEADER', 5);
}
/**
 * footer margin
 */
if (!defined('PDF_MARGIN_FOOTER')) {
	define ('PDF_MARGIN_FOOTER', 10);
}
/**
 * top margin
 */
if (!defined('PDF_MARGIN_TOP')) {
	define ('PDF_MARGIN_TOP', 27);
}
/**
 * bottom margin
 */
if (!defined('PDF_MARGIN_BOTTOM')) {
	define ('PDF_MARGIN_BOTTOM', 25);
}
/**
 * left margin
 */
if (!defined('PDF_MARGIN_LEFT')) {
	define ('PDF_MARGIN_LEFT', 15);
}
/**
 * right margin
 */
if (!defined('PDF_MARGIN_RIGHT')) {
	define ('PDF_MARGIN_RIGHT', 15);
}
/**
 * default main font name
 */
if (!defined('PDF_FONT_NAME_MAIN')) {
	define ('PDF_FONT_NAME_MAIN', 'helvetica');
}
/**
 * default main font size
 */
if (!defined('PDF_FONT_SIZE_MAIN')) {
	define ('PDF_FONT_SIZE_MAIN', 10);
}
/**
 * default data font name
 */
if (!defined('PDF_FONT_NAME_DATA')) {
	define ('PDF_FONT_NAME_DATA', 'helvetica');
}
/**
 * default data font size
 */
if (!defined('PDF_FONT_SIZE_DATA')) {
	define ('PDF_FONT_SIZE_DATA', 8);
}
/**
 * default monospaced font name
 */
if (!defined('PDF_FONT_MONOSPACED')) {
	define ('PDF_FONT_MONOSPACED', 'courier');
}
/**
 * ratio used to adjust the conversion of pixels to user units
 */
if (!defined('PDF_IMAGE_SCALE_RATIO')) {
	define ('PDF_IMAGE_SCALE_RATIO', 3/2);
}
/**
 * magnification factor for titles
 */
if (!defined('HEAD_MAGNIFICATION')) {
	define('HEAD_MAGNIFICATION', 1.1);
}
/**
 * height of cell repect font height
 */
if (!defined('K_CELL_HEIGHT_RATIO')) {
	define('K_CELL_HEIGHT_RATIO', 1.25);
}
/**
 * title magnification respect main font size
 */
if (!defined('K_TITLE_MAGNIFICATION')) {
	define('K_TITLE_MAGNIFICATION', 1.3);
}
/**
 * reduction factor for small font
 */
if (!defined('K_SMALL_RATIO')) {
	define('K_SMALL_RATIO', 2/3);
}

/**
 * reduction factor for small font
 */
if (!defined('PDF_DEFAULT_CACHE_LENGTH')) {
	define('PDF_DEFAULT_CACHE_LENGTH', 3600);
}
?>