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
require_once(dirname(__FILE__)."/../cms_rc_frontend.php");
//augment memory and time limits
@ini_set('memory_limit', '256M');
@set_time_limit(0);
@ini_set('output_buffering','Off');

header('Cache-Control: private, must-revalidate, max-age=0'); // HTTP/1.1
header('Pragma: private');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

//Message contants
define('MESSAGE_TABLE_OF_CONTENT', 3);
define('MESSAGE_PLEASE_WAIT', 6);
define('MESSAGE_PDF_IS_CREATED', 7);
define('MESSAGE_WAIT_FEW_MINUTES', 8);
define('MESSAGE_PDF_IS_READY', 9);
define('MESSAGE_CLICK_TO_GET', 10);

$filename = 'pdf.pdf';

$pdfId = io::request('pdf', 'io::isPositiveInteger');

if (!$pdfId) {
	header('HTTP/1.x 301 Moved Permanently', true, 301);
	header('Location: '.PATH_SPECIAL_PAGE_NOT_FOUND_WR.'');
	exit;
}

//Get link from pdf ID
$link = new CMS_pdf_link($pdfId);

if (!$link || $link->hasError()) {
	header('HTTP/1.x 301 Moved Permanently', true, 301);
	header('Location: '.PATH_SPECIAL_PAGE_NOT_FOUND_WR.'');
	exit;
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

//force 5 minutes cache for export of more than 1 page
if (sizeof($pages) > 1 && isset($attributes['cache']) && (!io::isPositiveInteger($attributes['cache']) || $attributes['cache'] < 300)) {
	$attributes['cache'] = 300;
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
	if (isset($aCacheRef['get']['_dc'])) {
		unset($aCacheRef['get']['_dc']);
	}
	if (isset($aCacheRef['get']['context'])) {
		unset($aCacheRef['get']['context']);
	}
	if (isset($aCacheRef['get']['pdf-done'])) {
		unset($aCacheRef['get']['pdf-done']);
	}
	$cacheRef = md5(serialize($aCacheRef));
	
	if (!isset($attributes['cache'])) {
		$attributes['cache'] = CMS_pdf::PDF_DEFAULT_CACHE_LENGTH;
	}
	//create cache object
	$cache = new CMS_cache($cacheRef, MOD_CMS_PDF_CODENAME, $attributes['cache'], false);
	$datas = '';
	if ($cache->exist() && ($datas = $cache->load())) {
		//send cache content
		header('Content-Type: application/pdf', true);
		header('Content-Transfer-Encoding: binary', true);
		header('Content-Length: '.strlen($datas));
		header('Content-Disposition: inline; filename="'.$filename.'";');
		echo $datas;
		exit;
	}
}

//PDF does not exists in cache. If PDF use more than 1 page, send "please wait" screen to user
$waitScreen = false;
if (sizeof($pages) > 1) {
	$waitScreen = true;
	@ob_end_flush();
	
	$content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>'.$cms_language->getMessage(MESSAGE_PLEASE_WAIT, false, MOD_CMS_PDF_CODENAME).'</title>
		<meta http-equiv="Content-Type" content="text/html; charset='.APPLICATION_DEFAULT_ENCODING.'" />
		<meta name="robots" content="noindex, noarchive" />
		<style type="text/css">
		body {
			background-color: 		#E9F1DA;
			margin:					0;
			font:					13px/1.231 arial,helvetica,clean,sans-serif;
		}
		#message {
			font-size:				123.1%;
			width:					500px;
			margin:					20px auto 0 auto;
			display: 				block;
			padding:				10px;
			text-align:				center;
			background:				#FFF url(/img/cms_pdf/loader.gif) 480px 7px no-repeat;
			border-radius: 			10px;
			-moz-border-radius:		10px;
			-webkit-border-radius:	10px;
			box-shadow:				3px 3px 5px #888;
			-moz-box-shadow:		3px 3px 5px #888;
			-webkit-box-shadow:		3px 3px 5px #888;
		}
		hr {
			border:					0px solid white;
			border-bottom:			1px solid #DDE6CB;
		}
		h1 {
			font-size:				123.1%;
			margin:					4px 0;
		}
		a, a:link {
			color:					#5F900B;
		}
		</style>
	</head>
	<body>
	<div id="message">';
	echo $content;
	@ob_start();
	@ob_end_clean();
	@flush();
	@ob_end_flush();
	@usleep(1);
	
	$content = '
	<div>
		<h1>'.$cms_language->getMessage(MESSAGE_PDF_IS_CREATED, false, MOD_CMS_PDF_CODENAME).'</h1>
		'.$cms_language->getMessage(MESSAGE_WAIT_FEW_MINUTES, false, MOD_CMS_PDF_CODENAME).'<br /><br />
	</div>';
	echo $content;
	@ob_start();
	@ob_end_clean();
	@flush();
	@ob_end_flush();
	@usleep(1);
	$endWaitMessage = '
		<style type="text/css">
		#message {
			background:				#FFF;
		}
		</style>
		<div>
		'.$cms_language->getMessage(MESSAGE_PDF_IS_READY, false, MOD_CMS_PDF_CODENAME).'<br />
		<a href="'.$_SERVER['REQUEST_URI'].'&amp;pdf-done=1">'.$cms_language->getMessage(MESSAGE_CLICK_TO_GET, false, MOD_CMS_PDF_CODENAME).'</a>
		</div>
		<script type="text/javascript">window.top.location = \''.$_SERVER['REQUEST_URI'].'&pdf-done=1\';</script>
		</div>
	</body>
	</html>';
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

