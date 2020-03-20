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

class LyraStandardPayment extends AbstractLyraPayment
{
    protected $prefix = 'LYRA_STD_';
    protected $tpl_name = 'payment_std.tpl';
    protected $logo = 'standard.png';
    protected $name = 'standard';

    public function isAvailable($cart)
    {
        if (! parent::isAvailable($cart)) {
            return false;
        }

        if ($this->proposeOney()) {
            return LyraTools::checkOneyRequirements($cart, 'FacilyPay Oney');
        }

        return true;
    }

    protected function proposeOney($data = array())
    {
        if (isset($data['card_type']) && ! in_array($data['card_type'], array('ONEY_SANDBOX', 'ONEY'))) {
            return false;
        }

        if (Configuration::get($this->prefix . 'PROPOSE_ONEY') !== 'True') {
            return false;
        }

        return true;
    }

    public function validate($cart, $data = array())
    {
        $errors = parent::validate($cart, $data);

        if (empty($errors) && $this->proposeOney($data)) {
            $billing_address = new Address((int) $cart->id_address_invoice);

            // Check address validity according to FacilyPay Oney payment specifications.
            $errors = LyraTools::checkAddress($billing_address, 'billing', $this->name);

            if (empty($errors)) {
                // Billing address is valid, check delivery address.
                $delivery_address = new Address((int) $cart->id_address_delivery);

                $errors = LyraTools::checkAddress($delivery_address, 'delivery', $this->name);
            }
        }

        return $errors;
    }

    public function getTplVars($cart)
    {
        $vars = parent::getTplVars($cart);

        $entry_mode = $this->getEntryMode();
        $vars['lyra_std_card_data_mode'] = $entry_mode;

        // Payment by identifier.
        $customers_config = @unserialize(Configuration::get('LYRA_CUSTOMERS_CONFIG'));
        $saved_identifier = isset($customers_config[$cart->id_customer][$this->name]['n']) ? $customers_config[$cart->id_customer][$this->name]['n'] : '';

        $vars['lyra_saved_identifier'] = false;
        $vars['lyra_saved_payment_mean'] = '';

        $vars['lyra_rest_form_token'] = '';
        $vars['lyra_rest_identifier_token'] = '';
        $vars['lyra_rest_popin'] = false;

        if ((Configuration::get('LYRA_STD_1_CLICK_PAYMENT') === 'True') && $saved_identifier) {
            $vars['lyra_saved_identifier'] = true;
            $vars['lyra_saved_payment_mean'] = isset($customers_config[$cart->id_customer][$this->name]['m']) ?
            $customers_config[$cart->id_customer][$this->name]['m'] : '';
        }

        if ($entry_mode === '2' /* Card type on website. */) {
            $vars['lyra_avail_cards'] = $this->getPaymentCards();
        } elseif ($this->getEntryMode() === '4' /* IFrame mode. */) {
            $vars['lyra_can_cancel_iframe'] = (Configuration::get($this->prefix . 'CANCEL_IFRAME') === 'True');
            $this->tpl_name = 'payment_std_iframe.tpl';
        } elseif ($entry_mode === '5' /* REST API. */) {
            $vars['lyra_rest_popin'] = Configuration::get('LYRA_STD_REST_DISPLAY_MODE') === 'popin';

            $form_token = $this->getFormToken($cart);

            if ($form_token) {
                // REST API params.
                $vars['lyra_rest_form_token'] = $form_token;
                $vars['lyra_rest_identifier_token'] = $form_token;

                $customers_config = @unserialize(Configuration::get('LYRA_CUSTOMERS_CONFIG'));
                $saved_identifier = isset($customers_config[$cart->id_customer][$this->name]['n']) ? $customers_config[$cart->id_customer][$this->name]['n'] : '';
                if (Configuration::get('LYRA_STD_1_CLICK_PAYMENT') === 'True' && $saved_identifier) {
                    $identifier_token = $this->getFormToken($cart, true);
                    if ($identifier_token) {
                        $vars['lyra_rest_identifier_token'] = $identifier_token;
                    }
                }

                $this->tpl_name = 'payment_std_rest.tpl';
            } else {
                // Form token not generated by platform, force payment using default mode.
                $vars['lyra_std_card_data_mode'] = '1';
            }
        }

        return $vars;
    }

    private function getPaymentCards()
    {
        // Get selected card types.
        $cards = Configuration::get($this->prefix . 'PAYMENT_CARDS');
        if (! empty($cards)) {
            $cards = explode(';', $cards);
        } else {
            // No card type selected, display all supported cards.
            $cards = array_keys(LyraTools::getSupportedCardTypes());
        }

        if ($this->proposeOney()) {
            $cards[] = (Configuration::get('LYRA_MODE') === 'TEST') ? 'ONEY_SANDBOX' : 'ONEY';
        }

        // Retrieve card labels.
        $avail_cards = array();
        foreach (LyraApi::getSupportedCardTypes() as $code => $label) {
            if (in_array($code, $cards)) {
                $avail_cards[$code] = $label;
            }
        }

        return $avail_cards;
    }

