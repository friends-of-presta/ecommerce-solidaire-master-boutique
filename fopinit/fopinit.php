<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Fopinit extends Module
{
    public function __construct()
    {
        $this->name = 'fopinit';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'FOP - StoreCommander';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Fop Intialization');
        $this->description = $this->l('Easy way to launch your Ecommerce Solidaire shop');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';
        if (((bool)Tools::isSubmit('submitFopinitModule')) == true) {
            $output .= $this->postProcess();
        }

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitFopinitModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'html',
                        'name' => '<h3 class="modal-title">' . $this->l('Default FOP values') . '</h3>',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Set default values automatically'),
                        'name' => 'set_default_fop_values',
                        'is_bool' => true,
                        'desc' => $this->l('Update widget title, GDPR text, disable module if needed...'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'html',
                        'name' => '<h3 class="modal-title">' . $this->l('Contact shop & store creation') . '</h3>',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'PS_SHOP_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'PS_SHOP_NAME',
                        'label' => $this->l('Shop name'),
                    ),
                    array(
                        'col' => 3,
                        'row' => 3,
                        'type' => 'textarea',
                        'name' => 'PS_SHOP_DETAILS',
                        'label' => $this->l('Details (SIREN...)'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'PS_SHOP_ADDR1',
                        'label' => $this->l('Address 1'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'PS_SHOP_ADDR2',
                        'label' => $this->l('Address 2'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'PS_SHOP_CODE',
                        'label' => $this->l('Postcode'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'PS_SHOP_CITY',
                        'label' => $this->l('city'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-phone"></i>',
                        'name' => 'PS_SHOP_PHONE',
                        'label' => $this->l('Phone'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'html',
                        'name' => '<h3 class="modal-title">' . $this->l('Page Index') . '</h3>',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'meta_title',
                        'label' => $this->l('Meta title'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'meta_description',
                        'label' => $this->l('Meta description'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'html',
                        'name' => '<h3 class="modal-title">' . $this->l('Social Media') . '</h3>',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-facebook"></i>',
                        'name' => 'BLOCKSOCIAL_FACEBOOK',
                        'label' => 'Facebook',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-twitter"></i>',
                        'name' => 'BLOCKSOCIAL_TWITTER',
                        'label' => 'Twitter',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-youtube"></i>',
                        'name' => 'BLOCKSOCIAL_YOUTUBE',
                        'label' => 'Youtube',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-pinterest"></i>',
                        'name' => 'BLOCKSOCIAL_PINTEREST',
                        'label' => 'Pinterest',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-vimeo-square"></i>',
                        'name' => 'BLOCKSOCIAL_VIMEO',
                        'label' => 'Vimeo',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-instagram"></i>',
                        'name' => 'BLOCKSOCIAL_INSTAGRAM',
                        'label' => 'Instagram',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-linkedin"></i>',
                        'name' => 'BLOCKSOCIAL_LINKEDIN',
                        'label' => 'Linkedin',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'html',
                        'name' => '<h3 class="modal-title">' . $this->l('Upload config') . '</h3>',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-upload"></i>',
                        'name' => 'PS_LIMIT_UPLOAD_IMAGE_VALUE',
                        'label' => $this->l('Max image size in Mo'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $index = Db::getInstance()->getRow('SELECT title, description 
                                                FROM ' . _DB_PREFIX_ . 'meta_lang 
                                                WHERE id_meta = 4');
        return array(
            'set_default_fop_values' => false,
            'meta_title' => $index['title'],
            'meta_description' => $index['description'],
            'PS_SHOP_EMAIL' => Configuration::get('PS_SHOP_EMAIL'),
            'PS_SHOP_NAME' => Configuration::get('PS_SHOP_NAME'),
            'PS_SHOP_DETAILS' => Configuration::get('PS_SHOP_DETAILS'),
            'PS_SHOP_ADDR1' => Configuration::get('PS_SHOP_ADDR1'),
            'PS_SHOP_ADDR2' => Configuration::get('PS_SHOP_ADDR2'),
            'PS_SHOP_CODE' => Configuration::get('PS_SHOP_CODE'),
            'PS_SHOP_CITY' => Configuration::get('PS_SHOP_CITY'),
            'PS_SHOP_PHONE' => Configuration::get('PS_SHOP_PHONE'),
            'BLOCKSOCIAL_FACEBOOK' => Configuration::get('BLOCKSOCIAL_FACEBOOK'),
            'BLOCKSOCIAL_TWITTER' => Configuration::get('BLOCKSOCIAL_TWITTER'),
            'BLOCKSOCIAL_YOUTUBE' => Configuration::get('BLOCKSOCIAL_YOUTUBE'),
            'BLOCKSOCIAL_PINTEREST' => Configuration::get('BLOCKSOCIAL_PINTEREST'),
            'BLOCKSOCIAL_VIMEO' => Configuration::get('BLOCKSOCIAL_VIMEO'),
            'BLOCKSOCIAL_INSTAGRAM' => Configuration::get('BLOCKSOCIAL_INSTAGRAM'),
            'BLOCKSOCIAL_LINKEDIN' => Configuration::get('BLOCKSOCIAL_LINKEDIN'),
            'PS_LIMIT_UPLOAD_IMAGE_VALUE' => Configuration::get('PS_LIMIT_UPLOAD_IMAGE_VALUE'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        $sql = $errors = array();
        $default_values = false;
        foreach (array_keys($form_values) as $key) {
            $value = Tools::getValue($key);
            switch ($key) {
                case 'set_default_fop_values':
                    if ($value) {
                        $default_values = true;
                    }
                    break;
                case 'meta_title':
                    $sql[] = 'UPDATE ' . _DB_PREFIX_ . 'meta_lang SET title="' . pSQL($value) . '" WHERE id_meta = 4';
                    break;
                case 'meta_description':
                    $sql[] = 'UPDATE ' . _DB_PREFIX_ . 'meta_lang SET description="' . pSQL($value) . '" WHERE id_meta = 4';
                    break;
                default:
                    Configuration::updateValue($key, $value);
            }
        }

        if ($default_values && !$this->setDefaultFopValues()) {
            $errors[] = $this->l('Error during setting default fop values');
        }
        if (!empty($sql)) {
            if (!Db::getInstance()->execute(implode(';', $sql))) {
                $errors[] = $this->l('Error during setting index page values');
            }
        }

        if (!empty($errors)) {
            return $this->displayError(implode('<br/>', $errors));
        }
    }

    private function setDefaultFopValues()
    {
        $widget = array(
            1 => 'Produits',
            2 => 'Société',
        );
        $text_rgpd = "J'accepte les conditions générales et la politique de confidentialité";

        $sql = array();
        ## Action
        ## Désactiver les marques
        $sql[] = 'UPDATE ' . _DB_PREFIX_ . 'manufacturer SET active = 0';

        ## Update pays France pour le pays de la boutique
        $sql[] = 'UPDATE ' . _DB_PREFIX_ . 'configuration SET value=8 WHERE name="PS_SHOP_COUNTRY_ID"';

        ## saisie des infos de contact, adresses
        ## MAJ du premier magasin avec les infos de contact général
        $sql[] = 'UPDATE ' . _DB_PREFIX_ . 'store SET id_country=8, city="' . pSQL(Configuration::get('PS_SHOP_CITY')) . '", postcode="' . pSQL(Configuration::get('PS_SHOP_CODE')) . '", phone="' . pSQL(Configuration::get('PS_SHOP_PHONE')) . '", email="' . pSQL(Configuration::get('PS_SHOP_EMAIL')) . '" WHERE id_store=1';
        $sql[] = 'UPDATE ' . _DB_PREFIX_ . 'store_lang SET hours=NULL, name="' . pSQL(Configuration::get('PS_SHOP_NAME')) . '", address1="' . pSQL(Configuration::get('PS_SHOP_ADDR1')) . '", address2="' . pSQL(Configuration::get('PS_SHOP_ADDR2')) . '" WHERE id_store=1 AND id_lang=1';


        ## suppression des autres magasins
        $sql[] = 'DELETE FROM ' . _DB_PREFIX_ . 'store WHERE id_store > 1';
        $sql[] = 'DELETE FROM ' . _DB_PREFIX_ . 'store_lang WHERE id_store NOT IN (SELECT id_store FROM ' . _DB_PREFIX_ . 'store)';
        $sql[] = 'DELETE FROM ' . _DB_PREFIX_ . 'store_shop WHERE id_store NOT IN (SELECT id_store FROM ' . _DB_PREFIX_ . 'store)';

        ## Nettoyage zone géographiques
        $sql[] = 'UPDATE ' . _DB_PREFIX_ . 'zone SET active=0 WHERE id_zone > 1';

        ## Changer la bannière de la home (apparence / Thème et logo / Configuration page d'accueil)
        $dest_filename = md5('friends-of-presta') . '.png';
        if (copy(_PS_MODULE_DIR_ . $this->name . '/views/img/friends-of-presta.png', _PS_MODULE_DIR_ . 'ps_banner/img/' . $dest_filename)) {
            $languages = Language::getLanguages(false);
            $values = array();
            foreach ($languages as $lang) {
                $values['BANNER_IMG'][$lang['id_lang']] = $dest_filename;
                $values['BANNER_LINK'][$lang['id_lang']] = 'https://www.ecommerce-solidaire.fr/';
            }
            Configuration::updateValue('BANNER_IMG', $values['BANNER_IMG']);
            Configuration::updateValue('BANNER_LINK', $values['BANNER_LINK']);
            $this->_clearCache('module:ps_banner/ps_banner.tpl');
        }

        ## Renommer Apparence/link widget + désactiver promos
        foreach ($widget as $id_link_block => $value) {
            $sql[] = 'UPDATE ' . _DB_PREFIX_ . 'link_block_lang SET name="' . pSQL($value) . '" WHERE id_link_block=' . (int)$id_link_block . ' AND id_lang=1';
        }
        $sql[] = 'UPDATE ' . _DB_PREFIX_ . 'link_block SET content=REPLACE(content, \',"0":"prices-drop"\', "") WHERE id_link_block=1';

        ## Désactivation custom text block homepage
        Module::getInstanceByName('ps_customtext')->disable();

        ## Configurer GDPR (module déjà installé)
        $sql[] = 'UPDATE ' . _DB_PREFIX_ . 'psgdpr_consent_lang SET message="' . pSQL($text_rgpd) . '" WHERE id_lang=1';

        ## Rendre obligatoire numero de tel
        $sql[] = 'INSERT INTO ' . _DB_PREFIX_ . 'required_field (`object_name`,`field_name`) VALUES ("CustomesrAddress","phone")';

        return Db::getInstance()->execute(implode(';', $sql));
    }
}
