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

class StoreCommander extends Module
{

    public $currentUrl = '';
    public $_err = array();
    private $url_zip_SC = "http://www.storecommander.com/files/StoreCommander.zip";
    public $context;

    public function __construct()
    {
        $this->name = 'storecommander';
        $this->tab = 'administration';
        $this->version = '2.0.2';
        $this->author = 'Store Commander';
        $this->module_key = '7d3e55b97635c528975fbd7e82089a67';
        parent::__construct();

        $this->currentUrl = $this->getCurrentUrl();
        $token = Tools::getValue("token", "");
        $this->baseParams = "?controller=AdminModules&configure=storecommander&token=" . $token;
        if (version_compare(_PS_VERSION_, '1.5.0.0', '<')) {
            $this->baseParams = "?tab=AdminModules&configure=storecommander&token=" . $token;
        }
        $this->page = basename(__FILE__, '.php');

        $this->displayName = $this->l('Store Commander Installer');
        $this->description = $this->l('Install Store Commander to boost your backoffice.');
        $this->confirmUninstall = $this->l('Warning! This action definitely uninstall Store Commander!');
        $warning = '';
        if (!is_writeable(_PS_ROOT_DIR_ . '/modules/' . $this->name)) {
            $warning .= ' ' . $this->l('The /modules/storecommander folder must be writable.');
        }
        if (!Configuration::get('SC_INSTALLED')) {
            $warning .= ' ' . $this->l('Store Commander is not installed!');
        }
        if ($warning != '') {
            $this->warning = $warning;
        }
    }

    public function install()
    {
        if (!parent::install()
            || !Configuration::updateValue('SC_FOLDER_HASH', Tools::substr(md5(date("YmdHis") . _COOKIE_KEY_), 0, 11))
            || !$this->createSCFolder(Configuration::get('SC_FOLDER_HASH'))
            || !Configuration::updateValue('SC_INSTALLED', false)
        ) {
            return false;
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<'))
            Tools::redirectAdmin($this->currentUrl . $this->baseParams);
        return true;
    }

    public function uninstall()
    {
        $qaccess = Db::getInstance()->ExecuteS("SELECT GROUP_CONCAT(`id_quick_access`) AS qaccess FROM `" . _DB_PREFIX_ . "quick_access` WHERE `link` LIKE '%storecommander%'");
        if (count($qaccess) && $qaccess[0]['qaccess'] != '') {
            Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "quick_access` WHERE id_quick_access IN (" . psql($qaccess[0]['qaccess']) . ")");
            Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "quick_access_lang` WHERE id_quick_access IN (" . psql($qaccess[0]['qaccess']) . ")");
        }
        $tab = new Tab(Tab::getIdFromClassName('AdminStoreCommander'));
        $tab->delete();
        $this->removeSCFolder(Configuration::get('SC_FOLDER_HASH'));
        Configuration::deleteByName('SC_FOLDER_HASH');
        Configuration::deleteByName('SC_INSTALLED');
        Configuration::deleteByName('SC_SETTINGS');
        Configuration::deleteByName('SC_LICENSE_DATA');
        Configuration::deleteByName('SC_LICENSE_KEY');
        Configuration::deleteByName('SC_VERSIONS');
        Configuration::deleteByName('SC_VERSIONS_LAST');
        Configuration::deleteByName('SC_VERSIONS_LAST_CHECK');

