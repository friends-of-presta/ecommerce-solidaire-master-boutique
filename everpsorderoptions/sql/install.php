<?php
/**
 * Project : everpsorderoptions
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = array();

// Form fields
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'everpsorderoptions_field` (
    `id_everpsorderoptions_field` int(11) NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) unsigned DEFAULT 1,
    `type` varchar(255) NOT NULL,
    `is_required` int(10) DEFAULT NULL,
    `manage_quantity` int(10) DEFAULT NULL,
    `quantity` int(10) DEFAULT NULL,
    `active` int(10) DEFAULT NULL,
    PRIMARY KEY (`id_everpsorderoptions_field`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'everpsorderoptions_field_lang` (
    `id_everpsorderoptions_field` int(11) NOT NULL AUTO_INCREMENT,
    `id_lang` int(10) unsigned NOT NULL,
    `field_title` varchar(255) NOT NULL,
    `field_description` text NOT NULL,
    PRIMARY KEY (`id_everpsorderoptions_field`, `id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'everpsorderoptions_option` (
    `id_everpsorderoptions_option` int(11) NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) unsigned DEFAULT 1,
    `id_field` int(10) unsigned NOT NULL,
    `manage_quantity` int(10) DEFAULT NULL,
    `quantity` int(10) DEFAULT NULL,
    `active` int(10) DEFAULT NULL,
    PRIMARY KEY (`id_everpsorderoptions_option`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'everpsorderoptions_option_lang` (
    `id_everpsorderoptions_option` int(11) NOT NULL AUTO_INCREMENT,
    `id_lang` int(10) unsigned NOT NULL,
    `option_title` varchar(255) NOT NULL,
    `option_value` varchar(255) NOT NULL,
    PRIMARY KEY (`id_everpsorderoptions_option`, `id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
