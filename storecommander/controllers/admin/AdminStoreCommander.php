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


class AdminStoreCommander extends ModuleAdminController
{

    public function __construct()
    {
        $this->display = 'view';
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $this->multishop_context = false;
            $this->multishop_context_group = false;
        }
        parent::__construct();
    }

    private function findSCFolder()
    {
        $dir = dirname(__FILE__) . "/../../";
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

    public function renderView()
    {
        $link = Context::getContext()->link;
        $cookie = Context::getContext()->cookie;

        $this->addCSS(__PS_BASE_URI__.'/modules/storecommander/views/css/admin.css', 'all');
        $errors = array();
        $sc_title = "";
        $sc_url = "";
        //
        // Login as selected user on the front office
        // Fix connection for specific 1.5 shops
        //
        $_html = "";
        if (Tools::getIsset("SETLOGUSER")) {

            $path = '';
            $domains = null;
            if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                $id_shop = (int)(Tools::getValue('id_shop'));
                if ($id_shop == 0 && Shop::getTotalShops() > 1) {
                    $errors[] = Tools::displayError('There is a problem with the shop ID');
                } else {
                    $cookie_lifetime = (int)(defined('_PS_ADMIN_DIR_') ? Configuration::get('PS_COOKIE_LIFETIME_BO') : Configuration::get('PS_COOKIE_LIFETIME_FO'));
                    $cookie_lifetime = time() + (max($cookie_lifetime, 1) * 3600);

                    if (Shop::getTotalShops() > 1) {
                        $shop = new Shop($id_shop);
                        $shop_group = $shop->getGroup();

                        if ($shop_group->share_order) {
                            $cookie = new Cookie('ps-sg' . $shop_group->id, '', $cookie_lifetime,
                                $shop->getUrlsSharedCart());
                        } else {
                            $domains = null;
                            if ($shop->domain != $shop->domain_ssl) {
                                $domains = array($shop->domain_ssl, $shop->domain);
                            }
                            $cookie = new Cookie('ps-s' . $shop->id, '', $cookie_lifetime, $domains);
                        }
                    } else {
                        $shop = new Shop((int)Configuration::get('PS_SHOP_DEFAULT'));
                        if ($shop->domain != $shop->domain_ssl) {
                            $domains = array($shop->domain_ssl, $shop->domain);
                        }
                        $cookie = new Cookie('ps-s' . (int)Configuration::get('PS_SHOP_DEFAULT'), '', $cookie_lifetime,
                            $domains);
                    }
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
            if (Configuration::get('PS_CART_FOLLOWING') AND (empty($cookie->id_cart) OR Cart::getNbProducts($cookie->id_cart) == 0)) {
                $cookie->id_cart = Cart::lastNoneOrderedCart($customer->id);
            }
            if (Tools::getIsset('used_last_cart')) {
                if (version_compare(_PS_VERSION_, '1.5.0.0', '<')) {
                    $cookie->id_cart = $customer->getLastCart();
                }
            }

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
        elseif(version_compare(_PS_VERSION_, '1.7.0.0', '>=') && Tools::getIsset("REDIRECTADMIN")){
            $subaction = Tools::getValue('subaction',"catalog");
            $url_redir = "";
            if($subaction=="addproduct")
            {
                global $kernel; // sf kernel
                if ($kernel instanceof Symfony\Component\HttpKernel\HttpKernelInterface) {
                    $sfRouter = $kernel->getContainer()->get('router');
                }
                $url_redir = $sfRouter->generate('admin_product_new');
            }
            else
            {
                $urlParams = Tools::getValue('urlParams',array());
                $anchor = "";
                if(!empty($urlParams["anchor"]))
                {
                    $anchor = $urlParams["anchor"];
                    unset($urlParams["anchor"]);
                }
                $link = new Link();
                $url_redir = $link->getAdminLink($subaction, true, $urlParams).$anchor;
            }
            if(!empty($url_redir))
            {
                header("location: ".(Configuration::get('PS_SSL_ENABLED') ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_).$url_redir);
            }
        }else {
            $currentFileName = array_reverse(explode("/", $_SERVER['SCRIPT_NAME']));
            $psadminpath = $currentFileName[1];
            $datelastregen = Db::getInstance()->getValue('SELECT last_passwd_gen FROM ' . _DB_PREFIX_ . 'employee WHERE id_employee=' . (int)($cookie->id_employee));
            $scdir = $this->findSCFolder();
            if ($scdir === false) {
                $errors[] = 'Unable to find the Store Commander folder. Please contact <a href="support.storecommander.com" targe="_blank">support.storecommander.com</a>';
            } else {
                $sc_title = "Loading...";
                $sc_url = '../modules/storecommander/' . $scdir . '/SC/index.php?ide=' . $cookie->id_employee . '&psap=' . $psadminpath . '&key=' . md5($cookie->id_employee . $datelastregen) . (version_compare(_PS_VERSION_,'1.4.0.0', '>=') ? '' : '&id_lang=' . $cookie->id_lang);
            }
        }
        $errors_html = $this->displayScErrors($errors);
        $this->tpl_view_vars = array(
            'html' => $errors_html.$_html,
            'sc_url' => $sc_url,
            'sc_title' => $sc_title
        );
        return parent::renderView();
    }

    // useless but needed for compatibility with other modules
    public function displayErrors(){}

    public function displayScErrors($errors)
    {
        if (is_array($errors) && count($errors)) {

            $_html = '';
            $this->context->smarty->assign(array(
                'errors' => $errors
            ));
            $_html = $this->context->smarty->fetch(dirname(__FILE__).'/../../views/templates/hook/errors.tpl');
            return $_html;
        }
    }
}
