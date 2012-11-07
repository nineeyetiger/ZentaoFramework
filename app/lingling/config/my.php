<?php
$config->debug        = true;  
$config->requestType  = 'GET';    // PATH_INFO or GET.
$config->requestFix   = '-';
$config->webRoot      = '/demo/'; 
$config->myMailAccount = "liuwanwei@sungeo.cn";
$config->myMailPassword = "53554644";

if (defined('SAE_MYSQL_HOST_M')){
	$config->db->host     = SAE_MYSQL_HOST_M;
	$config->db->port     = SAE_MYSQL_PORT;
	$config->db->name     = SAE_MYSQL_DB; 
	$config->db->user     = SAE_MYSQL_USER; 
	$config->db->password = SAE_MYSQL_PASS;
}else{
	$config->db->name     = 'app_lingling1';
	$config->db->host     = 'localhost';
	$config->db->port     = '3306'; 
	$config->db->user     = 'root'; 
	$config->db->password = 'root';
	
	// SAE tmpFS directory prefix.
	define(SAE_TMP_PATH , ".");
}






