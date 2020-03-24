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

class AdminStoreCommander extends AdminTab
{

    public function __construct()
    {
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $this->multishop_context = false;
            $this->multishop_context_group = false;
        }
        parent::__construct();
    }

    private function findSCFolder()
    {
        $dir = dirname(__FILE__);
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if (is_dir($dir . '/' . $object) && Tools::strlen(basename($object)) == 11 && basename($object) != "controllers") {
                    return basename($object);
                }
            }
        }
        return false;
    }

    public function display()
    {
        global $cookie, $link, $smarty;

        Tools::addCSS(__PS_BASE_URI__.'/modules/storecommander/views/css/admin.css', 'all');
        $errors = array();
        // Login as selected user on the front office
        // Fix connection for specific 1.5 shops
        //
        if (Tools::getIsset("SETLOGUSER")) {
            $path = '';
            $domains = null;
            if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                $id_shop = (int)(Tools::getValue('id_shop'));
                if ($id_shop == 0 && Shop::getTotalShops() > 1) {
                    $errors[] = Tools::displayError('There is a problem with the shop ID');
                }
                $protocol_link = (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
                $protocol_content = (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
                Context::getContext()->link = new Link($protocol_link, $protocol_content);
                $link = Context::getContext()->link;

                $cookie_lifetime = (int)(Configuration::get('PS_COOKIE_LIFETIME_FO'));
                $cookie_lifetime = time() + (max($cookie_lifetime, 1) * 3600);

                if (Shop::getTotalShops() > 1) {
                    $shop = new Shop($id_shop);
                    $shop_group = $shop->getGroup();
                    if ($shop_group->share_order) {
                        $cookie = new Cookie('ps-sg' . $shop_group->id, $path, $cookie_lifetime,
                            $shop->getUrlsSharedCart());
                    } else {
                        if ($shop->domain != $shop->domain_ssl) {
                            $domains = array($shop->domain_ssl, $shop->domain);
                        }

                        $cookie = new Cookie('ps-s' . $shop->id, $path, $cookie_lifetime, $domains);
                    }
                } else {
                    $cookie = new Cookie('ps-s' . (int)Configuration::get('PS_SHOP_DEFAULT'), $path, $cookie_lifetime,
                        $domains);
                }

            } else {
                $cookie = new Cookie('ps');
            }
            if ($cookie->logged) {
                $cookie->logout();
            }
            Tools::setCookieLanguage();
            Tools::switchLanguage();
            $customer = new Customer((int)(Tools::getValue('id_customer')));
            $cookie->id_customer = (int)($customer->id);
            $cookie->customer_lastname = $customer->lastname;
            $cookie->customer_firstname = $customer->firstname;
            $cookie->logged = 1;
            $cookie->passwd = $customer->passwd;
            $cookie->email = $customer->email;
            $cookie->id_cart = (int)$customer->getLastCart();
            if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                $order_process = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order';
                if (Shop::getTotalShops() > 1) {
                    $server_host = Tools::getHttpHost(false, true);
                    $protocol = 'http://';
                    $protocol_ssl = 'https://';
                    $protocol_link = (Configuration::get('PS_SSL_ENABLED')) ? $protocol_ssl : $protocol;

                    // we replace default domain by selected shop domain
                    $urltmp = $link->getPageLink($order_process, true);
                    $urltmparr = explode('index.php', $urltmp);
                    $urlbase = $protocol_link . $shop->domain . $shop->getBaseURI();
                    $sc_url = $urlbase . 'index.php' . $urltmparr[1];
                } else {
                    $sc_url = $link->getPageLink($order_process,
                        true);  //  http://127.0.0.1/ps15301/index.php?controller=order-opc
                }
            } else {
                $sc_url = __PS_BASE_URI__ . 'order.php';
            }
            $sc_title = "Connecting...";
        }
        else
        {
            $currentFileName = array_reverse(explode("/", $_SERVER['SCRIPT_NAME']));
            $psadminpath = $currentFileName[1];
            $datelastregen = Db::getInstance()->getValue('SELECT last_passwd_gen FROM ' . _DB_PREFIX_ . 'employee WHERE id_employee=' . (int)($cookie->id_employee));
            $scdir = $this->findSCFolder();
            if ($scdir === false) {
                $errors[] = 'Unable to find the Store Commander folder. Please contact <a href="support.storecommander.com" targe="_blank">support.storecommander.com</a>';
            }
            else
            {
                $sc_title = "Loading...";
                $sc_url = '../modules/storecommander/' . $scdir . '/SC/index.php?ide=' . $cookie->id_employee . '&psap=' . $psadminpath . '&key=' . md5($cookie->id_employee . $datelastregen) . (version_compare(_PS_VERSION_,
                        '1.4.0.0', '>=') ? '' : '&id_lang=' . $cookie->id_lang);
            }
        }

        $errors_html = $this->displayScErrors($errors);
        $smarty->assign(array(
            'html' => $errors_html,
            'sc_url' => $sc_url,
            'sc_title' => $sc_title
        ));
        echo $smarty->fetch(dirname(__FILE__).'/views/templates/admin/store_commander/helpers/view/view.tpl');
    }

    public function displayScErrors($errors)
    {
        global $cookie, $link, $smarty;
        if (is_array($errors) && count($errors)) {

            $_html = '';
            $smarty->assign(array(
                'errors' => $errors
            ));
            $_html = $smarty->fetch(dirname(__FILE__).'/views/templates/hook/errors.tpl');
            return $_html;
        }
    }
}