        parent::uninstall();
        return true;
    }

    private function createSCFolder($folder)
    {
        if (!is_dir(dirname(__FILE__) . '/' . $folder)) {
            return mkdir(dirname(__FILE__) . '/' . $folder);
        }
    }

    private function removeSCFolder($folder)
    {
        if (is_dir(dirname(__FILE__) . '/' . $folder)) {
            $this->rrmdir(dirname(__FILE__) . '/' . $folder);
        }
        return true;
    }

    private function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            @rmdir($dir);
        }
        return true;
    }

    public function getContent()
    {
        if (class_exists('Context'))
        {
            $this->context = Context::getContext();
        }
        else
        {
            global $smarty, $cookie;
            $this->context = new StdClass();
            $this->context->smarty = $smarty;
            $this->context->cookie = $cookie;
        }
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>='))
        {
            $this->context->controller->addJS(__PS_BASE_URI__.'modules/' . $this->name . '/views/js/loader/jquery.loader-min.js');
            $this->context->controller->addCSS(__PS_BASE_URI__.'modules/' . $this->name . '/views/css/admin.css', 'all');
        }

        $sql = "SELECT class_name FROM " . _DB_PREFIX_ . "tab
				WHERE class_name = 'AdminStoreCommander'";
        $exists = Db::getInstance()->ExecuteS($sql);
        if (empty($exists[0]) || $exists[0]["class_name"] != "AdminStoreCommander") {
            $tab = new Tab();
            $tab->class_name = 'AdminStoreCommander';
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                $adminModuleParentTab = Tab::getInstanceFromClassName('AdminModules');
                $tab->id_parent = (int)$adminModuleParentTab->id_parent;
            }
            elseif (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                $tab->id_parent = (int)(Tab::getIdFromClassName('AdminParentModules'));
            } else {
                $tab->id_parent = (int)(Tab::getIdFromClassName('AdminModules'));
            }
            $tab->module = $this->name;
            $tab->name[Configuration::get('PS_LANG_DEFAULT')] = 'StoreCommander';
            $tab->name[Language::getIdByIso('en')] = 'StoreCommander';
            $tab->name[Language::getIdByIso('fr')] = 'StoreCommander';
            $tab->add();
        }

        $this->context->smarty->assign(array(
            'currentUrl' => $this->currentUrl,
            'baseParams' => $this->baseParams
        ));

        $_html = '';
        $_html .= $this->displayStep(Tools::getValue("sc_step"));
        return $_html;
    }

    private function displayStep($step)
    {
        $_html = '';
        switch ((int)$step) {
            case 1 :
                if (Configuration::get('SC_INSTALLED')) {
                    Tools::redirectAdmin($this->currentUrl . $this->baseParams . '&sc_step=3');
                } else {
                    if ($this->isSCFolderReady()) {
                        Tools::redirectAdmin($this->currentUrl . $this->baseParams . '&sc_step=3');
                    } else {
                        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                            return $_html.$this->display(__FILE__, 'etape_preinstall_1.5.tpl');
                        } else {
                            return $_html.$this->display(__FILE__, 'views/templates/hook/etape_preinstall_1.4.tpl');
                        }
                    }
                }
                break;

            case 2 :
                if (Configuration::get('SC_INSTALLED') || $this->isSCFolderReady()) {
                    Tools::redirectAdmin($this->currentUrl . $this->baseParams . '&sc_step=3');
                } else {
                    if (!$this->downloadExtractSC()) {
                        $this->_err[] = Tools::displayError('Error downloading StoreCommander');
                        $_html = $this->displayErrors($this->_err);
                    } else {
                        $this->createTab();
                        Configuration::updateValue('SC_INSTALLED', true);
                        if (file_exists(dirname(__FILE__).'/license.php'))
                            @copy(dirname(__FILE__).'/license.php',_PS_MODULE_DIR_.$this->name.'/'.Configuration::get('SC_FOLDER_HASH').'/SC/license.php');
                        Tools::redirectAdmin($this->currentUrl . $this->baseParams . '&sc_step=3');
                    }
                }
                break;


            case 3 :

                if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                    $this->context->smarty->assign(array(
                        'token' => Tools::getAdminToken('AdminStoreCommander' . (int)(Tab::getIdFromClassName('AdminStoreCommander')) . (int)($this->context->employee->id))
                    ));
                    return $_html.$this->display(__FILE__, 'etape_postinstall_1.5.tpl');
                } else {
                    global $cookie;
                    $this->context->smarty->assign(array(
                        'token' => Tools::getAdminToken('AdminStoreCommander' . (int)(Tab::getIdFromClassName('AdminStoreCommander')) . (int)($cookie->id_employee))
                    ));
                    return $_html.$this->display(__FILE__, 'views/templates/hook/etape_postinstall_1.4.tpl');
                }

                break;

            default :
                if (!$this->isSCFolderReady()) {
                    Tools::redirectAdmin($this->currentUrl . $this->baseParams . '&sc_step=1');
                } else {
                    Tools::redirectAdmin($this->currentUrl . $this->baseParams . '&sc_step=3');
                }
                break;
        }
        return $_html;
    }

    private function createTab()
    {
        if (!Tab::getIdFromClassName('AdminStoreCommander')) {
            $tab = new Tab();
            $tab->class_name = 'AdminStoreCommander';
            $tab->id_parent = (int)(Tab::getIdFromClassName((version_compare(_PS_VERSION_, '1.5.0.0',
                '>=') ? 'AdminParentModules' : 'AdminModules')));
            $tab->module = $this->name;
            foreach (Language::getLanguages(false) AS $language) {
                $tab->name[$language["id_lang"]] = 'Store Commander';
            }
            $tab->add();
            @copy(_PS_MODULE_DIR_ . $this->name . '/logo.gif', _PS_IMG_DIR_ . 't/AdminStoreCommander.gif');
        }

        $sql = 'SELECT id_quick_access AS id FROM `' . _DB_PREFIX_ . 'quick_access` q WHERE q.`link` LIKE \'%AdminStoreCommander%\'';
        $result = Db::getInstance()->getRow($sql);
        if (count($result) == 0) {
            $quickAccess = new QuickAccess();
            $tmp = array();
            $languages = Language::getLanguages();
            foreach ($languages AS $lang) {
                $tmp[$lang['id_lang']] = "Store Commander";
            }
            $quickAccess->name = $tmp;
            if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                $quickAccess->link = "index.php?controller=AdminStoreCommander";
            } else {
                $quickAccess->link = "index.php?tab=AdminStoreCommander";
            }
            $quickAccess->new_window = true;
            $quickAccess->add();
        }
    }

    public function isSCFolderReady()
    {
        if (file_exists(dirname(__FILE__) . '/' . Configuration::get('SC_FOLDER_HASH') . '/SC/index.php')) {
            return true;
        }
        return false;
    }

    public function sc_file_get_contents($param, $querystring = '')
    {
        $result = '';
        if (function_exists('file_get_contents') && version_compare(_PS_VERSION_, '1.4.0.0', '>=')) {
            @$result = Tools::file_get_contents($param,false,null,30);
        }
        if ($result == '' && function_exists('curl_init')) {
            $curl = curl_init();
            $header = '';
            $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
            $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
            $header[] = "Cache-Control: max-age=0";
            $header[] = "Connection: keep-alive";
            $header[] = "Keep-Alive: 300";
            $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
            $header[] = "Accept-Language: en-us,en;q=0.5";
            $header[] = "Pragma: ";
            curl_setopt($curl, CURLOPT_URL, $param);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Store Commander (http://www.storecommander.com)');
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $querystring);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
            $result = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);
            if ((int)$info['http_code'] != 200) {
                return '';
            }
        }
        return $result;
    }

    private function downloadExtractSC()
    {
        $data = $this->sc_file_get_contents($this->url_zip_SC);
        file_put_contents(_PS_MODULE_DIR_ . $this->name . '/' . basename($this->url_zip_SC), $data);
        return $this->extractArchive(_PS_MODULE_DIR_ . $this->name . '/' . basename($this->url_zip_SC));
    }


    private function extractArchive($file)
    {
        $success = true;
		if(version_compare(_PS_VERSION_, '1.4.0.0', '>='))
		{
            if (file_exists(_PS_TOOL_DIR_ . 'pclzip/pclzip.lib.php')){
                require_once(_PS_TOOL_DIR_ . 'pclzip/pclzip.lib.php');
                $zip = new PclZip($file);
                $list = $zip->extract(PCLZIP_OPT_PATH,
                    _PS_MODULE_DIR_ . $this->name . '/' . Configuration::get('SC_FOLDER_HASH'));
                foreach ($list as $extractedFile) {
                    if ($extractedFile['status'] != 'ok') {
                        $success = false;
                    }
                }
            } else {
                if (class_exists('ZipArchive', false))
                {
                    $zip = new ZipArchive();
                    if ($zip->open($file) === true AND $zip->extractTo(_PS_MODULE_DIR_ . $this->name . '/' . Configuration::get('SC_FOLDER_HASH')) AND $zip->close())
                        $success = true;
                    else
                        $success = false;
                }
                else
                    $success = false;
            }
		}
		else
		{
			if (Tools::substr($file, -4) == '.zip')
			{
				if (class_exists('ZipArchive', false))
				{
					$zip = new ZipArchive();
					if ($zip->open($file) === true AND $zip->extractTo(_PS_MODULE_DIR_ . $this->name . '/' . Configuration::get('SC_FOLDER_HASH')) AND $zip->close())
						$success = true;
					else
						$success = false;
				}
				else
					$success = false;
			}
			else
			{
				$archive = new Archive_Tar($file);
				if ($archive->extract(_PS_MODULE_DIR_ . $this->name . '/' . Configuration::get('SC_FOLDER_HASH')))
					$success = true;
				else
					$success = false;
			}
		}
        @unlink($file);
        return $success;
    }

    public function displayErrors($errors)
    {
        if (is_array($errors) && count($errors)) {

            $_html = '';
            $this->context->smarty->assign(array(
                'errors' => $errors
            ));
            $_html = $this->display(__FILE__, 'views/templates/hook/errors.tpl');
            return $_html;
        }
    }

    public function  getCurrentUrl()
    {
        $pageURL = 'http';
        if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        }
        $exp = explode("?", $pageURL);
        $pageURL = $exp[0];
        return $pageURL;
    }
}
