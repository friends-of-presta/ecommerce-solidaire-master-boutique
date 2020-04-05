<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_3_0($module)
{
    Db::getInstance()->execute('
        ALTER TABLE `'._DB_PREFIX_.pSQL($module->table_name).'`
        ADD `list` LONGTEXT NOT NULL AFTER `max`
    ');

    // Update override to the latest version
    @rename(_PS_OVERRIDE_DIR_.'/classes/Address.php', _PS_OVERRIDE_DIR_.'/classes/'.mktime().'_ZipCodeZone_Address.php');
    @copy(dirname(__FILE__).'/../Address.php', _PS_OVERRIDE_DIR_.'/classes/Address.php');

    return $module;
}
