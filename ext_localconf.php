<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$TYPO3_CONF_VARS['EXTCONF']['ldap_auth']['setup'] = unserialize($_EXTCONF);

if ($TYPO3_CONF_VARS['EXTCONF']['ldap_auth']['setup']['FE_fetchUserIfNoSession']) {
	$TYPO3_CONF_VARS['SVCONF']['auth']['setup']['FE_fetchUserIfNoSession'] = '1';
}
if ($TYPO3_CONF_VARS['EXTCONF']['ldap_auth']['setup']['BE_fetchUserIfNoSession']) {
	$TYPO3_CONF_VARS['SVCONF']['auth']['setup']['BE_fetchUserIfNoSession'] = '1';
}
if ($TYPO3_CONF_VARS['EXTCONF']['ldap_auth']['setup']['FE_alwaysFetchUser']) {
	$TYPO3_CONF_VARS['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = '1';
}
if ($TYPO3_CONF_VARS['EXTCONF']['ldap_auth']['setup']['BE_alwaysFetchUser']) {
	$TYPO3_CONF_VARS['SVCONF']['auth']['setup']['BE_alwaysFetchUser'] = '1';
}

if ($TYPO3_CONF_VARS['EXTCONF']['ldap_auth']['setup']['plainTextLoginBE']) {
	$TYPO3_CONF_VARS['BE']['loginSecurityLevel'] = 'normal';
}

if ($TYPO3_CONF_VARS['EXTCONF']['ldap_auth']['setup']['enableFE'] && !$TYPO3_CONF_VARS['EXTCONF']['ldap_auth']['setup']['enableBE']) {
	$subTypes = 'getUserFE,authUserFE';
}

if (!$TYPO3_CONF_VARS['EXTCONF']['ldap_auth']['setup']['enableFE'] && $TYPO3_CONF_VARS['EXTCONF']['ldap_auth']['setup']['enableBE']) {
	$subTypes = 'getUserBE,authUserBE';
}

if ($TYPO3_CONF_VARS['EXTCONF']['ldap_auth']['setup']['enableFE'] && $TYPO3_CONF_VARS['EXTCONF']['ldap_auth']['setup']['enableBE']) {
	$subTypes = 'getUserFE,authUserFE,getUserBE,authUserBE';
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService($_EXTKEY,
	'auth' /* sv type */,
	'LdapAuthenticationService' /* sv key */,
	array(
		'title' => 'LDAP Authentification Service',
		'description' => '',

		'subtype' => $subTypes,
		
		'available' => TRUE,
		'priority' => 60,
		'quality' => 60,

		'os' => '',
		'exec' => '',

		'className' => 'B13\\LdapAuth\\Service\\LdapAuthenticationService',
	)
);
