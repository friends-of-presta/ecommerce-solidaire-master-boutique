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

class MedMatomoLiteClass
{
    public function __construct($module)
    {
        $this->module = $module;
    }

    public function isoCode($domain = false)
    {
        $iso = $this->module->context->language->iso_code;

        if ($iso == 'fr') {
            return 'fr';
        } elseif ($domain) {
            return 'com';
        } else {
            return 'en';
        }
    }

    public function medJsonModuleFile()
    {
        $conf = Configuration::getMultiple(array('MED_JSON_TIME', 'MED_JSON_FILE'));

        if (!isset($conf['MED_JSON_TIME']) || $conf['MED_JSON_TIME'] < (time() - 604800)) {
            Configuration::updateValue('MED_JSON_TIME', time());
            $url_api = 'https://api-addons.prestashop.com/'._PS_VERSION_
                .'/contributor/all_products/'.$this->module->module_key
                .'/'.$this->module->context->language->iso_code
                .'/'.$this->module->context->country->iso_code;
            $conf['MED_JSON_FILE'] = Tools::file_get_contents($url_api);
            Configuration::updateValue('MED_JSON_FILE', $conf['MED_JSON_FILE']);
        }

        $modules = Tools::jsonDecode($conf['MED_JSON_FILE'], true);

        if (!is_array($modules) || isset($modules['errors'])) {
            Configuration::updateValue('MED_JSON_TIME', 0);
            return null;
        } else {
            return $modules;
        }
    }

    public function medJsonModuleRate($id = false, $hash = false)
    {
        if (!$id || !$hash) {
            return null;
        }

        $conf = Tools::jsonDecode(Configuration::get('MED_A_'.$id), true);

        if (!isset($conf['time']) || $conf['time'] < (time() - 604800)) {
            $conf['time'] = time();
            $iso = $this->module->context->language->iso_code;
            $country = $this->module->context->country->iso_code;
            $url_api = 'https://api-addons.prestashop.com/'._PS_VERSION_
                .'/contributor/product/'.$hash.'/'.$iso.'/'.$country;
            $result = Tools::file_get_contents($url_api);
            $module = Tools::jsonDecode($result, true);
            if (isset($module['products'][0]['nbRates'])) {
                $conf['nbRates'] = $module['products'][0]['nbRates'];
                $conf['avgRate'] = $module['products'][0]['avgRate']*2*10;
                $datas = Tools::jsonEncode($conf);
                Configuration::updateValue('MED_A_'.$id, $datas);
            } else {
                $conf = null;
            }
        }

        if (!is_array($conf)) {
            Configuration::deleteByName('MED_A_'.$id);
            return null;
        } else {
            return $conf;
        }
    }
}