    public function getEntryMode()
    {
        // Get data entry mode.
        return Configuration::get($this->prefix . 'CARD_DATA_MODE');
    }

    private function checkSsl()
    {
        return Configuration::get('PS_SSL_ENABLED') && Tools::usingSecureMode();
    }

    /**
     * {@inheritDoc}
     * @see AbstractLyraPayment::prepareRequest()
     */
    public function prepareRequest($cart, $data = array())
    {
        $request = parent::prepareRequest($cart, $data);

        if (isset($data['iframe_mode']) && $data['iframe_mode']) {
            $request->set('action_mode', 'IFRAME');

            // Hide logos below payment fields.
            $request->set('theme_config', $request->get('theme_config') . '3DS_LOGOS=false;');

            // Enable automatic redirection.
            $request->set('redirect_enabled', '1');
            $request->set('redirect_success_timeout', '0');
            $request->set('redirect_error_timeout', '0');

            $return_url = $request->get('url_return');
            $sep = strpos($return_url, '?') === false ? '?' : '&';
            $request->set('url_return', $return_url . $sep . 'content_only=1');
        }

        if (isset($data['card_type']) && $data['card_type']) {
            // Override payment_cards parameter.
            $request->set('payment_cards', $data['card_type']);

            if ($data['card_type'] === 'BANCONTACT') {
                // May not disable 3DS for Bancontact Mistercash.
                $request->set('threeds_mpi', null);
            }
        } else {
            $cards = Configuration::get($this->prefix . 'PAYMENT_CARDS');
            if (! empty($cards) && $this->proposeOney()) {
                $cards .= ';' . (Configuration::get('LYRA_MODE') === 'TEST' ? 'ONEY_SANDBOX' : 'ONEY');
            }

            $request->set('payment_cards', $cards);
        }

        // Payment by alias.
        if (Configuration::get('LYRA_STD_1_CLICK_PAYMENT') === 'True') {
            $customers_config = @unserialize(Configuration::get('LYRA_CUSTOMERS_CONFIG'));
            $saved_identifier = isset($customers_config[$cart->id_customer][$this->name]['n']) ? $customers_config[$cart->id_customer][$this->name]['n'] : '';
            $use_identifier = isset($data['payment_by_identifier']) ? $data['payment_by_identifier'] === '1' : false;

            if ($saved_identifier) {
                // Customer has an identifier.
                $request->set('identifier', $saved_identifier);

                if (! $use_identifier) {
                    // Customer choose to not use alias.
                    $request->set('page_action', 'REGISTER_UPDATE_PAY');
                }
            } else {
                // Card data entry on payment page, let's ask customer for data registration.
                LyraTools::getLogger()->logInfo('Customer ' . $request->get('cust_email') . ' will be asked for card data registration on payment page.');
                $request->set('page_action', 'ASK_REGISTER_PAY');
            }
        }

        return $request;
    }

