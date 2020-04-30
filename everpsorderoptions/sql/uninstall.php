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

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'everpsorderoptions_field`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'everpsorderoptions_field_lang`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'everpsorderoptions_option`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'everpsorderoptions_option_lang`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
