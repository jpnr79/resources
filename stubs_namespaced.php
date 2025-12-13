<?php
namespace GlpiPlugin\Resources;

// Minimal permissive stubs used only for static analysis.
class Html
{
    public static function textarea(...$args) {}
    public static function convDate(...$args) {}
    public static function showDateField(...$args) {}
    public static function formatNumber(...$args) {}
    public static function computeGenericDateTimeSearch(...$args) {}
    public static function file(...$args) {}
    public static function printPager(...$args) {}
    public static function printAjaxPager(...$args) {}
}

class Dropdown
{
    public static function showTimeStamp(...$args) {}
}

class Session
{
    public static function isMultiEntitiesMode(...$args) {}
    public static function haveTranslations(...$args) {}
}

class Search
{
    public static function prepareDatasForSearch(...$args) {}
    public static function constructSQL(...$args) {}
    public static function constructData(...$args) {}
    public static function displayData(...$args) {}
    public static function getOptions(...$args) {}
    public static function addSelect(...$args) {}
    public static function addDefaultJoin(...$args) {}
    public static function addDefaultWhere(...$args) {}
    public static function addHaving(...$args) {}
    public static function addWhere(...$args) {}
    public static function addOrderBy(...$args) {}
    public static function addMetaLeftJoin(...$args) {}
    public static function showError(...$args) {}
    public static function computeComplexJoinID(...$args) {}
    public static function makeTextSearch(...$args) {}
}

class Plugin
{
    public static function loadLang(...$args) {}
}

class Toolbox
{
    public static function return_bytes_from_ini_vars(...$args) {}
    public static function manageBeginAndEndPlanDates(...$args) {}
    public static function getTimestampTimeUnits(...$args) {}
    public static function logDebug(...$args) {}
    public static function cleanInteger(...$args) {}
    public static function append_params($url, $params = []) {}
    public static function return_bytes_from_ini_vars_alt(...$args) {}
}

class Ajax
{
    public static function commonDropdownUpdateItem(...$args) {}
    public static function updateItemOnInputTextEvent(...$args) {}
}

class Alert
{
    const END = 0;
}

class MassiveAction {}

class CommonDBTM
{
    public function showMassiveActionsSubForm(...$args) {}
    public function processMassiveActionsForOneItemtype(...$args) {}
}

class CommonGLPI
{
    public static function getTabNameForItem(...$args) {}
}

// Generic exceptions used in plugin
namespace Glpi\Exception\Http;
class BadRequestHttpException extends \Exception {}

// Provide permissive stubs for core item classes when referenced unqualified inside the plugin namespace
class Computer extends \CommonDBTM {}
class Monitor extends \CommonDBTM {}
class NetworkEquipment extends \CommonDBTM {}
class Peripheral extends \CommonDBTM {}
class Phone extends \CommonDBTM {}
class Printer extends \CommonDBTM {}
class ConsumableItem extends \CommonDBTM {}
class ComputerType extends \CommonDBTM {}
class PhoneType extends \CommonDBTM {}
class Appliance extends \CommonDBTM {}
class Item_Problem extends \CommonDBTM {}
class Item_Ticket extends \CommonDBTM {}

// Lightweight stubs for external/vendor classes referenced from plugin files
class AuthLDAP {}
class GLPIKey {}

// Plugin datainjection helper shim
class PluginDatainjectionCommonInjectionLib {}

// Local NotificationTarget used inside plugin namespace
class NotificationTarget {
    public $input = [];
    public $notification_targets = [];
    public $notification_targets_labels = [];
    public function addTarget($type, $label = null) {}
    public function addToRecipientsList($data) {}
    public function addUserByField($field) {}
    public function getDistinctUserCriteria() { return []; }
    public function addTagToList(...$a) { }
    public function getAllEvents() { return []; }
}
