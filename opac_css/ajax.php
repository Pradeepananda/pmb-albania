<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax.php,v 1.3 2009-02-11 21:41:55 touraine37 Exp $

$base_path = ".";
$base_noheader = 1;
$base_nobody = 1;

require_once($base_path."/includes/init.inc.php");
require_once($base_path."/includes/error_report.inc.php") ;
require_once($base_path."/includes/global_vars.inc.php");
require_once($base_path."/includes/rec_history.inc.php");
require_once($base_path.'/includes/opac_config.inc.php');
	
// r�cup�ration param�tres MySQL et connection � la base
if (file_exists($base_path.'/includes/opac_db_param.inc.php')) require_once($base_path.'/includes/opac_db_param.inc.php');
	else die("Fichier opac_db_param.inc.php absent / Missing file Fichier opac_db_param.inc.php");
	
require_once($base_path.'/includes/opac_mysql_connect.inc.php');
$dbh = connection_mysql();

//Sessions !! Attention, ce doit �tre imp�rativement le premier include (� cause des cookies)
require_once($base_path."/includes/session.inc.php");

require_once($base_path.'/includes/start.inc.php');
require_once($base_path."/includes/check_session_time.inc.php");

// r�cup�ration localisation
require_once($base_path.'/includes/localisation.inc.php');

require_once($base_path.'/includes/divers.inc.php');
require_once($base_path."/includes/misc.inc.php");

// inclusion des fonctions utiles pour renvoyer la r�ponse � la requette recu 
require_once ($base_path . "/includes/ajax.inc.php");
require_once ($base_path . "/includes/divers.inc.php");

/*	
 * Parse la commande Ajax du client vers 
 * $module est pass� dans l'url,envoy� par http_send_request, in http_request.js script file
 * les valeurs envoy�es dans les requ�tes en ajax du client vers le serveur sont encod�es
 * exclusivement en utf-8 donc d�codage de toutes les variables envoy�es si n�cessaire
*/
//header("Content-Type: 'text/html'; charset=$charset");
//$charset= 'iso-8859-1';
if (strtoupper($charset)!="UTF-8") {
	$t=array_keys($_POST);	
	foreach($t as $v) {
		global $$v;
		$$v=utf8_decode($$v);
	}
	$t=array_keys($_GET);	
	foreach($t as $v) {
		global $$v;
		//print $v."= ".$$v."\n";
		$$v=utf8_decode($$v);
	}
}

//	print $module;	exit;
$main_file="./$module/ajax_main.inc.php";
switch($module) {
	case 'ajax':
		include($main_file);
	break;
	default:
		//tbd
	break;	
}		
?>
		
