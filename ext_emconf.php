<?php

########################################################################
# Extension Manager/Repository config file for ext "ldap_auth".
#
# Auto generated 09-12-2009 14:47
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'LDAP Authentification Service',
	'description' => 'This extension provides a service for authentification against LDAP directories. It is based on the authentification service system established by the extension cc_sv_auth',
	'category' => 'services',
	'shy' => 0,
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Daniel Thomas, Benjamin Mack',
	'author_email' => 'dt@dpool.net,benni@typo3.org',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.1.0-6.2.99',
			'ldap_server' => '1.0.0-1.0.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:7:{s:21:"ext_conf_template.txt";s:4:"d871";s:12:"ext_icon.gif";s:4:"0898";s:17:"ext_localconf.php";s:4:"7b94";s:14:"doc/manual.sxw";s:4:"ce85";s:19:"doc/wizard_form.dat";s:4:"8039";s:20:"doc/wizard_form.html";s:4:"f458";s:29:"sv1/class.tx_ldapauth_sv1.php";s:4:"7ea8";}',
);

?>
