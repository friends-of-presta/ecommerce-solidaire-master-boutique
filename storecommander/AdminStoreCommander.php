<?php
/**
 * Store Commander
 *
 * @category administration
 * @author Store Commander - support@storecommander.com
 * @version 2016-04-01
 * @uses Prestashop modules
 * @since 2009
 * @copyright Copyright &copy; 2009-2015, Store Commander
 * @license commercial
 * All rights reserved! Copying, duplication strictly prohibited
 *
 * *****************************************
 * *           STORE COMMANDER             *
 * *   http://www.StoreCommander.com       *
 * *            V 2016-04-01               *
 * *****************************************
 *
 * Compatibility: PS version: 1.1 to 1.7
 *
 **/

if (version_compare(_PS_VERSION_, '1.5.0.0', '<')) {
    require(dirname(__FILE__) . "/AdminStoreCommander_1_4.php");
} else {
    require(dirname(__FILE__) . "/controllers/admin/AdminStoreCommander.php");
}

