<?php
// Additional permissive global stubs to reduce PHPStan noise for resources plugin.

namespace Glpi\RichText {
    class RichText { public static function getTextFromHtml(...$a) { return ''; } }
}

namespace GlpiPlugin\Badges {
    if (!class_exists('GlpiPlugin\\Badges\\Badge')) { class Badge extends \CommonDBTM {} }
}

namespace GlpiPlugin\Metademands {
    if (!class_exists('GlpiPlugin\\Metademands\\Metademand')) { class Metademand extends \CommonDBTM {} }
    if (!class_exists('GlpiPlugin\\Metademands\\Metademand_Resource')) { class Metademand_Resource extends \CommonDBTM {} }
    if (!class_exists('GlpiPlugin\\Metademands\\Field')) { class Field {} }
    if (!class_exists('GlpiPlugin\\Metademands\\FieldParameter')) { class FieldParameter {} }
    if (!class_exists('GlpiPlugin\\Metademands\\Config')) { class Config {} }
}

namespace {
    if (!class_exists('Search')) {
        class Search {
            public static function prepareDatasForSearch(...$a) { return []; }
            public static function constructSQL(...$a) { return ''; }
            public static function constructData(...$a) { return []; }
            public static function displayData(...$a) { return ''; }
            public static function getOptions(...$a) { return []; }
            public static function addSelect(...$a) { return; }
            public static function addDefaultJoin(...$a) { return; }
            public static function addDefaultWhere(...$a) { return; }
            public static function addHaving(...$a) { return; }
            public static function addWhere(...$a) { return; }
            public static function addOrderBy(...$a) { return; }
            public static function addMetaLeftJoin(...$a) { return; }
            public static function showError(...$a) { return; }
            public static function computeComplexJoinID(...$a) { return 0; }
            public static function makeTextSearch(...$a) { return ''; }
        }
    }

    if (!class_exists('DbUtils')) { class DbUtils { public static function getTableForItemtype($t = null) { return ''; } } }

    if (!class_exists('Notification_NotificationTemplate')) { class Notification_NotificationTemplate extends \CommonDBTM {} }
    if (!class_exists('Document_Item')) { class Document_Item extends \CommonDBTM {} }
    if (!class_exists('Log')) { class Log { public static function history(...$a) {} } }
    if (!class_exists('Planning')) { class Planning { public static function checkAlreadyPlanned(...$a) { return false; } } }
    if (!class_exists('PluginFieldsContainer')) { class PluginFieldsContainer {} }
    if (!class_exists('PluginFieldsField')) { class PluginFieldsField {} }
    if (!class_exists('PluginFieldsLabelTranslation')) { class PluginFieldsLabelTranslation { public static function getLabelFor(...$a) { return ''; } } }
    if (!class_exists('ITILCategory')) { class ITILCategory extends \CommonDBTM {} }
    if (!class_exists('TicketTemplate')) { class TicketTemplate extends \CommonDBTM {} }
    if (!class_exists('TicketTemplatePredefinedField')) { class TicketTemplatePredefinedField extends \CommonDBTM {} }
    if (!class_exists('UserCategory')) { class UserCategory extends \CommonDBTM {} }
    if (!class_exists('UserTitle')) { class UserTitle extends \CommonDBTM {} }
    if (!class_exists('Profile_User')) { class Profile_User extends \CommonDBRelation {} }
    if (!class_exists('UserEmail')) { class UserEmail extends \CommonDBTM {} }
    if (!class_exists('PluginPdfSimplePDF')) { class PluginPdfSimplePDF {} }
    if (!class_exists('PluginPdfCommon')) { class PluginPdfCommon {} }
    if (!class_exists('QueryFunction')) { class QueryFunction { public static function now(...$a) { return ''; } } }
    if (!class_exists('Alert')) { class Alert { const END = 0; } }

    // Core item permissive stubs used by resources plugin
    if (!class_exists('Computer')) { class Computer extends \CommonDBTM {} }
    if (!class_exists('Monitor')) { class Monitor extends \CommonDBTM {} }
    if (!class_exists('NetworkEquipment')) { class NetworkEquipment extends \CommonDBTM {} }
    if (!class_exists('Peripheral')) { class Peripheral extends \CommonDBTM {} }
    if (!class_exists('Phone')) { class Phone extends \CommonDBTM {} }
    if (!class_exists('Printer')) { class Printer extends \CommonDBTM {} }
    if (!class_exists('ConsumableItem')) { class ConsumableItem extends \CommonDBTM {} }
    if (!class_exists('ComputerType')) { class ComputerType extends \CommonDBTM {} }
    if (!class_exists('PhoneType')) { class PhoneType extends \CommonDBTM {} }
    if (!class_exists('Appliance')) { class Appliance extends \CommonDBTM {} }
    if (!class_exists('Item_Problem')) { class Item_Problem extends \CommonDBTM {} }
    if (!class_exists('Item_Ticket')) { class Item_Ticket extends \CommonDBTM {} }

    // CronTask stub
    if (!class_exists('CronTask')) { class CronTask { const STATE_DISABLE = 0; } }

    // Light Toolbox extras used in some places
    if (!class_exists('Toolbox')) {
        class Toolbox { public static function append_params($url, $params = []) { return $url; } }
    }

    if (!class_exists('NotificationTarget')) {
        class NotificationTarget {
            public $input = [];
            public $notification_targets = [];
            public $notification_targets_labels = [];
            public function addTarget($type, $label = null) { }
            public function addToRecipientsList($data) { }
            public function addUserByField($field) { }
            public function getDistinctUserCriteria() { return []; }
            public function addTagToList(...$a) { }
            public function getAllEvents() { return []; }
        }
    }
}

// Namespaced DBAL helper used in some code paths (top-level namespaces, not nested)
namespace Glpi\DBAL {
    if (!class_exists('Glpi\\DBAL\\QueryFunction')) { class QueryFunction { public static function now(...$a) { return ''; } } }
}

// Minimal Adldap vendor shims to satisfy LDAP-related logic in plugins
namespace Adldap {
    class Adldap {}
}
namespace Adldap\Auth {
    class BindException extends \Exception {}
}
namespace Adldap\Models {
    class ModelNotFoundException extends \Exception {}
}

namespace {
    // Global auth helper used by some plugin code
    if (!class_exists('Auth')) { class Auth { public static function getMethodName(...$a) {} public static function getTypeName(...$a) {} } }
}
