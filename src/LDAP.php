<?php

namespace GlpiPlugin\Resources;

if (!defined('GLPI_ROOT')) {
    include('../../../inc/includes.php');
}

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2022 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */


use LdapTools\Configuration\DomainConfiguration;
use LdapTools\LdapManager;
use LdapTools\Exception\LdapConnectionException;
use LdapTools\Exception\LdapRecordNotFoundException;
if (!defined('CREATE')) define('CREATE', 1);
if (!defined('UPDATE')) define('UPDATE', 2);
if (!defined('DELETE')) define('DELETE', 4);

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

// Fallback for static analysis: use stubs if GLPI core classes are not available
if (!class_exists('AuthLDAP')) {
    class_alias('GlpiPlugin\\Resources\\AuthLDAP', 'AuthLDAP');
}
if (!class_exists('GLPIKey')) {
    class_alias('GlpiPlugin\\Resources\\GLPIKey', 'GLPIKey');
}

/**
 * Class LDAP
 */
class LDAP extends CommonDBTM
{
    public static $rightname = 'plugin_resources';
    // From CommonDBTM
    public $dohistory = true;

    /**
     * Return the localized name of the current Type
     * Should be overloaded in each new class
     *
     * @param integer $nb Number of items
     *
     * @return string
     **/
    public static function getTypeName($nb = 0)
    {
        return __('LDAP', 'resources');
    }

    /**
     * Have I the global right to "view" the Object
     *
     * Default is true and check entity if the objet is entity assign
     *
     * May be overloaded if needed
     *
     * @return bool
     **/

    public static function canView(): bool
    {
        return \Session::haveRight(self::$rightname, READ);
    }

    /**
     * Have I the global right to "create" the Object
     * May be overloaded if needed (ex KnowbaseItem)
     *
     * @return bool
     **/


    public static function canCreate(): bool
    {
        return \Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
    }

    /**
     * Display Tab for each budget
     *
     * @param array $options
     *
     * @return array
     */
    //   function defineTabs($options = []) {
    //
    //      $ong = [];
    //
    //      $this->addDefaultFormTab($ong);
    //      $this->addStandardTab('Document', $ong, $options);
    //      $this->addStandardTab('Log', $ong, $options);
    //
    //      return $ong;
    //   }

    /**
     * allow to control data before adding in bdd
     *
     * @param $input
     * @return array
     */
    //   function prepareInputForAdd($input) {
    //
    //      if (!isset($input["plugin_resources_professions_id"]) || $input["plugin_resources_professions_id"] == '0') {
    //         Session::addMessageAfterRedirect(__('The profession for the budget must be filled', 'resources'), false, ERROR);
    //         return [];
    //      }
    //
    //      return $input;
    //   }

    /**
     * allow to control data before updating in bdd
     *
     * @param $input
     * @return array
     */
    //   function prepareInputForUpdate($input) {
    //
    //      if (!isset($input["plugin_resources_professions_id"]) || $input["plugin_resources_professions_id"] == '0') {
    //         Session::addMessageAfterRedirect(__('The profession for the budget must be filled', 'resources'), false, ERROR);
    //         return [];
    //      }
    //
    //      return $input;
    //   }

    /**
     * PluginInsightvmInsightvm constructor.
     */
    public function __construct()
    {
    }

    public function connect($authsId)
    {
        $ldap = new \AuthLDAP();
        if (method_exists($ldap, 'getFromDB')) {
            $ldap->getFromDB($authsId);
        }
        if (method_exists($ldap, 'connect')) {
            $ldap_connection = $ldap->connect();
        } else {
            $ldap_connection = null;
        }
        return $ldap_connection;
    }

