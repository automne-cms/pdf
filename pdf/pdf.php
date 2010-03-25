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
  * PDF generation file
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

define("ENABLE_HTML_COMPRESSION", false);
require_once($_SERVER["DOCUMENT_ROOT"]."/cms_rc_frontend.php");

define('MESSAGE_TABLE_OF_CONTENT', 3);
$filename = 'pdf.pdf';

$pdfId = io::request('pdf', 'io::isPositiveInteger');

if (!$pdfId) {
	die('Missing PDF id ...');
}

//Get link from pdf ID
$link = new CMS_pdf_link($pdfId);

if (!$link || $link->hasError()) {
	die('Unknown PDF id ...');
}

/**
 * Get all attributes
 * Supported attributes :
 * title : string : PDF title
 * subtitle : string : subtile for PDF (default : PDF source URL)
 * page : integer : printed page content
 * pages : string : list of ids of printed pages contents (comma separated)
 * exclude : string : list of ids of excluded pages (comma separated)
 * subpages : boolean : print subpages of selected page (default : false)
 * crosswebsite : boolean : does subpages stop at websites (default : false : stop at website)
 * maxlevel : integer :  max level for subpages (default : no limit)
 * language : string : language code to use
 * toc : boolean : add TOC on generated PDF (default : true for pdf with multiple pages, false, if PDF is for one page only)
 * cache : integer : cache time in seconds (default : 3600)
 * keeprequest : boolean : keep original page request for link to this file (default : false)
*/
$attributes = $link->getAttributes();

//Set PDF language
$cms_language = new CMS_language($attributes['language']);

//set excluded pages if any
$excluded = array();
if (isset($attributes['excluded'])) {
	$excludedPages = explode(',', str_replace(';',',', $attributes['excluded']));
	foreach ($excludedPages as $key => $excludedPage) {
		if (io::isPositiveInteger(trim($excludedPage))) {
			$excluded[trim($excludedPage)] = trim($excludedPage);
		}
	}
}

// ---------------------------------------------------------
// Grab Pages to export
// ---------------------------------------------------------
$pages = array();
if (isset($attributes['page']) && (!isset($attributes['subpages']) || !$attributes['subpages']) && (!isset($attributes['pages']) || !$attributes['pages'])) {
	//Single Page
	if (io::isPositiveInteger($attributes['page'])) {
		$pages[$attributes['page']] = $attributes['page'];
	}
} elseif (isset($attributes['page']) && isset($attributes['subpages']) && $attributes['subpages']) {
	//Little recursive function to build pages tree
	function buildRecursiveTargets($pageID, $excluded = array(), $level = 0, $crosswebsite = false, $maxlevel = false) {
		$targets=array();
		$recursiveTargets = array();
		$targets_temp = CMS_tree::getSiblings($pageID, true, false);
		if ($targets_temp && is_array($targets_temp)) {
			foreach ($targets_temp as $aTarget_temp) {
				if (!isset($excluded[$aTarget_temp]) && ($crosswebsite || !CMS_websitesCatalog::isWebsiteRoot($aTarget_temp))) {
					//construct targets array
					$targets[$aTarget_temp] = $aTarget_temp;
					if (!$maxlevel || ($level+1) < $maxlevel ) {
						//construct recursive targets array and array of cms_pages objects
						$returnedDatas = buildRecursiveTargets($aTarget_temp, $excluded, $level+1, $crosswebsite, $maxlevel);
						$targets = $targets + $returnedDatas["targets"];
						$recursiveTargets[$aTarget_temp] = $returnedDatas["recursiveTree"];
					}
				}
			}
		}
		return array("recursiveTree" => $recursiveTargets, "targets" => $targets);
	}
	
	//Page and all subpages
	if (io::isPositiveInteger($attributes['page'])) {
		$crosswebsite = isset($attributes['crosswebsite']) ? $attributes['crosswebsite'] : false;
		$maxlevel = (isset($attributes['maxlevel']) && io::isPositiveInteger($attributes['maxlevel'])) ? $attributes['maxlevel'] : false;
		//build pages tree datas
		$treeInfos = buildRecursiveTargets($attributes['page'], $excluded, 0, $crosswebsite, $maxlevel);
		
		//set pages datas
		$pages = $treeInfos['targets'];
		$pages[$attributes['page']] = $attributes['page'];
		//set tree datas
		$tree = array();
		$tree[$attributes['page']] = $treeInfos['recursiveTree'];
	}
} elseif (isset($attributes['pages']) && $attributes['pages']) {
	//Some specific pages
	$pages = array();
	if (isset($attributes['pages'])) {
		$pagesIds = explode(',', str_replace(';',',', $attributes['pages']));
		foreach ($pagesIds as $key => $pagesId) {
			if (io::isPositiveInteger(trim($pagesId)) && !isset($excluded[trim($pagesId)])) {
				$pages[trim($pagesId)] = trim($pagesId);
			}
		}
	}
}
//Foreach pages to grab, check user rights and if page is public
if (APPLICATION_ENFORCES_ACCESS_CONTROL) {
	//check user rights
	foreach ($pages as $key => $pageId) {
		if (!$cms_user->hasPageClearance($pageId, CLEARANCE_PAGE_VIEW)) {
			unset($pages[$key]);
		}
	}
}

