<?php
/**
 * Copyright © Lyra Network.
 * This file is part of Lyra Collect plugin for PrestaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

/**
 * This controller prepares form and redirects to payment gateway.
 */
class LyraRedirectModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    private $iframe = false;
    private $logger;

    private $accepted_payment_types = array(
        'standard',
        'multi',
        'oney',
        'oney34',
        'fullcb',
        'ancv',
        'sepa',
        'sofort',
        'paypal',
        'choozeo',
        'other',
        'grouped_other'
    );

    public function __construct()
    {
        $this->display_column_left = false;
        $this->display_column_right = version_compare(_PS_VERSION_, '1.6', '<');

        parent::__construct();

        $this->logger = LyraTools::getLogger();
    }

    public function init()
    {
        $this->iframe = (int) Tools::getValue('content_only', 0) == 1;

        parent::init();
    }

    /**
     * Initializes page header variables
     */
    public function initHeader()
    {
        parent::initHeader();

        // To avoid document expired warning.
        session_cache_limiter('private_no_expire');

        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    /**
     * PrestaShop 1.7: override page name in template to use same styles as checkout page.
     * @return array
     */
    public function getTemplateVarPage()
    {
        if (method_exists(get_parent_class($this), 'getTemplateVarPage')) {
            $page = parent::getTemplateVarPage();
            $page['page_name'] = 'checkout';
            return $page;
        }

        return null;
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $cart = $this->context->cart;

        // Page to redirect to if errors.
        $page = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order';

        // Check cart errors.
        if (! Validate::isLoadedObject($cart) || $cart->nbProducts() <= 0) {
            $this->lyraRedirect('index.php?controller=' . $page);
        } elseif (! $cart->id_customer || ! $cart->id_address_delivery || ! $cart->id_address_invoice || ! $this->module->active) {
            if (version_compare(_PS_VERSION_, '1.7', '<') && ! Configuration::get('PS_ORDER_PROCESS_TYPE')) {
                $page .= '&step=1'; // Not one page checkout, goto first checkout step.
            }

            $this->lyraRedirect('index.php?controller=' . $page);
        }

        $type = Tools::getValue('lyra_payment_type', null); // The selected payment submodule.
        if (! $type && $this->iframe) {
            // Only standard payment can be done inside iframe.
            $type = 'standard';
        }

        if (! in_array($type, $this->accepted_payment_types)) {
            $this->logger->logWarning('Error: payment type "' . $type . '" is not supported. Load standard payment by default.');

            // Do not log sensitive data.
            $sensitive_data = array('lyra_card_number', 'lyra_cvv', 'lyra_expiry_month', 'lyra_expiry_year');
            $dataToLog = array();
            foreach ($_REQUEST as $key => $value) {
                if (in_array($key, $sensitive_data)) {
                    $dataToLog[$key] = str_repeat('*', Tools::strlen($value));
                } else {
                    $dataToLog[$key] = $value;
                }
            }

            $this->logger->logWarning('Request data: ' . print_r($dataToLog, true));

            $type = 'standard'; // Force standard payment.
        }

        $payment = null;
        $data = array();

        switch ($type) {
            case 'standard':
                $payment = new LyraStandardPayment();

                if ($payment->getEntryMode() == 2) {
                    $data['card_type'] = Tools::getValue('lyra_card_type');
                } elseif ($payment->getEntryMode() == 4) {
                    $data['iframe_mode'] = true;
                }

                // Payment by alias.
                if (Configuration::get('LYRA_STD_1_CLICK_PAYMENT') === 'True') {
                    $data['payment_by_identifier'] = Tools::getValue('lyra_payment_by_identifier', '0');
                }

                break;

            case 'multi':
                $data['opt'] = Tools::getValue('lyra_opt');
                $data['card_type'] = Tools::getValue('lyra_card_type');

                $payment = new LyraMultiPayment();
                break;

            case 'oney':
                $payment = new LyraOneyPayment();

                $data['opt'] = Tools::getValue('lyra_oney_option');
                break;

            case 'oney34':
                $payment = new LyraOney34Payment();

                $data['opt'] = Tools::getValue('lyra_oney34_option');
                break;

            case 'fullcb':
                $payment = new LyraFullcbPayment();

                $data['card_type'] = Tools::getValue('lyra_card_type');
                break;

            case 'ancv':
                $payment = new LyraAncvPayment();
                break;

            case 'sepa':
                $payment = new LyraSepaPayment();
                break;

            case 'sofort':
                $payment = new LyraSofortPayment();
                break;

            case 'paypal':
                $payment = new LyraPaypalPayment();
                break;

            case 'choozeo':
                $payment = new LyraChoozeoPayment();
                $data['card_type'] = Tools::getValue('lyra_card_type');
                break;

            case 'other':
                $code = Tools::getValue('lyra_payment_code');
                $label = Tools::getValue('lyra_payment_label');

                $payment = new LyraOtherPayment();
                $payment->init($code, $label);

                $data['card_type'] = $code;
                break;

            case 'grouped_other':
                $payment = new LyraGroupedOtherPayment();

                $data['card_type'] = Tools::getValue('lyra_card_type');
                break;
        }

        // Validate payment data.
        $errors = $payment->validate($cart, $data);
        if (! empty($errors)) {
            $this->context->cookie->lyraPayErrors = implode("\n", $errors);

            if (version_compare(_PS_VERSION_, '1.7', '<') && ! Configuration::get('PS_ORDER_PROCESS_TYPE')) {
                $page .= '&step=3';

                if (version_compare(_PS_VERSION_, '1.5.1', '<')) {
                    $page .= '&cgv=1&id_carrier=' . $cart->id_carrier;
                }
            }

            $this->lyraRedirect('index.php?controller=' . $page);
        }

        if (Configuration::get('LYRA_CART_MANAGEMENT') !== LyraTools::KEEP_CART) {
            unset($this->context->cookie->id_cart); // To avoid double call to this page.
        }

        // Prepare data for payment gateway form.
        $request = $payment->prepareRequest($cart, $data);
        $fields = $request->getRequestFieldsArray(false, false /* Data escape will be done in redirect template. */);

        $dataToLog = $request->getRequestFieldsArray(true, false);
        $this->logger->logInfo('Data to be sent to payment gateway: ' . print_r($dataToLog, true));

        $this->context->smarty->assign('lyra_params', $fields);
        $this->context->smarty->assign('lyra_url', $request->get('platform_url'));
        $this->context->smarty->assign('lyra_logo', _MODULE_DIR_ . 'lyra/views/img/' . $payment->getLogo());

        // Parse order_info parameter.
        $parts = explode('&', $request->get('order_info'));

        // Recover payment method title.
        $module_id = Tools::substr($parts[0], Tools::strlen('module_id='));
        $class_name = 'Lyra' . Tools::ucfirst(str_replace('_', '', $module_id)) . 'Payment';
        $class_obj = new $class_name();
        $title = $class_obj->getTitle((int) $cart->id_lang);
        if (isset($parts[1])) {
            $option_id = Tools::substr($parts[1], Tools::strlen('option_id='));
            $multi_options = $class_obj::getAvailableOptions($cart);
            $option = $multi_options[$option_id];
            $title .= ' (' . $option['count'] . ' x)';
        }

        $this->context->smarty->assign('lyra_title', $title);

        if ($this->iframe) {
            $this->setTemplate(LyraTools::getTemplatePath('iframe/redirect.tpl'));
        } else {
            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                $this->setTemplate('module:lyra/views/templates/front/redirect.tpl');
            } else {
                $this->setTemplate('redirect_bc.tpl');
            }
        }
    }

    private function lyraRedirect($url)
    {
        if ($this->iframe) {
            // IFrame mode, use template to redirect to top window.
            $this->context->smarty->assign('lyra_url', LyraTools::getPageLink($url));
            $this->setTemplate(LyraTools::getTemplatePath('iframe/response.tpl'));
        } else {
            Tools::redirect($url);
        }
    }
}
