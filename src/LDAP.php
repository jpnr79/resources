<?php
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


namespace GlpiPlugin\Resources;


use Adldap\Adldap;
use Adldap\Auth\BindException;
use Adldap\Models\ModelNotFoundException;
if (!defined('CREATE')) define('CREATE', 1);
if (!defined('UPDATE')) define('UPDATE', 2);
if (!defined('DELETE')) define('DELETE', 4);

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
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
        $ldap->getFromDB($authsId);
        $ldap_connection = $ldap->connect();
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
        $res = $config_ldap->getFromDB($authID);

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
        $ad = new Adldap();
        $config = self::getConfig();
        $ad->addProvider($config);


        try {
            $provider = $ad->connect();
            $search = $provider->search();
            $record = $search->findByOrFail($adConfig->getField("logAD"), $login);

            $find = true;
        } catch (ModelNotFoundException $e) {
            // Record wasn't found!
            $find = false;
        }
        return $find;
    }

    public function createUserAD($data)
    {
        $adConfig = new Adconfig();
        $adConfig->getFromDB(1);
        $ad = new Adldap();
        $config = self::getConfig();
        $ad->addProvider($config);
        try {
            $provider = $ad->connect();
            $user = $provider->make()->user();

            // Create the users distinguished name.
            // We're adding an OU onto the users base DN to have it be saved in the specified OU.
            //         $dn = $user->getDnBuilder()->addOu($adConfig->getField("ouUser")); // Built DN will be: "CN=John Doe,OU=Users,DC=acme,DC=org";
            //         $dn->addCn($data["firstname"]." ".$data["name"]);
            //         // Set the users DN, account name.
            //         $user->setDn($dn);
            $dn = "CN=" . $data["name"] . " " . $data["firstname"] . "," . $adConfig->getField("ouUser");
            $user->setDn($dn);
            $user->setAccountName($data['login']);
            $user->setCommonName($data["name"] . " " . $data["firstname"]);


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
                        if ($field !== null && $field !== '') {
                            $attributes[$field] = $data[$a];
                        }
                    }
                }
            }
            $attributes['displayName'] = $data["firstname"] . " " . $data["name"];
            $attributes['description'] = $data["role"];
            $user->fill($attributes);

            if ($user->save()) {
                return true;
            } else {
                return false;
            }
        } catch (ModelNotFoundException $e) {
            // Record wasn't found!
            return false;
        }
    }

    public function updateUserAD($data)
    {
        $adConfig = new Adconfig();
        $adConfig->getFromDB(1);
        $ad = new Adldap();
        $config = self::getConfig();
        $ad->addProvider($config);
        try {
            $provider = $ad->connect();
            $user = $provider->search()->whereEquals($adConfig->getField("logAD"), $data["login"])->firstOrFail();

            // Create the users distinguished name.
            // We're adding an OU onto the users base DN to have it be saved in the specified OU.
            //         $user->setCommonName($data["firstname"]." ".$data["name"]);


            $attributes = [];
            $attr = $adConfig->getArrayAttributes();
            foreach ($attr as $at) {
                $field = $adConfig->getField($at);
                if (!empty($field)) {
                    $a = LinkAd::getMapping($at);
                    if (isset($data[$a])) {
                        if (empty($data[$a]) && $at != "contractEndAD") {
                            if ($field !== null && $field !== '') {
                                $user->setAttribute($field, null);
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
                            if ($field !== null && $field !== '') {
                                $user->setAttribute($field, $data[$a]);
                                $attributes[$field] = $data[$a];
                            }
                        }
                    }
                }
            }
            $rename = false;
            if (count($dirty = $user->getDirty())) {
                if (isset($dirty[$adConfig->getField("firstnameAD")]) || isset($dirty[$adConfig->getField("nameAD")])) {
                    $rename = true;
                }
            }
            $new_value = [];

            $attributesEnd = $user->getAttributes();
            foreach ($dirty as $k => $d) {
                if (isset($attributesEnd[$k])) {
                    $new_value[$k] = $attributesEnd[$k];
                }
            }
            if ($user->save()) {
                if ($rename) {
                    $ncn = "cn=" . $data["name"] . " " . $data["firstname"];
                    if ($user->rename($ncn)) {
                        return [true, $new_value];
                    }
                    return [false, $new_value];
                }

                return [true, $new_value];
            } else {
                return [false, $new_value];
            }
        } catch (ModelNotFoundException $e) {
            // Record wasn't found!
            return [false, []];
        }
    }

    public function disableUserAD($data)
    {
        $adConfig = new Adconfig();
        $adConfig->getFromDB(1);
        $ad = new Adldap();
        $config = self::getConfig();
        $ad->addProvider($config);
        try {
            $provider = $ad->connect();
            $user = $provider->search()->whereEquals($adConfig->getField("logAD"), $data["login"])->firstOrFail();


            $attributes = [];
            $attr = $adConfig->getArrayAttributes();
            $ac = $user->getUserAccountControlObject();

            // Mark the account as enabled (normal).
            $ac->accountIsDisabled();
            $user->setUserAccountControl($ac);

            if ($user->save()) {
                //            $newParentDn = $user->getDnBuilder()->addOu($adConfig->getField("ouDesactivateUserAD"));
                //            $newParentDn = $newParentDn->removeOu($adConfig->getField("ouUser"));
                //            $newParentDn = $newParentDn->removeCn($user->getCommonName());
                $newParentDn = $adConfig->getField("ouDesactivateUserAD");
                if ($user->move($newParentDn)) {
                    return true;
                }
                return false;
            } else {
                return false;
            }
        } catch (ModelNotFoundException $e) {
            // Record wasn't found!
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
