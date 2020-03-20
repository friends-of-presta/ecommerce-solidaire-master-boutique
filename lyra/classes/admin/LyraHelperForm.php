<?php
/**
 * Copyright © Lyra Network.
 * This file is part of Lyra Collect plugin for PrestaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

if (! defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class that renders payment module administration interface.
 */
class LyraHelperForm
{
    private function __construct()
    {
        // Do not instantiate this class.
    }

    public static function getAdminFormContext()
    {
        $context = Context::getContext();

        /* @var Lyra */
        $lyra = Module::getInstanceByName('lyra');

        $languages = array();
        foreach (LyraApi::getSupportedLanguages() as $code => $label) {
            $languages[$code] = $lyra->l($label, 'lyrahelperform');
        }
        asort($languages);

        $category_options = array(
            'FOOD_AND_GROCERY' => $lyra->l('Food and grocery', 'lyrahelperform'),
            'AUTOMOTIVE' => $lyra->l('Automotive', 'lyrahelperform'),
            'ENTERTAINMENT' => $lyra->l('Entertainment', 'lyrahelperform'),
            'HOME_AND_GARDEN' => $lyra->l('Home and garden', 'lyrahelperform'),
            'HOME_APPLIANCE' => $lyra->l('Home appliance', 'lyrahelperform'),
            'AUCTION_AND_GROUP_BUYING' => $lyra->l('Auction and group buying', 'lyrahelperform'),
            'FLOWERS_AND_GIFTS' => $lyra->l('Flowers and gifts', 'lyrahelperform'),
            'COMPUTER_AND_SOFTWARE' => $lyra->l('Computer and software', 'lyrahelperform'),
            'HEALTH_AND_BEAUTY' => $lyra->l('Health and beauty', 'lyrahelperform'),
            'SERVICE_FOR_INDIVIDUAL' => $lyra->l('Service for individual', 'lyrahelperform'),
            'SERVICE_FOR_BUSINESS' => $lyra->l('Service for business', 'lyrahelperform'),
            'SPORTS' => $lyra->l('Sports', 'lyrahelperform'),
            'CLOTHING_AND_ACCESSORIES' => $lyra->l('Clothing and accessories', 'lyrahelperform'),
            'TRAVEL' => $lyra->l('Travel', 'lyrahelperform'),
            'HOME_AUDIO_PHOTO_VIDEO' => $lyra->l('Home audio, photo, video', 'lyrahelperform'),
            'TELEPHONY' => $lyra->l('Telephony', 'lyrahelperform')
        );

        // Get documentation links.
        $doc_files = array();
        $filenames = glob(_PS_MODULE_DIR_ . 'lyra/installation_doc/' . LyraTools::getDocPattern());

        $doc_languages = array(
            'fr' => 'Français',
            'en' => 'English',
            'es' => 'Español',
            'de' => 'Deutsch'
            // Complete when other languages are managed.
        );

        foreach ($filenames as $filename) {
            $base_filename = basename($filename, '.pdf');
            $lang = Tools::substr($base_filename, -2); // Extract language code.

            $doc_files[$base_filename . '.pdf'] = $doc_languages[$lang];
        }

        $placeholders = self::getArrayConfig('LYRA_STD_REST_PLACEHLDR');
        if (empty($placeholders)) {
            $placeholders = array('pan' => '', 'expiry' => '', 'cvv' => '');
        }

        $enabledCountries = Country::getCountries((int) $context->language->id, true);
        $all_countries =    Country::getCountries((int) $context->language->id, false);
        $countryList = array();
        foreach ($enabledCountries as $value) {
            $countryList['ps_countries'][$value['iso_code']] = $value['name'];
        }

        foreach (LyraTools::$submodules as $key => $module) {
            $module_class_name = 'Lyra' . $module.'Payment';
            $instance_module = new $module_class_name();
            if (method_exists($instance_module, 'getCountries') && ! empty($instance_module->getCountries())) {
                $submodule_specific_countries = $instance_module->getCountries();
                foreach ($submodule_specific_countries as $country) {
                    if (isset($countryList['ps_countries'][$country])) {
                        $countryList[$key][$country] = $countryList['ps_countries'][$country];
                    }
                }
            }
        }

        foreach ($all_countries as $value) {
            if ($value['iso_code'] === 'FR') {
                $countryList['FULLCB']['FR'] = $value['name'];
                break;
            }
        }

        $tpl_vars = array(
            'lyra_support_email' => LyraTools::getDefault('SUPPORT_EMAIL'),
            'lyra_plugin_version' => LyraTools::getDefault('PLUGIN_VERSION'),
            'lyra_gateway_version' => LyraTools::getDefault('GATEWAY_VERSION'),

            'lyra_plugin_features' => LyraTools::$plugin_features,
            'lyra_request_uri' => $_SERVER['REQUEST_URI'],

            'lyra_doc_files' => $doc_files,
            'lyra_enable_disable_options' => array(
                'False' => $lyra->l('Disabled', 'lyrahelperform'),
                'True' => $lyra->l('Enabled', 'lyrahelperform')
            ),
            'lyra_mode_options' => array(
                'TEST' => $lyra->l('TEST', 'lyrahelperform'),
                'PRODUCTION' => $lyra->l('PRODUCTION', 'lyrahelperform')
            ),
            'lyra_language_options' => $languages,
            'lyra_validation_mode_options' => array(
                '' => $lyra->l('Bank Back Office configuration', 'lyrahelperform'),
                '0' => $lyra->l('Automatic', 'lyrahelperform'),
                '1' => $lyra->l('Manual', 'lyrahelperform')
            ),
            'lyra_payment_cards_options' => array('' => $lyra->l('ALL', 'lyrahelperform')) + LyraTools::getSupportedCardTypes(),
            'lyra_multi_payment_cards_options' => array('' => $lyra->l('ALL', 'lyrahelperform')) + LyraTools::getSupportedMultiCardTypes(),
            'lyra_category_options' => $category_options,
            'lyra_yes_no_options' => array(
                'False' => $lyra->l('No', 'lyrahelperform'),
                'True' => $lyra->l('Yes', 'lyrahelperform')
            ),
            'lyra_delivery_type_options' => array(
                'PACKAGE_DELIVERY_COMPANY' => $lyra->l('Delivery company', 'lyrahelperform'),
                'RECLAIM_IN_SHOP' => $lyra->l('Reclaim in shop', 'lyrahelperform'),
                'RELAY_POINT' => $lyra->l('Relay point', 'lyrahelperform'),
                'RECLAIM_IN_STATION' => $lyra->l('Reclaim in station', 'lyrahelperform')
            ),
            'lyra_delivery_speed_options' => array(
                'STANDARD' => $lyra->l('Standard', 'lyrahelperform'),
                'EXPRESS' => $lyra->l('Express', 'lyrahelperform'),
                'PRIORITY' => $lyra->l('Priority', 'lyrahelperform')
            ),
            'lyra_delivery_delay_options' => array(
                'INFERIOR_EQUALS' => $lyra->l('<= 1 hour', 'lyrahelperform'),
                'SUPERIOR' => $lyra->l('> 1 hour', 'lyrahelperform'),
                'IMMEDIATE' => $lyra->l('Immediate', 'lyrahelperform'),
                'ALWAYS' => $lyra->l('24/7', 'lyrahelperform')
            ),
            'lyra_failure_management_options' => array(
                LyraTools::ON_FAILURE_RETRY => $lyra->l('Go back to checkout', 'lyrahelperform'),
                LyraTools::ON_FAILURE_SAVE => $lyra->l('Save order and go back to order history', 'lyrahelperform')
            ),
            'lyra_cart_management_options' => array(
                LyraTools::EMPTY_CART => $lyra->l('Empty cart to avoid amount errors', 'lyrahelperform'),
                LyraTools::KEEP_CART => $lyra->l('Keep cart (PrestaShop default behavior)', 'lyrahelperform')
            ),
            'lyra_card_data_mode_options' => array(
                '1' => $lyra->l('Bank data acquisition on payment gateway', 'lyrahelperform'),
                '2' => $lyra->l('Card type selection on merchant site', 'lyrahelperform'),
                '4' => $lyra->l('Payment page integrated to checkout process (iframe mode)', 'lyrahelperform'),
                '5' => $lyra->l('Embedded payment fields (REST API)', 'lyrahelperform')
            ),
            'lyra_countries_options' => array(
                '1' => $lyra->l('All Allowed Countries', 'lyrahelperform'),
                '2' => $lyra->l('Specific Countries', 'lyrahelperform')
            ),
            'lyra_countries_list' => $countryList,
            'lyra_card_selection_mode_options' => array(
                '1' => $lyra->l('On payment gateway', 'lyrahelperform'),
                '2' => $lyra->l('On merchant site', 'lyrahelperform')
            ),
            'lyra_default_multi_option' => array(
                'label' => '',
                'min_amount' => '',
                'max_amount' => '',
                'contract' => '',
                'count' => '',
                'period' => '',
                'first' => ''
            ),
            'lyra_default_oney_option' => array(
                'label' => '',
                'code' => '',
                'min_amount' => '',
                'max_amount' => '',
                'count' => '',
                'rate' => ''
            ),
            'lyra_default_other_payment_means_option' => array(
                'title' => '',
                'code' => '',
                'min_amount' => '',
                'max_amount' => '',
                'validation' => '-1',
                'capture' => '',
                'cart' => 'False'
            ),
            'lyra_rest_display_mode_options' => array(
                'embedded' => $lyra->l('Directly on merchant site', 'lyrahelperform'),
                'popin' => $lyra->l('In a pop-in', 'lyrahelperform')
            ),
            'lyra_std_rest_theme_options' => array(
                'classic' =>  'Classic',
                'material' => 'Material'
            ),

            'prestashop_categories' => Category::getCategories((int) $context->language->id, true, false),
            'prestashop_languages' => Language::getLanguages(false),
            'prestashop_lang' => Language::getLanguage((int) $context->language->id),
            'prestashop_carriers' => Carrier::getCarriers(
                (int) $context->language->id,
                true,
                false,
                false,
                null,
                Carrier::ALL_CARRIERS
            ),
            'prestashop_groups' => self::getAuthorizedGroups(),
            'lyra_sepa_mandate_mode_options' => array(
                'PAYMENT' => $lyra->l('One-off SEPA direct debit', 'lyrahelperform'),
                'REGISTER_PAY' => $lyra->l('Register a recurrent SEPA mandate with direct debit', 'lyrahelperform'),
                'REGISTER' => $lyra->l('Register a recurrent SEPA mandate without direct debit', 'lyrahelperform')
            ),

            'LYRA_ENABLE_LOGS' => Configuration::get('LYRA_ENABLE_LOGS'),

            'LYRA_SITE_ID' => Configuration::get('LYRA_SITE_ID'),
            'LYRA_KEY_TEST' => Configuration::get('LYRA_KEY_TEST'),
            'LYRA_KEY_PROD' => Configuration::get('LYRA_KEY_PROD'),
            'LYRA_MODE' => Configuration::get('LYRA_MODE'),
            'LYRA_SIGN_ALGO' => Configuration::get('LYRA_SIGN_ALGO'),
            'LYRA_PLATFORM_URL' => Configuration::get('LYRA_PLATFORM_URL'),
            'LYRA_NOTIFY_URL' => self::getIpnUrl(),

            'LYRA_PUBKEY_TEST' => Configuration::get('LYRA_PUBKEY_TEST'),
            'LYRA_PRIVKEY_TEST' => Configuration::get('LYRA_PRIVKEY_TEST'),
            'LYRA_PUBKEY_PROD' => Configuration::get('LYRA_PUBKEY_PROD'),
            'LYRA_PRIVKEY_PROD' => Configuration::get('LYRA_PRIVKEY_PROD'),
            'LYRA_RETKEY_TEST' => Configuration::get('LYRA_RETKEY_TEST'),
            'LYRA_RETKEY_PROD' => Configuration::get('LYRA_RETKEY_PROD'),
            'LYRA_REST_NOTIFY_URL' => self::getIpnUrl(),

            'LYRA_DEFAULT_LANGUAGE' => Configuration::get('LYRA_DEFAULT_LANGUAGE'),
            'LYRA_AVAILABLE_LANGUAGES' => ! Configuration::get('LYRA_AVAILABLE_LANGUAGES') ?
                                            array('') :
                                            explode(';', Configuration::get('LYRA_AVAILABLE_LANGUAGES')),
            'LYRA_DELAY' => Configuration::get('LYRA_DELAY'),
            'LYRA_VALIDATION_MODE' => Configuration::get('LYRA_VALIDATION_MODE'),

            'LYRA_THEME_CONFIG' => self::getLangConfig('LYRA_THEME_CONFIG'),
            'LYRA_SHOP_NAME' => Configuration::get('LYRA_SHOP_NAME'),
            'LYRA_SHOP_URL' => Configuration::get('LYRA_SHOP_URL'),

            'LYRA_3DS_MIN_AMOUNT' => self::getArrayConfig('LYRA_3DS_MIN_AMOUNT'),

            'LYRA_REDIRECT_ENABLED' => Configuration::get('LYRA_REDIRECT_ENABLED'),
            'LYRA_REDIRECT_SUCCESS_T' => Configuration::get('LYRA_REDIRECT_SUCCESS_T'),
            'LYRA_REDIRECT_SUCCESS_M' => self::getLangConfig('LYRA_REDIRECT_SUCCESS_M'),
            'LYRA_REDIRECT_ERROR_T' => Configuration::get('LYRA_REDIRECT_ERROR_T'),
            'LYRA_REDIRECT_ERROR_M' => self::getLangConfig('LYRA_REDIRECT_ERROR_M'),
            'LYRA_RETURN_MODE' => Configuration::get('LYRA_RETURN_MODE'),
            'LYRA_FAILURE_MANAGEMENT' => Configuration::get('LYRA_FAILURE_MANAGEMENT'),
            'LYRA_CART_MANAGEMENT' => Configuration::get('LYRA_CART_MANAGEMENT'),

            'LYRA_SEND_CART_DETAIL' => Configuration::get('LYRA_SEND_CART_DETAIL'),
            'LYRA_COMMON_CATEGORY' => Configuration::get('LYRA_COMMON_CATEGORY'),
            'LYRA_CATEGORY_MAPPING' => self::getArrayConfig('LYRA_CATEGORY_MAPPING'),
            'LYRA_SEND_SHIP_DATA' => Configuration::get('LYRA_SEND_SHIP_DATA'),
            'LYRA_ONEY_SHIP_OPTIONS' => self::getArrayConfig('LYRA_ONEY_SHIP_OPTIONS'),

            'LYRA_STD_TITLE' => self::getLangConfig('LYRA_STD_TITLE'),
            'LYRA_STD_ENABLED' => Configuration::get('LYRA_STD_ENABLED'),
            'LYRA_STD_AMOUNTS' => self::getArrayConfig('LYRA_STD_AMOUNTS'),
            'LYRA_STD_DELAY' => Configuration::get('LYRA_STD_DELAY'),
            'LYRA_STD_VALIDATION' => Configuration::get('LYRA_STD_VALIDATION'),
            'LYRA_STD_PAYMENT_CARDS' => ! Configuration::get('LYRA_STD_PAYMENT_CARDS') ?
                                            array('') :
                                            explode(';', Configuration::get('LYRA_STD_PAYMENT_CARDS')),
            'LYRA_STD_PROPOSE_ONEY' => Configuration::get('LYRA_STD_PROPOSE_ONEY'),
            'LYRA_STD_CARD_DATA_MODE' => Configuration::get('LYRA_STD_CARD_DATA_MODE'),
            'LYRA_STD_REST_DISPLAY_MODE' => Configuration::get('LYRA_STD_REST_DISPLAY_MODE'),
            'LYRA_STD_REST_THEME' => Configuration::get('LYRA_STD_REST_THEME'),
            'LYRA_STD_REST_PLACEHLDR' => $placeholders,
            'LYRA_STD_REST_ATTEMPTS' => Configuration::get('LYRA_STD_REST_ATTEMPTS'),
            'LYRA_STD_1_CLICK_PAYMENT' => Configuration::get('LYRA_STD_1_CLICK_PAYMENT'),
            'LYRA_STD_CANCEL_IFRAME' => Configuration::get('LYRA_STD_CANCEL_IFRAME'),

            'LYRA_MULTI_TITLE' => self::getLangConfig('LYRA_MULTI_TITLE'),
            'LYRA_MULTI_ENABLED' => Configuration::get('LYRA_MULTI_ENABLED'),
            'LYRA_MULTI_AMOUNTS' => self::getArrayConfig('LYRA_MULTI_AMOUNTS'),
            'LYRA_MULTI_DELAY' => Configuration::get('LYRA_MULTI_DELAY'),
            'LYRA_MULTI_VALIDATION' => Configuration::get('LYRA_MULTI_VALIDATION'),
            'LYRA_MULTI_CARD_MODE' => Configuration::get('LYRA_MULTI_CARD_MODE'),
            'LYRA_MULTI_PAYMENT_CARDS' => ! Configuration::get('LYRA_MULTI_PAYMENT_CARDS') ?
                                            array('') :
                                            explode(';', Configuration::get('LYRA_MULTI_PAYMENT_CARDS')),
            'LYRA_MULTI_OPTIONS' => self::getArrayConfig('LYRA_MULTI_OPTIONS'),

            'LYRA_ANCV_TITLE' => self::getLangConfig('LYRA_ANCV_TITLE'),
            'LYRA_ANCV_ENABLED' => Configuration::get('LYRA_ANCV_ENABLED'),
            'LYRA_ANCV_AMOUNTS' => self::getArrayConfig('LYRA_ANCV_AMOUNTS'),
            'LYRA_ANCV_DELAY' => Configuration::get('LYRA_ANCV_DELAY'),
            'LYRA_ANCV_VALIDATION' => Configuration::get('LYRA_ANCV_VALIDATION'),

            'LYRA_ONEY_TITLE' => self::getLangConfig('LYRA_ONEY_TITLE'),
            'LYRA_ONEY_ENABLED' => Configuration::get('LYRA_ONEY_ENABLED'),
            'LYRA_ONEY_AMOUNTS' => self::getArrayConfig('LYRA_ONEY_AMOUNTS'),
            'LYRA_ONEY_DELAY' => Configuration::get('LYRA_ONEY_DELAY'),
            'LYRA_ONEY_VALIDATION' => Configuration::get('LYRA_ONEY_VALIDATION'),
            'LYRA_ONEY_ENABLE_OPTIONS' => Configuration::get('LYRA_ONEY_ENABLE_OPTIONS'),
            'LYRA_ONEY_OPTIONS' => self::getArrayConfig('LYRA_ONEY_OPTIONS'),

            'LYRA_ONEY34_TITLE' => self::getLangConfig('LYRA_ONEY34_TITLE'),
            'LYRA_ONEY34_ENABLED' => Configuration::get('LYRA_ONEY34_ENABLED'),
            'LYRA_ONEY34_AMOUNTS' => self::getArrayConfig('LYRA_ONEY34_AMOUNTS'),
            'LYRA_ONEY34_DELAY' => Configuration::get('LYRA_ONEY34_DELAY'),
            'LYRA_ONEY34_VALIDATION' => Configuration::get('LYRA_ONEY34_VALIDATION'),
            'LYRA_ONEY34_ENABLE_OPTIONS' => Configuration::get('LYRA_ONEY34_ENABLE_OPTIONS'),
            'LYRA_ONEY34_OPTIONS' => self::getArrayConfig('LYRA_ONEY34_OPTIONS'),

            'LYRA_FULLCB_TITLE' => self::getLangConfig('LYRA_FULLCB_TITLE'),
            'LYRA_FULLCB_ENABLED' => Configuration::get('LYRA_FULLCB_ENABLED'),
            'LYRA_FULLCB_AMOUNTS' => self::getArrayConfig('LYRA_FULLCB_AMOUNTS'),
            'LYRA_FULLCB_ENABLE_OPTS' => Configuration::get('LYRA_FULLCB_ENABLE_OPTS'),
            'LYRA_FULLCB_OPTIONS' => self::getArrayConfig('LYRA_FULLCB_OPTIONS'),

            'LYRA_SEPA_TITLE' => self::getLangConfig('LYRA_SEPA_TITLE'),
            'LYRA_SEPA_ENABLED' => Configuration::get('LYRA_SEPA_ENABLED'),
            'LYRA_SEPA_AMOUNTS' => self::getArrayConfig('LYRA_SEPA_AMOUNTS'),
            'LYRA_SEPA_DELAY' => Configuration::get('LYRA_SEPA_DELAY'),
            'LYRA_SEPA_VALIDATION' => Configuration::get('LYRA_SEPA_VALIDATION'),
            'LYRA_SEPA_MANDATE_MODE' => Configuration::get('LYRA_SEPA_MANDATE_MODE'),

            'LYRA_SOFORT_TITLE' => self::getLangConfig('LYRA_SOFORT_TITLE'),
            'LYRA_SOFORT_ENABLED' => Configuration::get('LYRA_SOFORT_ENABLED'),
            'LYRA_SOFORT_AMOUNTS' => self::getArrayConfig('LYRA_SOFORT_AMOUNTS'),

            'LYRA_PAYPAL_TITLE' => self::getLangConfig('LYRA_PAYPAL_TITLE'),
            'LYRA_PAYPAL_ENABLED' => Configuration::get('LYRA_PAYPAL_ENABLED'),
            'LYRA_PAYPAL_AMOUNTS' => self::getArrayConfig('LYRA_PAYPAL_AMOUNTS'),
            'LYRA_PAYPAL_DELAY' => Configuration::get('LYRA_PAYPAL_DELAY'),
            'LYRA_PAYPAL_VALIDATION' => Configuration::get('LYRA_PAYPAL_VALIDATION'),

            'LYRA_CHOOZEO_TITLE' => self::getLangConfig('LYRA_CHOOZEO_TITLE'),
            'LYRA_CHOOZEO_ENABLED' => Configuration::get('LYRA_CHOOZEO_ENABLED'),
            'LYRA_CHOOZEO_AMOUNTS' => self::getArrayConfig('LYRA_CHOOZEO_AMOUNTS'),
            'LYRA_CHOOZEO_DELAY' => Configuration::get('LYRA_CHOOZEO_DELAY'),
            'LYRA_CHOOZEO_OPTIONS' => self::getArrayConfig('LYRA_CHOOZEO_OPTIONS'),

            'LYRA_OTHER_GROUPED_VIEW' => Configuration::get('LYRA_OTHER_GROUPED_VIEW'),
            'LYRA_OTHER_ENABLED' => Configuration::get('LYRA_OTHER_ENABLED'),
            'LYRA_OTHER_TITLE' => self::getLangConfig('LYRA_OTHER_TITLE'),
            'LYRA_OTHER_AMOUNTS' => self::getArrayConfig('LYRA_OTHER_AMOUNTS'),
            'LYRA_OTHER_PAYMENT_MEANS' => self::getArrayConfig('LYRA_OTHER_PAYMENT_MEANS')
        );

        foreach (LyraTools::$submodules as $key => $module) {
            $tpl_vars['LYRA_' . $key . '_COUNTRY'] = Configuration::get('LYRA_' . $key . '_COUNTRY');
            $tpl_vars['LYRA_' . $key . '_COUNTRY_LST'] = ! Configuration::get('LYRA_' . $key . '_COUNTRY_LST') ?
                array() : explode(';', Configuration::get('LYRA_' . $key . '_COUNTRY_LST'));
        }

        if (! LyraTools::$plugin_features['embedded']) {
            unset($tpl_vars['lyra_card_data_mode_options']['5']);
        }

        return $tpl_vars;
    }