    public function getFormToken($cart, $use_identifier = false)
    {
        $request = $this->prepareRequest($cart, array());

        $strong_auth = $this->getEscapedVar($request, 'threeds_mpi') === '2' ? 'DISABLED' : 'AUTO';
        $currency = LyraApi::findCurrencyByNumCode($this->getEscapedVar($request, 'currency'));
        $cart_id = $this->getEscapedVar($request, 'order_id');
        $cust_mail = $this->getEscapedVar($request, 'cust_email');

        $params = array(
            'orderId' => $cart_id,
            'customer' => array(
                'email' => $cust_mail,
                'reference' => $this->getEscapedVar($request, 'cust_id'),
                'billingDetails' => array(
                    'language' => $this->getEscapedVar($request, 'language'),
                    'title' => $this->getEscapedVar($request, 'cust_title'),
                    'firstName' => $this->getEscapedVar($request, 'cust_first_name'),
                    'lastName' => $this->getEscapedVar($request, 'cust_last_name'),
                    'category' => $this->getEscapedVar($request, 'cust_status'),
                    'address' => $this->getEscapedVar($request, 'cust_address'),
                    'zipCode' => $this->getEscapedVar($request, 'cust_zip'),
                    'city' => $this->getEscapedVar($request, 'cust_city'),
                    'state' => $this->getEscapedVar($request, 'cust_state'),
                    'phoneNumber' => $this->getEscapedVar($request, 'cust_phone'),
                    'country' => $this->getEscapedVar($request, 'cust_country')
                ),
                'shippingDetails' => array(
                    'firstName' => $this->getEscapedVar($request, 'ship_to_first_name'),
                    'lastName' => $this->getEscapedVar($request, 'ship_to_last_name'),
                    'category' => $this->getEscapedVar($request, 'ship_to_status'),
                    'address' => $this->getEscapedVar($request, 'ship_to_street'),
                    'address2' => $this->getEscapedVar($request, 'ship_to_street2'),
                    'zipCode' => $this->getEscapedVar($request, 'ship_to_zip'),
                    'city' => $this->getEscapedVar($request, 'ship_to_city'),
                    'state' => $this->getEscapedVar($request, 'ship_to_state'),
                    'phoneNumber' => $this->getEscapedVar($request, 'ship_to_phone_num'),
                    'country' => $this->getEscapedVar($request, 'ship_to_country'),
                    'deliveryCompanyName' => $this->getEscapedVar($request, 'ship_to_delivery_company_name'),
                    'shippingMethod' => $this->getEscapedVar($request, 'ship_to_type'),
                    'shippingSpeed' => $this->getEscapedVar($request, 'ship_to_speed')
                )
            ),
            'transactionOptions' => array(
                'cardOptions' => array(
                    'captureDelay' => $this->getEscapedVar($request, 'capture_delay'),
                    'manualValidation' => ($this->getEscapedVar($request, 'validation_mode') === '1') ? 'YES' : 'NO',
                    'paymentSource' => 'EC'
                )
            ),
            'contrib' => $this->getEscapedVar($request, 'contrib'),
            'strongAuthenticationState' => $strong_auth,
            'currency' => $currency->getAlpha3(),
            'amount' => $this->getEscapedVar($request, 'amount'),
            'metadata' => array(
                'orderInfo' => 'module_id=' . $this->name
            )
        );

        // Set Number of attempts in case of rejected payment.
        if (Configuration::get($this->prefix . 'REST_ATTEMPTS')) {
            $params['transactionOptions']['cardOptions']['retry'] = Configuration::get($this->prefix . 'REST_ATTEMPTS');
        }

        if ($use_identifier) {
            LyraTools::getLogger()->logInfo("Customer {$cust_mail} has an identifier. Use it for payment of cart #{$cart_id}.");

            $customers_config = @unserialize(Configuration::get('LYRA_CUSTOMERS_CONFIG'));
            $saved_identifier = isset($customers_config[$cart->id_customer][$this->name]['n']) ? $customers_config[$cart->id_customer][$this->name]['n'] : '';
            $params['paymentMethodToken'] = $saved_identifier;
        } elseif (Configuration::get('LYRA_STD_1_CLICK_PAYMENT') === 'True' && $this->getEscapedVar($request, 'cust_id')) {
            // 1-Click enabled and customer logged-in, let's ask customer for card data registration.
            LyraTools::getLogger()->logInfo("Customer {$cust_mail} will be asked for card data registration on payment page for order #{$cart_id}.");
            $params['formAction'] = 'ASK_REGISTER_PAY';
        }

        $test_mode = Configuration::get('LYRA_MODE') === 'TEST';
        $key = $test_mode ? Configuration::get('LYRA_PRIVKEY_TEST') : Configuration::get('LYRA_PRIVKEY_PROD');
        $site_id = Configuration::get('LYRA_SITE_ID');

        require_once _PS_MODULE_DIR_ . 'lyra/classes/LyraRest.php';

        $return = false;
        try {
            $client = new LyraRest(LyraTools::getDefault('REST_URL'), $site_id, $key);
            $result = $client->post('V4/Charge/CreatePayment', json_encode($params));

            if ($result['status'] !== 'SUCCESS') {
                LyraTools::getLogger()->logError("Error while creating payment form token for cart #$cart_id: " . $result['answer']['errorMessage']
                    . ' (' . $result['answer']['errorCode'] . ').');

                if (isset($result['answer']['detailedErrorMessage']) && ! empty($result['answer']['detailedErrorMessage'])) {
                    LyraTools::getLogger()->logError('Detailed message: ' . $result['answer']['detailedErrorMessage']
                        . ' (' . $result['answer']['detailedErrorCode'] . ').');
                }
            } else {
                // Payment form token created successfully.
                LyraTools::getLogger()->logInfo("Form token created successfully for cart #$cart_id.");
                $return = $result['answer']['formToken'];
            }
        } catch (Exception $e) {
            LyraTools::getLogger()->logError($e->getMessage());
        }

        return $return;
    }

    private function getEscapedVar($request, $var)
    {
        $value = $request->get($var);

        if (empty($value)) {
            return null;
        }

        return $value;
    }

    public function hasForm()
    {
        if ($this->getEntryMode() === '1') {
            return false;
        }

        return true;
    }

    protected function getDefaultTitle()
    {
        return $this->l('Payment by credit card');
    }
}