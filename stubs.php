<?php
// Load global permissive stubs then plugin namespaced stubs for PHPStan runs.
$global = __DIR__ . '/../dev_global_stubs.php';
if (file_exists($global)) {
    require_once $global;
}
$namespaced = __DIR__ . '/stubs_namespaced.php';
if (file_exists($namespaced)) {
    require_once $namespaced;
}
$funcs = __DIR__ . '/stubs_functions.php';
if (file_exists($funcs)) {
    require_once $funcs;
}
$more = __DIR__ . '/stubs_global_more.php';
if (file_exists($more)) {
    require_once $more;
}