//Create cache reference
if (!isset($attributes['cache']) || (isset($attributes['cache']) && io::isPositiveInteger($attributes['cache']))) {
	$aCacheRef = array();
	$aCacheRef['pages'] = $pages;
	$aCacheRef['attributes'] = $attributes;
	if (APPLICATION_ENFORCES_ACCESS_CONTROL) {
		$aCacheRef['user'] = $cms_user;
	}
	$aCacheRef['get'] = $_GET;
	if (isset($aCacheRef['_dc'])) {
		unset($aCacheRef['_dc']);
	}
	if (isset($aCacheRef['context'])) {
		unset($aCacheRef['context']);
	}
	$cacheRef = md5(serialize($aCacheRef));
	
	//Cache options
	$frontendOptions = array(
		'lifetime' 			=> $attributes['cache'], // cache duration
		'caching' 			=> true,
		'cache_id_prefix'	=> MOD_CMS_PDF_CODENAME
	);
	$backendOptions = array(
		'cache_dir' => PATH_CACHE_FS.'/', // Directory where to put the cache files
		'cache_file_umask' => octdec(FILES_CHMOD),
		'hashed_directory_umask' => octdec(DIRS_CHMOD),
	);
	// getting a Zend_Cache_Core object
	try {
		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
	} catch (Zend_Cache_Exception $e) {
		CMS_query::raiseError($e->getMessage());
	}
	$datas = '';
	if (isset($cache) && ($datas = $cache->load($cacheRef))) {
		//send cache content
		header('Content-Type: application/pdf');
		header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Content-Length: '.strlen($datas));
		header('Content-Disposition: inline; filename="'.$filename.'";');
		echo $datas;
		exit;
	}
}

//Load pages
foreach ($pages as $key => $pageId) {
	$page = CMS_tree::getPageByID($pageId);
	if ($page->getPublication() == RESOURCE_PUBLICATION_PUBLIC) {
		$pages[$key] = $page;
	} else {
		unset($pages[$key]);
	}
}

//Create PDF title
$title = '';
if ($pages) {
	if (isset($attributes['title'])) {
		$title = $attributes['title'];
	} else {
		foreach ($pages as $page) {
			$title .= $title ? ' - ' : '';
			$title .= $page->getTitle(true);
		}
	}
}

//Set mutipages status
$multipages = sizeof($pages) > 1 ? true : false;

// ---------------------------------------------------------
//Build PDF
// ---------------------------------------------------------

// create new PDF document
$pdf = new CMS_pdf($cms_language, PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true); 

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(APPLICATION_LABEL);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 

//set some language-dependent strings
$pdf->setLanguageArray($l); 

$pdf->SetTitle($title);
// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $title, $attributes['subtitle']);

if (isset($tree)) {
	function buildRecursivePages(&$pdf, $level = 0, $tree = array(), $pages = array()) {
		global $cms_language;
		foreach ($tree as $pageId => $leaf) {
			if (isset($pages[$pageId])) {
				$page = $pages[$pageId];
				$pageContent = $page->getContent($cms_language, PAGE_VISUALMODE_HTML_PUBLIC_INDEXABLE);
				// add a page
				$pdf->AddPage();
				$pdf->Bookmark($page->getTitle(true), $level, 0);
				//Set page title
				$pdf->SetFont('dejavusans', 'B', 16);
				$pdf->MultiCell(0, 0, $page->getTitle(true), 0, 'C', 0, 1, '', '', true, 0);
				//set page HTML content
				$pdf->SetFont('helvetica', '', 10);
				$pdf->writeHTML($pageContent, true, 0, true, 0);
				if ($leaf) {
					buildRecursivePages($pdf, $level + 1, $leaf, $pages);
				}
			}
		}
	}
	buildRecursivePages($pdf, 0, $tree, $pages);
} else {
	//Create PDF pages
	foreach ($pages as $page) {
		$pageContent = $page->getContent($cms_language, PAGE_VISUALMODE_HTML_PUBLIC_INDEXABLE);
		// add a page
		$pdf->AddPage();
		$pdf->Bookmark($page->getTitle(true), 0, 0);
		//Set page title
		$pdf->SetFont('dejavusans', 'B', 16);
		$pdf->MultiCell(0, 0, $page->getTitle(true), 0, 'C', 0, 1, '', '', true, 0);
		//set page HTML content
		$pdf->SetFont('helvetica', '', 10);
		$pdf->writeHTML($pageContent, true, 0, true, 0);
	}
}

//Create PDF TOC
if (($multipages && !isset($attributes['toc'])) || $attributes['toc'] == true) {
	// add a new page for TOC
	$pdf->AddPage();
	
	// write the TOC title
	$pdf->SetFont('dejavusans', 'B', 16);
	$pdf->MultiCell(0, 0, $cms_language->getMessage(MESSAGE_TABLE_OF_CONTENT, false, MOD_CMS_PDF_CODENAME), 0, 'C', 0, 1, '', '', true, 0);
	$pdf->Ln();
	
	$pdf->SetFont('dejavusans', '', 12);
	
	// add table of content at page 1
	$pdf->addTOC(1, 'courier', '.', $cms_language->getMessage(MESSAGE_TABLE_OF_CONTENT, false, MOD_CMS_PDF_CODENAME));
}

if (isset($cache)) {
	//get pdf datas
	$datas = $pdf->Output($filename, 'S');
	//save datas to cache
	$cache->save($datas);
	
	//send datas content
	header('Content-Type: application/pdf');
	header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
	header('Pragma: public');
	header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Content-Length: '.strlen($datas));
	header('Content-Disposition: inline; filename="'.$filename.'";');
	echo $datas;
	exit;
} else {
	//Close and output PDF document
	$pdf->Output($filename, 'I');
}
?>