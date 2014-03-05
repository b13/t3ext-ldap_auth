<?php

namespace B13\LdapAuth\Service;

/***************************************************************
*  Copyright notice
*  
*  (c) 2004  ()
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/** 
 * @author	Daniel Thomas <dt@dpool.net>
 */

class LdapAuthenticationService extends \TYPO3\CMS\Sv\AbstractAuthenticationService {
	var $prefixId = 'LdapAuthenticationService';					// Same as class name
	var $scriptRelPath = 'Classes/Service/LdapAuthenticationService.php';	// Path to this script relative to the extension dir.
	var $extKey = 'ldap_auth';							// The extension key.

	/**
	 * Inits some variables
	 *
	 * @return	void
	 */
	function init()	{
		// exit if no LDAP support in PHP
		if (!extension_loaded('ldap')) {
			throw new \RuntimeException('PHP was not compiled with the LDAP extension.');
			return FALSE;
		}
		return parent::init();
	}

	/**
	 * Tries to find a user by whatever name is returned by the initUser() function
	 *
	 * @return	array	a temporary user record
	 */
	function getUser() {

			// only do this if there is no valid user session
		if (!$this->authInfo['userSession']['uid']) {
				// init the ldapServer object getting the relevant ldap server record
				// and establishing a ldap connection object
			$this->ldapServer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('B13\\LdapServer\\Server');
			$server = $this->ldapServer->initLDAP('', '', $this->authInfo['loginType']);
			if (is_array($server)) {
				if ($this->writeDevLog) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('LDAP server available ('.$server['servername'].'). getUser()', 'ldap_auth');
				}
			} else {
				if ($this->writeDevLog) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('No LDAP server available. getUser()', 'ldap_auth');
				}
				return FALSE;
			}
				// get username and password
			$tmpUser = $this->initUser($server['config']);
			if ($tmpUser) {
				if ($this->writeDevLog) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Basic user info available. initUser()', 'ldap_auth');
				}
			} else {
				if ($this->writeDevLog) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('No user information what-so-ever found. initUser()', 'ldap_auth','2');
					\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Security level: ' . $this->authInfo['security_level'], 'ldap_auth');
				}
				return false;
			}
							
				// we can now savely assume the functional ldap connection object here
			$GLOBALS['LDAP_CONNECT']->search(str_replace('###USERNAME###', $tmpUser['username'], $server['filter_person']));
			$user = $GLOBALS['LDAP_CONNECT']->fetch();
			$user['dn'] = $GLOBALS['LDAP_CONNECT']->getDN();
			if ($user['dn']) {
				return array_merge($user, $tmpUser);
			}
		}
	}
	
	/**
	 * Authenticate a user
	 *
	 * @param	array 	Data of user.
	 * @param	array 	Information array. Holds submitted form data etc.
	 * @param	string 	subtype of the service which is used to call this service.
	 * @return	int
	 */	
	function authUser(&$user) {
		$ret = 100;
		$auth = $user['authenticated'];
		$ldapServer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('B13\\LdapServer\\Server');
		if (is_object($ldapServer)) {
			if (is_array($server = $ldapServer->initLDAP('','',$this->authInfo['loginType']))) {
				if ((($username = $ldapServer->getUsernameForAuth($user, $server)) && ($this->login['uident_text'])) || $auth) {
						// stupid hack. I need to preserve the cid and bid connections if authentification has already taken place.
						// if it has not taken place they are set later again.
					if (!$auth) {
						$GLOBALS['LDAP_CONNECT']->cid = false;
						$GLOBALS['LDAP_CONNECT']->bid = false;
					}
						// the connect function returns true only it a bind is possible
					if ($GLOBALS['LDAP_CONNECT']->connect($username, $this->login['uident_text'], $server['servername']) || $auth) {
							// now we have either a bind with the new user
							// or we have the user already authenticated
						if ($GLOBALS['LDAP_CONNECT']->bid || $auth) {
								// only if there is valid table definition we know what to do with the data
								// ldap_server::getConf()
							if ($conf = $ldapServer->getConf($server['config'], 'LDAP_AUTH', $this->authInfo['db_user']['table'])) {
									// only if transfer data returns a valid user we can authenticate
								if ($tmp = $ldapServer->transferData($user, $conf['sync.'], $server['pid'])) {
									$user['uid'] = $tmp['uid'];
									$ret = 200;
								}
							}
						}
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * Find the basic information about a user
	 * The function getUser expects to have a username returned which is unique in the LDAP directory
	 * It needs to return the username key. If you want to use a SSO setup you can use the authenticated key for that
	 *
	 * @return	array	data for the user. $user['username'] and optional $user['authenticated'] = true
	 */	
	function initUser($serverConf) {
		$user['authenticated'] = false;
		if ($this->login['uname'] && $this->login['uident_text']) {
			$user['username'] = $this->login['uname'];
			return $user;
		} else if ($conf = $this->ldapServer->getConf($serverConf, 'LDAP_AUTH', $this->authInfo['db_user']['table'])) {
				// Checking for logout status enables that users can logout even if SSO is set
				// Thanks Hans J. Martin for the tip
			if (($conf['SSO'] == '1') && (is_array($conf['SSO.']) && ($this->login['status'] != 'logout'))) {
				foreach ($conf['SSO.'] as $method) {
					$retArr = \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($method['userFunc'], $method['userFunc.'], $this);
					if ($retArr['authenticated'] == '1') {
						return $retArr;
					}
				}
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Sample function for SSO process
	 *
	 * @param	array	$data: usually empty
	 * @param	array	$conf: usually empty
	 * @return	array	sets output for initUser()
	 */
	function authFromGet($data, $conf = '') {
		$user = array();
		if($username = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('sso')) {
			$user['username'] = $username;
			$user['authenticated'] = '1';
		}
		return $user;
	}
}