    private static function getConfig()
    {
        if (!class_exists('AuthLDAP')) {
            throw new \Exception('AuthLDAP class not found. Make sure GLPI is loaded.');
        }
        if (!class_exists('GLPIKey')) {
            throw new \Exception('GLPIKey class not found. Make sure GLPI is loaded.');
        }
        $config_ldap = new \AuthLDAP();
        $configAD = new Adconfig();
        $configAD->getFromDB(1);
        $authID = $configAD->fields["auth_id"];
        if (method_exists($config_ldap, 'getFromDB')) {
            $res = $config_ldap->getFromDB($authID);
        } else {
            $res = null;
        }

        // Create a configuration array.
        if (($ret = strpos($config_ldap->fields['host'], 'ldaps://')) !== false) {
            $host = str_replace('ldaps://', '', $config_ldap->fields['host']);
            $ssl = true;
        } elseif (($ret = strpos($config_ldap->fields['host'], 'ldap://')) !== false) {
            $host = str_replace('ldap://', '', $config_ldap->fields['host']);
            $ssl = false;
        } else {
            $host = $config_ldap->fields['host'];
            $ssl = false;
        }
        $config = [
            'hosts' => [$host],
            'port' => $config_ldap->fields['port'],
            'use_tls' => !empty($config_ldap->fields['use_tls']),
            'use_ssl' => $ssl,
            'follow_referrals' => !empty($config_ldap->fields['deref_option']),
            'version' => 3,
            'base_dn' => $config_ldap->fields['basedn'],
            'username' => $config_ldap->fields['rootdn'],
            'password' => (new \GLPIKey())->decrypt($config_ldap->fields['rootdn_passwd']),
        ];
        return $config;
    }

    public function existingUser($login)
    {
        $find = false;
        $adConfig = new Adconfig();
        $adConfig->getFromDB(1);
        $config = self::getConfig();
        $domain = new DomainConfiguration('default');
        $domain->setServers($config['hosts']);
        $domain->setBaseDn($config['base_dn']);
        $domain->setUsername($config['username']);
        $domain->setPassword($config['password']);
        $ldap = new LdapManager($domain);
        try {
            $query = $ldap->buildLdapQuery()->fromUsers()->where([$adConfig->getField("logAD") => $login]);
            $user = $ldap->getLdapQueryBuilder()->getLdapQuery()->getSingleResult();
            $find = true;
        } catch (LdapRecordNotFoundException $e) {
            $find = false;
        } catch (LdapConnectionException $e) {
            $find = false;
        }
        return $find;
    }

    public function createUserAD($data)
    {
        $adConfig = new Adconfig();
        $adConfig->getFromDB(1);
        $config = self::getConfig();
        $domain = new DomainConfiguration('default');
        $domain->setServers($config['hosts']);
        $domain->setBaseDn($config['base_dn']);
        $domain->setUsername($config['username']);
        $domain->setPassword($config['password']);
        $ldap = new LdapManager($domain);
        try {
            $user = $ldap->createLdapObject('user');
            $dn = "CN=" . $data["name"] . " " . $data["firstname"] . "," . $adConfig->getField("ouUser");
            $user->set('dn', $dn);
            $user->set('samaccountname', $data['login']);
            $user->set('cn', $data["name"] . " " . $data["firstname"]);
            $attributes = [];
            $attr = $adConfig->getArrayAttributes();
            foreach ($attr as $at) {
                $field = $adConfig->getField($at);
                if (!empty($field)) {
                    $a = LinkAd::getMapping($at);
                    if (isset($data[$a]) && !empty($data[$a])) {
                        if ($at == "contractEndAD") {
                            $win_time = 0;
                            if (!empty($data[$a])) {
                                $unix_time = strtotime($data[$a]);
                                $win_time = $this->unixTimeToLdapTime($unix_time);
                            }
                            $data[$a] = $win_time;
                        }
                        if ($field !== null && $field !== '' && $a !== null && $a !== '' && (is_string($field) || is_int($field))) {
                            $attributes[$field] = $data[$a];
                        }
                    }
                }
            }
            $attributes['displayName'] = $data["firstname"] . " " . $data["name"];
            $attributes['description'] = $data["role"];
            foreach ($attributes as $key => $value) {
                $user->set($key, $value);
            }
            $ldap->persist($user);
            return true;
        } catch (LdapConnectionException $e) {
            return false;
        }
    }