    private static function getIpnUrl()
    {
        $shop = new Shop(Configuration::get('PS_SHOP_DEFAULT'));

        // SSL enabled on default shop?
        $id_shop_group = isset($shop->id_shop_group) ? $shop->id_shop_group : $shop->id_group_shop;
        $ssl = Configuration::get('PS_SSL_ENABLED', null, $id_shop_group, $shop->id);

        $ipn = ($ssl ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain)
            . $shop->getBaseURI() . 'modules/lyra/validation.php';

        return $ipn;
    }

    private static function getArrayConfig($name)
    {
        $value = @unserialize(Configuration::get($name));

        if (! is_array($value)) {
            $value = array();
        }

        return $value;
    }

    private static function getLangConfig($name)
    {
        $languages = Language::getLanguages(false);

        $result = array();
        foreach ($languages as $language) {
            $result[$language['id_lang']] = Configuration::get($name, $language['id_lang']);
        }

        return $result;
    }

    private static function getAuthorizedGroups()
    {
        $context = Context::getContext();

        /* @var Lyra */
        $lyra = Module::getInstanceByName('lyra');

        $sql = 'SELECT DISTINCT gl.`id_group`, gl.`name` FROM `' . _DB_PREFIX_ . 'group_lang` AS gl
            INNER JOIN `' . _DB_PREFIX_ . 'module_group` AS mg
            ON (
                gl.`id_group` = mg.`id_group`
                AND mg.`id_module` = ' . (int) $lyra->id . '
                AND mg.`id_shop` = ' . (int) $context->shop->id . '
            )
            WHERE gl.`id_lang` = ' . (int) $context->language->id;

        return Db::getInstance()->executeS($sql);
    }
}
