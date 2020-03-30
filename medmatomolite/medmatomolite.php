<?php
/**
 * NOTICE OF LICENSE
 *
 * Read in the module
 *
 * @author    Mediacom87 <support@mediacom87.net>
 * @copyright 2008-today Mediacom87
 * @license   define in the module
 */

if (!defined('_TB_VERSION_')
    && !defined('_PS_VERSION_')) {
    exit;
}

include_once dirname(__FILE__).'/class/mediacom87.php';

class MedMatomoLite extends Module
{
    public $smarty;
    public $context;
    public $controller;
    private $errors = array();
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'medmatomolite';
        $this->version = '1.0.0';
        $this->tab = 'front_office_features';
        $this->author = 'Mediacom87';
        $this->need_instance = 0;
        $this->module_key = '';
        $this->addons_id = ''; //
        $this->ps_versions_compliancy = array('min' => '1.5.0.0', 'max' => '1.7.99.99');

        parent::__construct();

        $this->displayName = $this->l('Matomo tracking module');
        $this->description = $this->l(
            'Easily insert the tracking script proposed by Matomo'
        );

        $this->bootstrap = true;

        $this->mediacom87 = new MedMatomoLiteClass($this);

        $this->conf = unserialize(Configuration::get($this->name));

        $this->tpl_path = _PS_MODULE_DIR_.$this->name;
    }

    public function install()
    {
        if (!parent::install()
            || !$this->defaultConf()
            || !$this->registerHook('displayheader')) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()
            || !Configuration::deleteByName($this->name)) {
            return false;
        }

        return true;
    }

    public function getContent($tab = 'AdminModules')
    {
        $output = '';

        $form_url = AdminController::$currentIndex
            .'&amp;configure='.$this->name
            .'&amp;token='.Tools::getAdminToken(
                $tab
                .(int)Tab::getIdFromClassName($tab)
                .(int)$this->context->cookie->id_employee
            );

        if (Tools::isSubmit('saveconf')) {
            $this->postProcess();

            if (count($this->errors)) {
                $output .= $this->displayError(implode('<br />', $this->errors));
            } else {
                Tools::redirectAdmin(html_entity_decode($form_url).'&conf=6');
            }
        }
        if (Tools::isSubmit('eraseLogo')) {
            $this->eraseLogo();

            if (count($this->errors)) {
                $output .= $this->displayError(implode('<br />', $this->errors));
            } else {
                Tools::redirectAdmin(html_entity_decode($form_url).'&conf=6');
            }
        }

        if (isset($this->addons_id) && $this->addons_id) {
            $json_rates = $this->mediacom87->medJsonModuleRate($this->addons_id, $this->module_key);
        } else {
            $json_rates = false;
        }

        if (isset($this->module_key) && $this->module_key) {
            $json_modules = $this->mediacom87->medJsonModuleFile();
        } else {
            $json_modules = null;
        }

        $this->context->smarty->assign(
            array(
                'form_url' => $form_url,
                'addons_id' => $this->addons_id,
                'tpl_path' => $this->tpl_path,
                'img_path' => $this->_path.'views/img/',
                'languages' => Language::getLanguages(false),
                'description' => $this->description,
                'author' => $this->author,
                'name' => $this->name,
                'version' => $this->version,
                'ps_version' => defined('_PS_VERSION_') ? _PS_VERSION_ : null,
                'tb_version' => defined('_TB_VERSION_') ? _TB_VERSION_ : null,
                'php_version' => phpversion(),
                'iso_code' => $this->mediacom87->isoCode(),
                'iso_domain' => $this->mediacom87->isoCode(true),
                'id_active_lang' => $this->context->language->id,
                'json_modules' => $json_modules,
                'json_rates' => $json_rates,
                'config' => $this->conf,
            )
        );
        $this->context->controller->addJS($this->_path.'libraries/js/riotcompiler.min.js');
        $this->context->controller->addJS($this->_path.'libraries/js/pageloader.js');
        $this->context->controller->addJS($this->_path.'views/js/admin.js');

        $this->context->controller->addCSS($this->_path.'views/css/back.css', 'all');

        if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            $this->context->controller->addJS('https://use.fontawesome.com/8ebcaf88e9.js');
            $this->context->controller->addCSS($this->_path.'views/css/button.css', 'all');
        }

        $output .= $this->display(__FILE__, 'views/templates/admin/admin.tpl');
        $output .= $this->display(__FILE__, 'libraries/prestui/ps-tags.tpl');

        return $output;
    }

    public function defaultConf()
    {
        $conf = array();

        $conf['matomo_url'] = 'https://analytics.mediacom87.eu/';
        $conf['matomo_siteId'] = '';

        if (!Configuration::updateGlobalValue($this->name, serialize($conf))) {
            return false;
        }
        return true;
    }

    public function postProcess()
    {
        $strings = array(
            'matomo_url',
        );

        foreach ($strings as $s) {
            if ($s == 'matomo_url') {
                $matomo_url = trim((string)Tools::getValue($s));
                if (Validate::isAbsoluteUrl($matomo_url)
                    && Tools::substr($matomo_url, 0, 4) == 'http') {
                    if (Tools::substr($matomo_url, -1) != '/') {
                        $matomo_url .= '/';
                    }
                    $this->conf[$s] = $matomo_url;
                } else {
                    $this->conf[$s] = 'https://analytics.mediacom87.eu/';
                    $this->errors[] = $this->l(
                        'Url of proposed tracking does not seem valid, thank you to respect the instructions.'
                    );
                }
            } else {
                $this->conf[$s] = (string)Tools::getValue($s);
            }
        }

        $ints = array(
            'matomo_siteId',
        );

        foreach ($ints as $i) {
            $this->conf[$i] = (int)Tools::getValue($i);
        }

        if (!Configuration::updateValue($this->name, serialize($this->conf))) {
            $this->errors[] = $this->l('Settings update error');
            return false;
        }

        return true;
    }

    public function hookdisplayHeader()
    {
        return $this->hookHeader();
    }

    public function hookHeader()
    {
        if ($this->active) {
            if (!$this->isCached('header.tpl', $this->getCacheId())) {
                $this->context->smarty->assign(
                    array(
                        'config' => $this->conf,
                    )
                );
            }

            return $this->display(__FILE__, 'header.tpl', $this->getCacheId());
        }
    }
}