    public function updateUserAD($data)
    {
        $adConfig = new Adconfig();
        $adConfig->getFromDB(1);
        $config = self::getConfig();
        $domain = new DomainConfiguration('default');
        $domain->setServers($config['hosts']);
        $domain->setBaseDn($config['base_dn']);
        $domain->setUsername($config['username']);
        $domain->setPassword($config['password']);
        $ldap = new LdapManager($domain);
        try {
            $query = $ldap->buildLdapQuery()->fromUsers()->where([$adConfig->getField("logAD") => $data["login"]]);
            $user = $ldap->getLdapQueryBuilder()->getLdapQuery()->getSingleResult();
            $attributes = [];
            $attr = $adConfig->getArrayAttributes();
            foreach ($attr as $at) {
                $field = $adConfig->getField($at);
                if (!empty($field)) {
                    $a = LinkAd::getMapping($at);
                    if (isset($data[$a])) {
                        if (empty($data[$a]) && $at != "contractEndAD") {
                            if ($field !== null && $field !== '' && (is_string($field) || is_int($field))) {
                                $user->set($field, null);
                                $attributes[$field] = [];
                            }
                        } else {
                            if ($at == "contractEndAD") {
                                $win_time = 0;
                                if (!empty($data[$a])) {
                                    $unix_time = strtotime($data[$a]);
                                    $win_time = $this->unixTimeToLdapTime($unix_time);
                                }
                                $data[$a] = $win_time;
                            }
                            if ($field !== null && $field !== '' && $a !== null && $a !== '' && (is_string($field) || is_int($field))) {
                                $user->set($field, $data[$a]);
                                $attributes[$field] = $data[$a];
                            }
                        }
                    }
                }
            }
            foreach ($attributes as $key => $value) {
                $user->set($key, $value);
            }
            $ldap->persist($user);
            return [true, $attributes];
        } catch (LdapRecordNotFoundException $e) {
            return [false, []];
        } catch (LdapConnectionException $e) {
            return [false, []];
        }
    }

    public function disableUserAD($data)
    {
        $adConfig = new Adconfig();
        $adConfig->getFromDB(1);
        $config = self::getConfig();
        $domain = new DomainConfiguration('default');
        $domain->setServers($config['hosts']);
        $domain->setBaseDn($config['base_dn']);
        $domain->setUsername($config['username']);
        $domain->setPassword($config['password']);
        $ldap = new LdapManager($domain);
        try {
            $query = $ldap->buildLdapQuery()->fromUsers()->where([$adConfig->getField("logAD") => $data["login"]]);
            $user = $ldap->getLdapQueryBuilder()->getLdapQuery()->getSingleResult();
            // Disable the user (set userAccountControl or equivalent attribute)
            $user->set('userAccountControl', 514); // 514 = disabled in AD
            $ldap->persist($user);
            // Move user to deactivated OU
            $newParentDn = $adConfig->getField("ouDesactivateUserAD");
            $user->set('dn', $newParentDn);
            $ldap->persist($user);
            return true;
        } catch (LdapRecordNotFoundException $e) {
            return false;
        } catch (LdapConnectionException $e) {
            return false;
        }
    }


    public function ldapTimeToUnixTime($ldapTime)
    {
        $secsAfterADEpoch = $ldapTime / 10000000;
        $ADToUnixConverter = ((1970 - 1601) * 365 - 3 + round((1970 - 1601) / 4, 0, PHP_ROUND_HALF_UP)) * 86400;
        return intval($secsAfterADEpoch - $ADToUnixConverter);
    }

    public function unixTimeToLdapTime($unixTime)
    {
        $ADToUnixConverter = ((1970 - 1601) * 365 - 3 + round((1970 - 1601) / 4, 0, PHP_ROUND_HALF_UP)) * 86400;
        $secsAfterADEpoch = intval($ADToUnixConverter + $unixTime);
        return $secsAfterADEpoch * 10000000;
    }


}
