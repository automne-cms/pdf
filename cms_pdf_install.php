<?php
/**
  * Install or update cms_pdf module
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

require_once(dirname(__FILE__).'/../../cms_rc_admin.php');

//check if ASE is already installed (if so, it is an update)
$sql = "show tables";
$q = new CMS_query($sql);
$installed = false;
while ($table = $q->getValue(0)) {
	if ($table == 'mod_cms_pdf') {
		$installed = true;
	}
}
if (!$installed) {
	echo "PDF Export installation : Not installed : Launch installation ...<br />";
	if (CMS_patch::executeSqlScript(PATH_MAIN_FS.'/sql/mod_cms_pdf.sql',true)) {
		CMS_patch::executeSqlScript(PATH_MAIN_FS.'/sql/mod_cms_pdf.sql',false);
		echo "PDF Export installation : Installation done.<br /><br />";
	} else {
		echo "PDF Export installation : INSTALLATION ERROR ! Problem in SQL syntax (SQL tables file) ...<br />";
	}
} else {
	echo "PDF Export installation : Already installed : update done.<br />";
}
?>