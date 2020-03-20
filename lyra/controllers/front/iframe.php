<?php
/**
 * Copyright © Lyra Network.
 * This file is part of Lyra Collect plugin for PrestaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

class LyraIframeModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        if (Configuration::get('LYRA_CART_MANAGEMENT') !== LyraTools::KEEP_CART) {
            if ($this->context->cart->id) {
                $this->context->cookie->lyraCartId = (int) $this->context->cart->id;
            }

            if (isset($this->context->cookie->lyraCartId)) {
                $this->context->cookie->id_cart = $this->context->cookie->lyraCartId;
            }
        }

        $this->setTemplate(LyraTools::getTemplatePath('iframe/loader.tpl'));
    }
}