if (strtolower(APPLICATION_DEFAULT_ENCODING) != 'utf-8') {
	$title = utf8_encode($title);
	$attributes['subtitle'] = utf8_encode($attributes['subtitle']);
}

$pdf->SetTitle($title);
// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $title, $attributes['subtitle']);

if (isset($tree)) {
	function buildRecursivePages(&$pdf, $level = 0, $tree = array(), $pages = array()) {
		global $cms_language, $waitScreen;
		foreach ($tree as $pageId => $leaf) {
			if (isset($pages[$pageId])) {
				$page = $pages[$pageId];
				$pageTitle = $page->getTitle(true);
				$pageContent = $page->getContent($cms_language, PAGE_VISUALMODE_HTML_PUBLIC_INDEXABLE);
				if (strtolower(APPLICATION_DEFAULT_ENCODING) != 'utf-8') {
					$pageTitle = utf8_encode($pageTitle);
					$pageContent = utf8_encode($pageContent);
				}
				if (trim(strip_tags($pageContent))) {
					// add a page
					$pdf->AddPage();
					$pdf->Bookmark($pageTitle, $level, 0);
					//Set page title
					$pdf->SetFont('dejavusans', 'B', 16);
					$pdf->MultiCell(0, 0, $pageTitle, 0, 'C', 0, 1, '', '', false, 0);
					//set page HTML content
					$pdf->SetFont('helvetica', '', 10);
					$pdf->writeHTML($pageContent, true, 0, true, 0);
					if ($leaf) {
						buildRecursivePages($pdf, $level +1, $leaf, $pages);
					}
				} elseif ($leaf) {
					buildRecursivePages($pdf, $level, $leaf, $pages);
				}
				//clean page
				unset($pages[$pageId]);
				unset($page);
				if ($waitScreen) {
					echo "\n";
					@ob_start();
					@ob_end_clean();
					@flush();
					@ob_end_flush();
					@usleep(1);
				}
			}
		}
	}
	buildRecursivePages($pdf, 0, $tree, $pages);
} else {
	//Create PDF pages
	foreach ($pages as $pageId => $page) {
		$pageTitle = $page->getTitle(true);
		$pageContent = $page->getContent($cms_language, PAGE_VISUALMODE_HTML_PUBLIC_INDEXABLE);
		if (strtolower(APPLICATION_DEFAULT_ENCODING) != 'utf-8') {
			$pageTitle = utf8_encode($pageTitle);
			$pageContent = utf8_encode($pageContent);
		}
		if (trim(strip_tags($pageContent))) {
			// add a page
			$pdf->AddPage();
			$pdf->Bookmark($pageTitle, 0, 0);
			//Set page title
			$pdf->SetFont('dejavusans', 'B', 16);
			$pdf->MultiCell(0, 0, $pageTitle, 0, 'C', 0, 1, '', '', false, 0);
			//set page HTML content
			$pdf->SetFont('helvetica', '', 10);
			$pdf->writeHTML($pageContent, true, 0, true, 0);
		}
		//clean page
		unset($pages[$pageId]);
		unset($page);
		if ($waitScreen) {
			echo "\n";
			@ob_start();
			@ob_end_clean();
			@flush();
			@ob_end_flush();
			@usleep(1);
		}
	}
}

//Create PDF TOC
if (($multipages && !isset($attributes['toc'])) || (isset($attributes['toc']) && $attributes['toc'] == true)) {
	// add a new page for TOC
	$pdf->AddPage();
	$tocLabel = $cms_language->getMessage(MESSAGE_TABLE_OF_CONTENT, false, MOD_CMS_PDF_CODENAME);
	if (strtolower(APPLICATION_DEFAULT_ENCODING) != 'utf-8') {
		$tocLabel = utf8_encode($tocLabel);
	}
	// write the TOC title
	$pdf->SetFont('dejavusans', 'B', 16);
	$pdf->MultiCell(0, 0, $tocLabel, 0, 'C', 0, 1, '', '', false, 0);
	$pdf->Ln();
	
	$pdf->SetFont('dejavusans', '', 12);
	
	// add table of content at page 1
	$pdf->addTOC(1, 'courier', '.', $tocLabel);
}

if (isset($cache)) {
	//get pdf datas
	$datas = $pdf->Output($filename, 'S');
	//save datas to cache
	$cache->save($datas);
	
	if ($waitScreen) {
		echo $endWaitMessage;
		exit;
	}
	
	//send datas content
	header('Content-Type: application/pdf', true);
	header('Content-Transfer-Encoding: binary', true);
	header('Content-Length: '.strlen($datas));
	header('Content-Disposition: inline; filename="'.$filename.'";');
	echo $datas;
	exit;
} else {
	//Close and output PDF document
	if (!$waitScreen) {
		$pdf->Output($filename, 'I');
	} else {
		echo $endWaitMessage;
	}
}
?>