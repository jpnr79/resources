<?php
// Global helper function stubs for PHPStan.
if (!function_exists('__')) {
    function __(string $msg, ...$args) { return $msg; }
}
if (!function_exists('_n')) {
    function _n(string $singular, string $plural, int $number, ...$args) { return $number === 1 ? $singular : $plural; }
}
if (!function_exists('_x')) {
    function _x(string $msg, string $context, ...$args) { return $msg; }
}
if (!function_exists('_sx')) {
    function _sx(string $msg, string $context, ...$args) { return $msg; }
}

if (!function_exists('getEntitiesRestrictCriteria')) {
    function getEntitiesRestrictCriteria(...$args) { return null; }
}

if (!function_exists('countDistinctElementsInTable')) {
    function countDistinctElementsInTable(...$args) { return 0; }
}

if (!function_exists('getSonsOf')) {
    function getSonsOf(...$args) { return []; }
}

if (!function_exists('isPluginItemType')) {
    function isPluginItemType(...$args) { return false; }
}
