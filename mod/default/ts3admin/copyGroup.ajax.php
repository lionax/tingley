<?php
	//init
	require_once("libraries/ts3init.php");
	
	//get sid and init server_instance
	getSID();
	
	//get vsid and locate vserver_instance
	getVSID();
	
	checkStdRightIssues();
	
	if($ts3vserver_rights["r_add_group"]!=1) {
		$smarty->display('../mod/default/ts3admin/notallowed.tpl');
		die();
	}
	
	$smarty->assign('ajaxcallback', false);
	
	if(isset($_GET["save"])) {
		try {
			$type = TeamSpeak3::GROUP_DBTYPE_TEMPLATE;
			switch($_GET["type"]){
				case "clients":
					$type = TeamSpeak3::GROUP_DBTYPE_REGULAR;
					break;
				case "query":
					$type = TeamSpeak3::GROUP_DBTYPE_SERVERQUERY;
					break;
				default:
					$type = TeamSpeak3::GROUP_DBTYPE_TEMPLATE;
			}
			$ts3server->serverGroupCopy($_GET["ssgid"],$_GET["name"],0,$type);
			$smarty->assign('ajaxcallback', true);	
		}catch(Exception $e) {
			$notify->raiseError("",$e);	
		}
	}
	
	$smarty->display('../mod/default/ts3admin/copyGroup.tpl');
?>