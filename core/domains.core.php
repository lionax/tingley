<?php
	
	function getCurrentDomainName() {
		$replace = array('www.', 'http://', 'https://', '/');
		$domain = $_SERVER['HTTP_HOST'];
		$domain = str_replace($replace, '', $domain);
		return $domain;
	}
	
	function getCurrentDomain() {
		global $db;
		$result = $db->selectOneRow('domains', '*', "INSTR(`name`, '".getCurrentDomainName()."') > 0");
		return $result;
	}
	
	function getCurrentDomainIndex() {
		$domain = getCurrentDomain();
		return (int)$domain['domainid'];
	}
	
	function getDomainList() {
		global $db;
		return $db->selectList('domains');
	}
	
?>