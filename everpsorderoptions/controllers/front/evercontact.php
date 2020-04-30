<?php
/**
 * Project : everpsorderoptions
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link http://team-ever.com
 */

/**
 * @since 1.5.0
 */
require_once _PS_MODULE_DIR_.'everpsorderoptions/models/EverpsorderoptionsGroup.php';
require_once _PS_MODULE_DIR_.'everpsorderoptions/models/EverpsorderoptionsField.php';
require_once _PS_MODULE_DIR_.'everpsorderoptions/models/EverpsorderoptionsOption.php';

class EverpsorderoptionsEvercontactModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();
        $everforms = array();
        $form = new EverpsorderoptionsGroup(
            (int)Configuration::get('EVERPSCONTACT_FORM'),
            (int)$this->context->shop->id,
            (int)$this->context->language->id
        );
        $everforms[] = array(
            'group' => $form,
            'fields' => EverpsorderoptionsField::getOptionsFieldsByGroup(
                (int)$form->id,
                (int)$this->context->shop->id,
                (int)$this->context->language->id
            ),
            'options' => EverpsorderoptionsOption::getOptionsOptionsByGroup(
                (int)$form->id,
                (int)$this->context->shop->id,
                (int)$this->context->language->id
            ),
        );
        $contact_msg = $this->changeShortcodes(
            (string)Configuration::get(
                'EVERPSCATALOG_MSG',
                (int)$this->context->language->id
            ),
            (int)$this->context->customer->id
        );
        $this->context->smarty->assign(array(
            'everforms' => $everforms,
            'contact_msg' => $contact_msg,
        ));
        if (_PS_VERSION_ >= '1.7') {
            $this->setTemplate(
                'module:everpsorderoptions/views/templates/front/everpsorderoptionspage.tpl'
            );
        } else {
            $this->setTemplate('everpsorderoptionspage16.tpl');
        }
    }

    protected function changeShortcodes($message, $id_customer)
    {
        $link = new Link();
        $contactLink = $link->getPageLink('contact');
        $customer = new Customer((int)$id_customer);
        $gender = new Gender((int)$customer->id_gender, (int)$customer->id_lang);
        $cartLink = $link->getPageLink('cart');
        $shortcodes = array(
            '[customer_lastname]' => $customer->lastname ? $customer->lastname : '',
            '[customer_firstname]' => $customer->firstname ? $customer->firstname : '',
            '[customer_company]' => $customer->company ? $customer->company : '',
            '[customer_siret]' => $customer->siret ? $customer->siret : '',
            '[customer_ape]' => $customer->ape ? $customer->ape : '',
            '[customer_birthday]' => $customer->birthday ? $customer->birthday : '',
            '[customer_website]' => $customer->website ? $customer->website : '',
            '[customer_gender]' => $gender->name ? $gender->name : '',
            '[shop_url]' => Tools::getShopDomainSsl(true),
            '[shop_name]'=> (string)Configuration::get('PS_SHOP_NAME'),
            '[start_cart_link]' => '<a href="'.$cartLink.' target="_blank">',
            '[end_cart_link]' => '</a>',
            '[start_shop_link]' => '<a href="'.Tools::getShopDomainSsl(true).'" target="_blank">',
            '[start_contact_link]' => '<a href="'.$contactLink.'" target="_blank">',
            '[end_shop_link]' => '</a>',
            '[end_contact_link]' => '</a>',
            'NULL' => '', // Useful : remove empty strings in case of NULL
            'null' => '', // Useful : remove empty strings in case of null
        );
        foreach ($shortcodes as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        return $message;
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->l('Options'),
            'url' => $this->context->link->getModuleLink(
                'everpsorderoptions',
                'evercontact'
            ),
        );
        return $breadcrumb;
    }
}
