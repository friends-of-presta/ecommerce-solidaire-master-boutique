<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_4_0($module)
{
    if (file_exists(_PS_ROOT_DIR_.'/override/classes/Address.php')) {
        @rename(_PS_OVERRIDE_DIR_.'classes/Address.php', _PS_OVERRIDE_DIR_.'classes/'.mktime().'_ZipCodeZone_Address.php');
    }

    $module->registerHook('actionGetIDZoneByAddressID');

    return $module;
}
