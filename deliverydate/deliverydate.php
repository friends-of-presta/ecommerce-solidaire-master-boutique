<?php

class DeliveryDate extends Module
{
    protected $_warnings = array();

    public function __construct()
    {
        $this->name = 'deliverydate';
        $this->tab = 'shipping_logistics';
        $this->version = '1.6.4';
        $this->module_key = '512f4d91276fa09d3117008695cb3a3c';
        $this->author = 'MARICHAL Emmanuel';

        parent::__construct();

        $this->displayName = $this->l('Delivery dates');
        $this->description = $this->l('Display delivery dates in order process and product page');

        $this->bootstrap = true;
    }

    public function install()
    {
        Configuration::updateValue('DELIVERYDATE_PREP_TIME', 1);

        $success = Db::getInstance()->Execute('
                CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'delivery_date` (
                    `id` int(10) NOT NULL AUTO_INCREMENT,
                    `id_carrier` int(10),
                    `id_zone` int(10),
                    `days` varchar(14) DEFAULT NULL,
                    `min` int(10) DEFAULT 0,
                    `max` int(10) DEFAULT 0,
                    `hours` int(10) DEFAULT 0,
                    `minutes` int(10) DEFAULT 0,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `DeliveryDateCarrierIndex` (`id_carrier`, `id_zone`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;')
            && Db::getInstance()->Execute('
                CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'delivery_date_history` (
                    `id` int(10) NOT NULL AUTO_INCREMENT,
                    `id_order` int(10),
                    `date_min` date NOT NULL,
                    `date_max` date NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `DeliveryDateOrderIndex` (`id_order`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;')
            && $this->createExceptionsTable()
            && parent::install()
            && $this->registerHook('displayBeforeCarrier')
            && $this->registerHook('updateCarrier')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('displayAdminOrder')
            && $this->registerHook('displayOrderDetail')
            && $this->registerHook('header')
            && $this->registerHook('displayPDFInvoice')
            && $this->registerHook('actionAdminSuppliersFormModifier')
            && $this->registerHook('actionObjectSupplierAddAfter')
            && $this->registerHook('actionObjectSupplierUpdateAfter')
            && $this->registerHook('productTab') // 1.6-1.5
            && $this->registerHook('productTabContent') // 1.6-1.5
            && $this->registerHook('displayProductExtraContent') // 1.7
            && $this->registerHook('displayProductButtons');

        if (version_compare(_PS_VERSION_, '1.6', '>')) {
            return $success && $this->registerHook('displayAdminOrderTabShip') && $this->registerHook('displayAdminOrderContentShip');
        } else {
            return $success && $this->registerHook('displayAdminOrder');
        }
    }

    private function createExceptionsTable()
    {
        return Db::getInstance()->Execute('
                CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'delivery_date_exceptions` (
                    `id` int(10) NOT NULL AUTO_INCREMENT,
                    `id_carrier` int(10),
                    `id_zone` int(10),
                    `date` date NOT NULL,
                    `delivery` int(1) DEFAULT 0,
                    `preparation` int(1) DEFAULT 0,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `DeliveryDateException` (`id_carrier`, `id_zone`, `date`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;');
    }

    public function hookHeader($params)
    {
        if (Context::getContext()->controller instanceof OrderController
            || Context::getContext()->controller instanceof OrderOPCController) {
            $this->context->controller->addJS($this->_path.'views/js/before_carrier.js');
            if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                $this->context->controller->addCSS($this->_path.'views/css/before_carrier.css');
            } else {
                $this->context->controller->addCSS($this->_path.'views/css/before_carrier_17.css');
            }
        }
    }

    protected static function getSuppliersDelays()
    {
        $suppliers = Configuration::get('DELIVERYDATE_SUPPLIERS_DELAY');
        $suppliers = $suppliers ? json_decode($suppliers, true) : array();
        return $suppliers;
    }

    public function hookActionAdminSuppliersFormModifier($params)
    {
        $params['fields'][0]['form']['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Additional delay (out of stock)'),
            'desc' => $this->l('Additional number of days when a product from this supplier is out of stock. This value is used by the delivery dates module and will be added to the delivery date instead of the default out of stock value set in the module.'),
            'name' => 'outofstock_delay',
            'required' => false,
            'col' => 3,
        );
        $suppliers = self::getSuppliersDelays();
        $id_supplier = (int)Tools::getValue('id_supplier');

        $params['fields_value']['outofstock_delay'] = isset($suppliers[$id_supplier]) ? (int)$suppliers[$id_supplier] : 0;
    }

    public function hookActionObjectSupplierAddAfter($params)
    {
        $suppliers = self::getSuppliersDelays();
        $suppliers[$params['object']->id] = (int)Tools::getValue('outofstock_delay');
        Configuration::updateValue('DELIVERYDATE_SUPPLIERS_DELAY', json_encode($suppliers));
    }

    public function hookActionObjectSupplierUpdateAfter($params)
    {
        $this->hookActionObjectSupplierAddAfter($params);
    }

    public function hookDisplayProductButtons($params)
    {
        if (is_array($params['product']) && $params['product']['is_virtual']) {
            return '';
        } elseif (!is_array($params['product']) && $params['product']->is_virtual) {
            return '';
        }

        $carriers = $this->getProductPageCarriers();

        if (empty($carriers)) {
            return '';
        }

        $best_available = 0;
        $best_oot = 0;

        // Search for the best date
        foreach ($carriers as $carrier) {
            if (!$best_available || $carrier['min'] < $best_available) {
                $best_available = $carrier['min'];
            }
            if ((isset($carrier['oot_min']) && $carrier['oot_min']) && (!$best_oot || $carrier['oot_min'] < $best_oot)) {
                $best_oot = $carrier['oot_min'];
            }
        }

        if (!$best_available) {
            return '';
        }

        $this->context->smarty->assign('available_text', str_replace(array('[date_min]'), array(date($this->context->language->date_format_lite, $best_available)), $this->l('Delivery expected from [date_min]')));

        if ($best_oot) {
            $this->context->smarty->assign('oot_text', str_replace(array('[date_min]'), array(date($this->context->language->date_format_lite, $best_oot)), $this->l('Delivery expected from [date_min]')));
        }

        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            return $this->display(__FILE__, 'views/templates/hook/add_to_cart_block.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/add_to_cart_block_17.tpl');
        }
    }

    public function hookProductTab($params)
    {
        if (is_array($params['product']) && (int)$params['product']['is_virtual']) {
            return '';
        } elseif (!is_array($params['product']) && (int)$params['product']->is_virtual) {
            return '';
        }

        $carriers = $this->getProductPageCarriers();

        if (!empty($carriers) && Configuration::get('DELIVERYDATE_TABS')) {
            return $this->display(__FILE__, 'views/templates/hook/product_tab.tpl');
        } else {
            return '';
        }
    }

    public function hookProductTabContent($params)
    {
        $this->context->controller->addJS($this->_path.'views/js/product.js');
        $this->context->smarty->assign('tab', Configuration::get('DELIVERYDATE_TABS'));
        return $this->getProductTabContent($params['product']);
    }

    private function getProductTabContent($product, $only_content = false)
    {
        if ($product->is_virtual) {
            return '';
        }

        $carriers = $this->getProductPageCarriers();

        if (empty($carriers)) {
            return '';
        }

        $qties = [];
        foreach($product->getAttributeCombinations() as $combination) {
            $qties[$combination['id_product_attribute']] = $combination['quantity'];
        }

        if (empty($qties)) {
            $qties['0'] = $product->quantity;
        }

        $config = Configuration::getMultiple(array(
            'PS_STOCK_MANAGEMENT',
            'PS_ORDER_OUT_OF_STOCK',
        ));

        $this->context->smarty->assign(array(
            'carriers' => $carriers,
            'only_content' => $only_content,
            'qties' => $qties,
            'allow_oosp' => !$config['PS_STOCK_MANAGEMENT'] || $config['PS_ORDER_OUT_OF_STOCK'],
            'id_product_attribute' => Tools::getValue('id_product_attribute', Product::getDefaultAttribute($product->id)),
        ));

        return $this->display(__FILE__, 'views/templates/hook/product_tab_content.tpl');
    }

    public function hookDisplayProductExtraContent($params)
    {
        $tabs = array();
        $content = $this->getProductTabContent($params['product'], true);

        if (empty($content)) {
            return $tabs;
        }

        $pec = new PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
        $pec->addAttr(array('class' => 'deliverydate-content'));
        $tabs[] = $pec->setTitle($this->l('Delivery'))->setContent($content);

        $this->context->controller->addJS($this->_path.'views/js/product17.js');
        $this->context->controller->addCSS($this->_path.'views/css/product.css');

        return $tabs;
    }

    public function getFormatedDeliveryText($min, $max, $date_format_lite)
    {
        if ($min == $max) {
            $text = str_replace(array('[date_min]'), array(date($date_format_lite, $min)), $this->l('Delivery scheduled the [date_min]'));
        } else {
            $text = str_replace(array('[date_min]', '[date_max]'), array('<b>'.date($date_format_lite, $min).'</b>', '<b>'.date($date_format_lite, $max).'</b>'), $this->l('Delivery scheduled between the [date_min] and [date_max]'));
        }
        return $text;
    }

    private function getProductPageCarriers()
    {
        static $carriers = null;

        if ($carriers) {
            return $carriers;
        }

        $carriers = array();

        $config = Configuration::getMultiple(array(
            'DELIVERYDATE_PREP_DAYS',
            'DELIVERYDATE_PREP_TIME',
            'DELIVERYDATE_OUT_OF_STOCK',
            'DELIVERYDATE_PRODUCT',
            'PS_STOCK_MANAGEMENT',
            'PS_ORDER_OUT_OF_STOCK',
            'DELIVERYDATE_DATE_MAX'
        ));

        if (!isset($config['DELIVERYDATE_PREP_DAYS']) || !isset($config['DELIVERYDATE_PRODUCT']) || !$config['DELIVERYDATE_PRODUCT']) {
            return $carriers;
        }

        // Get preparation days and exit if at least one day is not selected
        $prep_days = explode(',', $config['DELIVERYDATE_PREP_DAYS']);
        if (array_search(1, $prep_days) === false) {
            return $carriers;
        }

        $id_address = $this->context->cart->id_address_delivery;
        if ($id_address) {
            $address = new Address($id_address);

            if (!Validate::isLoadedObject($address)) {
                return false;
            }

            if (!Address::isCountryActiveById($address->id)) {
                return false;
            }

            $id_zone = Address::getZoneById($address->id);
        } else {
            $country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
            $id_zone = $country->id_zone;
        }

        $product = new Product(Tools::getValue('id_product'));

        if (!Validate::isLoadedObject($product)) {
            return $carriers;
        }

        $product->loadStockData();
        $available_carriers = Carrier::getAvailableCarrierList($product, 0);

        if (!$available_carriers || !is_array($available_carriers) || empty($available_carriers)) {
            return $carriers;
        }

        // We also need to filter carriers that are too big or too heavy because getAvailableCarrierList checks are based
        // on the current cart, but the product is not necessarily in the cart yet
        $conditions = Db::getInstance()->ExecuteS('
            SELECT dd.*, c.*, MAX(rw.delimiter2) as range_max
            FROM '._DB_PREFIX_.'delivery_date dd
            LEFT JOIN '._DB_PREFIX_.'carrier c ON c.id_carrier = dd.id_carrier
            LEFT JOIN '._DB_PREFIX_.'range_weight rw ON rw.id_carrier = dd.id_carrier
            WHERE dd.id_zone IN (0, '.(int)$id_zone.')
                AND (c.max_weight = 0 OR c.max_weight > '.(float)$product->weight.')
                AND (c.max_width = 0 OR c.max_width > '.(float)$product->width.')
                AND (c.max_height = 0 OR c.max_height > '.(float)$product->height.')
                AND (c.max_depth = 0 OR c.max_depth > '.(float)$product->depth.')
                AND dd.id_carrier IN ('.implode(',', array_map('intval', array_values($available_carriers))).')
            GROUP BY dd.id
            HAVING c.shipping_method = 0
                OR c.is_free
                OR c.range_behavior = 0
                OR (c.range_behavior = 1 AND range_max > '.(float)$product->weight.')
        ');

        if (!$conditions || empty($conditions)) {
            return $carriers;
        }

        foreach ($conditions as $condition) {
            // Make sure that the condition with a zone id has priority
            if (isset($carriers[$condition['id_carrier']]) && !$condition['id_zone']) {
                continue;
            }
            $exceptions = self::getDeliveryExceptions($id_zone, $condition['id_carrier']);
            $dates = self::getDeliveryDelay($condition, $exceptions, $config, $prep_days, false);
            $oot_dates = $config['PS_STOCK_MANAGEMENT'] && $config['PS_ORDER_OUT_OF_STOCK'] ? self::getDeliveryDelay($condition, $exceptions, $config, $prep_days, array($product->id_supplier)) : false;

            $carriers[$condition['id_carrier']] = array();
            $carriers[$condition['id_carrier']]['logo'] = _THEME_SHIP_DIR_.$condition['id_carrier'].'.jpg';
            $carriers[$condition['id_carrier']]['name'] = $condition['name'];
            $carriers[$condition['id_carrier']]['date'] = $this->getFormatedDeliveryText($dates['start'], $dates['end'], $this->context->language->date_format_lite);
            $carriers[$condition['id_carrier']]['min'] = $dates['start'];
            if ($oot_dates) {
                $carriers[$condition['id_carrier']]['oot_date'] = $this->getFormatedDeliveryText($oot_dates['start'], $oot_dates['end'], $this->context->language->date_format_lite);
                $carriers[$condition['id_carrier']]['oot_min'] = $oot_dates['start'];
            }
        }
        return $carriers;
    }

    public function hookDisplayOrderDetail($params)
    {
        $id_order = $params['order']->id;
        $dates = Db::getInstance()->getRow('SELECT date_min AS min, date_max AS max FROM '._DB_PREFIX_.'delivery_date_history WHERE id_order = '.(int)$id_order);

        if (!$dates) {
            return '';
        }

        $dates['min'] = date($this->context->language->date_format_lite, strtotime($dates['min']));
        $dates['max'] = date($this->context->language->date_format_lite, strtotime($dates['max']));
        if ($dates['min'] == $dates['max']) {
            $text = str_replace(array('[date_min]'), array($dates['min']), $this->l('Delivery scheduled the [date_min]'));
        } else {
            $text = str_replace(array('[date_min]', '[date_max]'), array($dates['min'], $dates['max']), $this->l('Delivery scheduled between the [date_min] and [date_max]'));
        }

        $this->context->smarty->assign('text', $text);

        return $this->display(__FILE__, 'views/templates/hook/order_detail.tpl');
    }

    public function hookPDFInvoice($params)
    {
        return $this->hookDisplayPDFInvoice($params);
    }

    public function hookDisplayPDFInvoice($params)
    {
        $id_order = (_PS_VERSION_ > '1.5' ? $params['object']->id_order : $params['id_order']);
        $dates = Db::getInstance()->getRow('SELECT date_min AS min, date_max AS max FROM '._DB_PREFIX_.'delivery_date_history WHERE id_order = '.(int)$id_order);

        if (!$dates) {
            return '';
        }

        $dates['min'] = date($this->context->language->date_format_lite, strtotime($dates['min']));
        $dates['max'] = date($this->context->language->date_format_lite, strtotime($dates['max']));
        $text = str_replace(array('[date_min]', '[date_max]'), array($dates['min'], $dates['max']), $this->l('Delivery scheduled between the [date_min] and [date_max]'));

        if (_PS_VERSION_ > '1.5') {
            return $text;
        } else {
            $pdf = $params['pdf'];
            $pdf->Ln(10);
            $pdf->Cell(0, 0, utf8_decode($text));
        }
    }

    public function hookUpdateCarrier($params)
    {
        Db::getInstance()->Execute('
            UPDATE '._DB_PREFIX_.'delivery_date
            SET id_carrier = '.(int)$params['carrier']->id.'
            WHERE id_carrier = '.(int)$params['id_carrier']);
    }

    public function hookNewOrder($params)
    {
        return $this->hookActionValidateOrder($params);
    }

    public function hookActionValidateOrder($params)
    {
        $condition = Db::getInstance()->getRow(
           'SELECT * FROM '._DB_PREFIX_.'delivery_date
            WHERE id_carrier = '.(int)$params['cart']->id_carrier.'
                AND id_zone IN (0, '.(int)Address::getZoneById($params['cart']->id_address_delivery).')
            ORDER BY id_zone DESC'
        );

        $config = Configuration::getMultiple(array(
            'DELIVERYDATE_PREP_DAYS',
            'DELIVERYDATE_PREP_TIME',
            'DELIVERYDATE_OUT_OF_STOCK',
            'PS_STOCK_MANAGEMENT',
            'DELIVERYDATE_DATE_MAX'
        ));

        if (!$condition || !isset($config['DELIVERYDATE_PREP_DAYS'])) {
            return;
        }

        // Get preparation days and exit if at least one day is not selected
        $prep_days = explode(',', $config['DELIVERYDATE_PREP_DAYS']);
        if (array_search(1, $prep_days) === false) {
            return;
        }

        $oos_products_suppliers = $config['PS_STOCK_MANAGEMENT'] ? $this->getOutOfStockProductsSuppliersInCart() : false;

        $id_zone = Address::getZoneById($params['cart']->id_address_delivery);
        $exceptions = self::getDeliveryExceptions($id_zone, $params['cart']->id_carrier);
        $delay = self::getDeliveryDelay($condition, $exceptions, $config, $prep_days, $oos_products_suppliers);

        Db::getInstance()->execute('
            INSERT INTO '._DB_PREFIX_.'delivery_date_history (id_order, date_min, date_max)
            VALUES('.(int)$params['order']->id.', "'.date('Y-m-d', $delay['start']).'", "'.date('Y-m-d', $delay['end']).'")');
    }

    public function hookAdminOrder($params)
    {
        return $this->hookDisplayAdminOrder($params);
    }

    private function getOrderDeliveryDates($id_order)
    {
        static $dates = null;

        if ($dates !== null) {
            return $dates;
        }

        $dates = Db::getInstance()->getRow(
           'SELECT UNIX_TIMESTAMP(date_min) AS date_min, UNIX_TIMESTAMP(date_max) AS date_max
            FROM '._DB_PREFIX_.'delivery_date_history
            WHERE id_order = '.(int)$id_order
        );

        if ($dates) {
            $dates = array(
                'date_min' => date($this->context->language->date_format_lite, $dates['date_min']),
                'date_max' => date($this->context->language->date_format_lite, $dates['date_max'])
            );
        }

        return $dates;
    }

    public function hookDisplayAdminOrderTabShip($params)
    {
        $dates = $this->getOrderDeliveryDates($params['order']->id);

        if (!$dates) {
            return '';
        } else {
            return $this->display(__FILE__, 'admin_order_tab_ship.tpl');
        }
    }

    public function hookDisplayAdminOrderContentShip($params)
    {
        $dates = $this->getOrderDeliveryDates($params['order']->id);

        if (!$dates) {
            return '';
        } else {
            $this->context->smarty->assign($dates);
            return $this->display(__FILE__, 'admin_order_content_ship.tpl');
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            return;
        }

        $dates = $this->getOrderDeliveryDates($params['id_order']);

        if (!$dates) {
            return '';
        } else {
            $this->context->smarty->assign($dates);
            return $this->display(__FILE__, 'admin_order.tpl');
        }
    }

    protected static function getOutOfStockDelay($oos_products_suppliers, $default_delay = 0)
    {
        $suppliers = self::getSuppliersDelays();
        $oos_delay = false;
        if (is_array($oos_products_suppliers)) {
            // Array is reversed to make sure that if a product is out of stock without value set for it's supplier,
            // the default value will be used.
            rsort($oos_products_suppliers);
            foreach ($oos_products_suppliers as $id_supplier) {
                if (!$id_supplier && ($oos_delay === false || $oos_delay < (int)$default_delay)) {
                    $oos_delay = (int)$default_delay;
                } elseif ($id_supplier && count($suppliers) && $suppliers[$id_supplier] > 0
                            && ($oos_delay > (int)$suppliers[$id_supplier] || $oos_delay === false)) {
                    $oos_delay = (int)$suppliers[$id_supplier];
                }
            }
        }
        return (int)$oos_delay;
    }

    protected static function getDeliveryDelay($condition, $exceptions, $config, $prep_days, $oos_products_suppliers)
    {
        $days = 0;
        $delay = array();

        // Making sure that max is > than min
        if ($condition['min'] > $condition['max']) {
            $real_max = $condition['min'];
            $condition['min'] = $condition['max'];
            $condition['max'] = $real_max;
        }

        // Check if today is count as an expedition day
        if (strtotime($condition['hours'].':'.$condition['minutes']) < strtotime('now')) {
            $days += 1;
        }

        // Additionnal time for out of stock
        if ($oos_products_suppliers && !$config['DELIVERYDATE_DATE_MAX']) {
            $days += self::getOutOfStockDelay($oos_products_suppliers, $config['DELIVERYDATE_OUT_OF_STOCK']);
        }

        // Add preparation time
        $time = (int)$config['DELIVERYDATE_PREP_TIME'];
        while ($time) {
            $index = date('w', strtotime('+'.$days.' day'));
            $date = date('Y-m-d', strtotime('+'.$days.' day'));
            if ($prep_days[$index ? $index - 1 : 6] == 1 && !in_array($date, $exceptions['preparation'])) {
                $time -= 1;
            }
            $days += 1;
        }

        // Get delivery days
        $delivery = explode(',', $condition['days']);

        // Add days until min delay is reached (-1 because first day is count as a delivery day)
        $min = (int)$condition['min'] - 1;
        $index = date('w', strtotime('+'.$days.' day'));
        while (in_array(date('Y-m-d', strtotime('+'.$days.' day')), $exceptions['delivery']) || $delivery[$index ? $index - 1 : 6] == 0) {
            $days += 1;
            $index = date('w', strtotime('+'.$days.' day'));
        }

        while ($min > 0) {
            $index = date('w', strtotime('+'.$days.' day'));
            $date = date('Y-m-d', strtotime('+'.$days.' day'));

            if ($delivery[$index ? $index - 1 : 6] == 1 && !in_array($date, $exceptions['delivery'])) {
                $min -= 1;
            }
            $days += 1;

            // Making sure that we don't stop on a non-delivery day
            if ($min == 0) {
                $index = date('w', strtotime('+'.$days.' day'));
                while ($delivery[$index ? $index - 1 : 6] == 0 || in_array(date('Y-m-d', strtotime('+'.$days.' day')), $exceptions['delivery'])) {
                    $days += 1;
                    $index = date('w', strtotime('+'.$days.' day'));
                }
            }
        }

        // Save the date
        $delay['start'] = strtotime('+'.$days.' day');

        // Additionnal time for out of stock
        if ($oos_products_suppliers && $config['DELIVERYDATE_DATE_MAX']) {
            $days += self::getOutOfStockDelay($oos_products_suppliers, $config['DELIVERYDATE_OUT_OF_STOCK']);
        }

        // Add days until max delay is reached
        $max = (int)$condition['max'] - (int)$condition['min'];
        while ($max) {
            $index = date('w', strtotime('+'.$days.' day'));
            $date = date('Y-m-d', strtotime('+'.$days.' day'));

            if ($delivery[$index ? $index - 1 : 6] == 1 && !in_array($date, $exceptions['delivery'])) {
                $max--;
            }
            $days += 1;

            // Making sure that we don't stop on a non-delivery day
            if ($max == 0) {
                $index = date('w', strtotime('+'.$days.' day'));
                while ($delivery[$index ? $index - 1 : 6] == 0 || in_array(date('Y-m-d', strtotime('+'.$days.' day')), $exceptions['delivery'])) {
                    $days += 1;
                    $index = date('w', strtotime('+'.$days.' day'));
                }
            }
        }

        // Save the date
        $delay['end'] = strtotime('+'.$days.' day');

        return $delay;
    }

    private function getOutOfStockProductsSuppliersInCart()
    {
        $suppliers = array();
        foreach ($this->context->cart->getProducts() as $product) {
            if ($product['stock_quantity'] < $product['cart_quantity']) {
                $product_suppliers = ProductSupplier::getSupplierCollection($product['id_product'])->getResults();
                foreach ($product_suppliers as $supplier) {
                    $suppliers[] = $supplier->id_supplier;
                }
                if (empty($product_suppliers)) {
                    $suppliers[] = 0;
                }
            }
        }

        $suppliers = array_unique($suppliers);

        return count($suppliers) ? $suppliers : false;
    }

    public function hookBeforeCarrier($params)
    {
        return $this->hookDisplayBeforeCarrier($params);
    }

    public function getDeliveryExceptions($id_zone, $id_carrier)
    {
        static $exceptions = array();

        if (!isset($exceptions[$id_zone])) {
            $dbquery = new DbQuery();
            $dbquery->select('*');
            $dbquery->from('delivery_date_exceptions');
            $dbquery->where('id_zone = 0 OR id_zone = '.(int)$id_zone);

            $exceptions[$id_zone] = Db::getInstance()->ExecuteS($dbquery->build());
        }

        $values = array('preparation' => array(), 'delivery' => array());

        if (isset($exceptions[$id_zone])) {
            foreach ($exceptions[$id_zone] as $exception) {
                if ($exception['id_carrier'] == $id_carrier || !$exception['id_carrier']) {
                    if (!$exception['preparation']) {
                        $values['preparation'][] = $exception['date'];
                    }
                    if (!$exception['delivery']) {
                        $values['delivery'][] = $exception['date'];
                    }
                }
            }
        }

        return $values;
    }

    public function hookDisplayBeforeCarrier($params)
    {
        $id_zone = Address::getZoneById($this->context->cart->id_address_delivery);
        if (!$id_zone) {
            $country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
            $id_zone = $country->id_zone;
        }

        $dbquery = new DbQuery();
        $dbquery->select('*');
        $dbquery->from('delivery_date');
        $dbquery->where('id_zone IN (0, '.(int)$id_zone.')');
        $conditions = Db::getInstance()->ExecuteS($dbquery->build());
        $carriers = Carrier::getCarriersForOrder($id_zone, Customer::getGroupsStatic($this->context->cart->id_customer));
        $deliveries = array();
        $config = Configuration::getMultiple(array(
            'DELIVERYDATE_PREP_DAYS',
            'DELIVERYDATE_PREP_TIME',
            'DELIVERYDATE_OUT_OF_STOCK',
            'PS_STOCK_MANAGEMENT',
            'DELIVERYDATE_POSITION',
            'DELIVERYDATE_DATE_MAX',
            'DELIVERYDATE_SUPPLIERS_DELAY',
        ));
        $config['DELIVERYDATE_REASON'] = Configuration::get('DELIVERYDATE_REASON', $this->context->language->id);

        if (!$conditions || empty($carriers) || !isset($config['DELIVERYDATE_PREP_DAYS'])) {
            return false;
        }

        $oos_products_suppliers = $config['PS_STOCK_MANAGEMENT'] ? $this->getOutOfStockProductsSuppliersInCart() : false;

        // Get preparation days and exit if at least one day is not selected
        $prep_days = explode(',', $config['DELIVERYDATE_PREP_DAYS']);
        if (array_search(1, $prep_days) === false) {
            return;
        }

        foreach ($carriers as $carrier) {
            foreach ($conditions as $condition) {
                if ((int)$condition['id_carrier'] != (int)$carrier['id_carrier']) {
                    continue;
                }

                // Make sure that the condition with a zone id has priority
                if (isset($deliveries[$carrier['id_carrier']]) && !$condition['id_zone']) {
                    continue;
                }

                $exceptions = self::getDeliveryExceptions($id_zone, $carrier['id_carrier']);
                $delay = self::getDeliveryDelay($condition, $exceptions, $config, $prep_days, $oos_products_suppliers);
                $deliveries[$carrier['id_carrier']] = array();
                $deliveries[$carrier['id_carrier']]['name'] = $carrier['name'];
                $deliveries[$carrier['id_carrier']]['start'] = date($this->context->language->date_format_lite, $delay['start']);
                $deliveries[$carrier['id_carrier']]['end'] = date($this->context->language->date_format_lite, $delay['end']);
            }
        }

        $this->context->smarty->assign(array(
            'deliveries' => $deliveries,
            'position' => isset($config['DELIVERYDATE_POSITION']) ? $config['DELIVERYDATE_POSITION'] : 'bottom',
            'reason' => isset($config['DELIVERYDATE_REASON']) ? $config['DELIVERYDATE_REASON'] : '',
            'ajax' => Tools::getValue('ajax')
        ));

        return $this->display(__FILE__, 'views/templates/hook/before_carrier_js.tpl').$this->display(__FILE__, 'views/templates/hook/before_carrier.tpl');
    }

    private function saveRule()
    {
        if (Tools::getValue('min') == 0 || Tools::getValue('max') == 0) {
            $this->_errors[] = $this->l('Delivery time must be at least 1 day');
            return false;
        }

        $days = implode(
            ',',
            array(
            (int)Tools::getValue('monday'),
            (int)Tools::getValue('tuesday'),
            (int)Tools::getValue('wednesday'),
            (int)Tools::getValue('thursday'),
            (int)Tools::getValue('friday'),
            (int)Tools::getValue('saturday'),
            (int)Tools::getValue('sunday'))
        );

        $errors = array();

        $id_carrier = Tools::getValue('carrier');
        if (!Validate::isUnsignedInt($id_carrier) || !$id_carrier) {
            $errors[] = $this->l('You must select a carrier');
        }

        $id_zone = Tools::getValue('zone');
        if (!Validate::isUnsignedInt($id_zone) || !$id_zone) {
            $errors[] = $this->l('You must select a zone');
        }

        $min = Tools::getValue('min');
        if (!Validate::isUnsignedInt($min) || !$min) {
            $errors[] = $this->l('You must set a min value');
        }

        $max = Tools::getValue('max');
        if (!Validate::isUnsignedInt($max) || !$max) {
            $errors[] = $this->l('You must set a max value');
        }

        $hours = Tools::getValue('hours');
        if (!Validate::isUnsignedInt($hours) || !$hours) {
            $errors[] = $this->l('Wrong expedition limit');
        }

        $minutes = Tools::getValue('minutes');
        if (!Validate::isUnsignedInt($minutes) || !$minutes) {
            $errors[] = $this->l('Wrong expedition limit');
        }

        $id_rule = Tools::getValue('id_rule');
        if ($id_rule) {
            $sql = 'UPDATE '._DB_PREFIX_.'delivery_date
                SET id_carrier = '.(int)$id_carrier.', id_zone = '.(int)$id_zone.', days = "'.pSQL($days).'", min = '.(int)$min.', max = '.(int)$max.', hours = '.(int)$hours.', minutes = '.(int)$minutes.'
                WHERE id = '.(int)$id_rule;
        } else {
            $sql = 'INSERT INTO '._DB_PREFIX_.'delivery_date (id_carrier, id_zone, days, min, max, hours, minutes)
                            VALUES('.(int)$id_carrier.', '.(int)$id_zone.', "'.pSQL($days).'",
                                         '.(int)$min.', '.(int)$max.', '.(int)$hours.', '.(int)$minutes.')';
        }

        try {
            if (!Db::getInstance()->Execute($sql)) {
                throw new PrestaShopDatabaseException();
            }
            $this->_confirmations[] = $this->l('Saved with success');
        } catch (PrestaShopDatabaseException $e) {
            $this->_errors[] = $this->l('An error occurred');
            return false;
        }

        return true;
    }

    private function saveException()
    {
        $errors = array();

        $date = Tools::getValue('date');
        if (!$date || !Validate::isDate($date)) {
            $errors[] = $this->l('You must select a date');
        }

        $id_carrier = Tools::getValue('carrier');
        if (!Validate::isUnsignedInt($id_carrier)) {
            $errors[] = $this->l('You must select a carrier');
        }

        $id_zone = Tools::getValue('zone');
        if (!Validate::isUnsignedInt($id_zone)) {
            $errors[] = $this->l('You must select a zone');
        }

        if (count($errors)) {
            $this->_errors = array_merge($this->_errors, $errors);
            return false;
        }

        $preparation = Tools::getValue('preparation') ? 1 : 0;
        $delivery = Tools::getValue('delivery') ? 1 : 0;

        $id_exception = Tools::getValue('id_exception');
        if ($id_exception) {
            $sql = 'UPDATE '._DB_PREFIX_.'delivery_date_exceptions
                            SET id_carrier = '.(int)$id_carrier.', id_zone = '.(int)$id_zone.', date = "'.pSQL($date).'", preparation = '.(int)$preparation.', delivery = '.(int)$delivery.'
                            WHERE id = '.(int)$id_exception;
        } else {
            $sql = 'INSERT INTO '._DB_PREFIX_.'delivery_date_exceptions (id_carrier, id_zone, date, preparation, delivery)
                            VALUES('.(int)$id_carrier.', '.(int)$id_zone.', "'.pSQL($date).'",
                                '.(int)$preparation.', '.(int)$delivery.')';
        }

        try {
            if (!Db::getInstance()->Execute($sql)) {
                throw new PrestaShopDatabaseException();
            }
            $this->_confirmations[] = $this->l('Saved with success');
        } catch (PrestaShopDatabaseException $e) {
            $this->_errors[] = $this->l('An error occurred');
            return false;
        }

        return true;
    }

    private function delCondition($id)
    {
        $_GET['dd_tab'] = 'rules';
        if (Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'delivery_date WHERE id = '.(int)$id)) {
            $this->_confirmations[] = $this->l('Rule deleted with success');
            return true;
        } else {
            $this->_errors[] = $this->l('An error occurred');
            return false;
        }
    }

    private function delException($id)
    {
        $_GET['dd_tab'] = 'exceptions';
        if (Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'delivery_date_exceptions WHERE id = '.(int)$id)) {
            $this->_confirmations[] = $this->l('Exception deleted with success');
            return true;
        } else {
            $this->_errors[] = $this->l('An error occurred');
            return false;
        }
    }

    private function saveSettings()
    {
        $errors = array();

        if (Tools::getValue('prep_time') == 0) {
            $errors[] = $this->l('Preparation time must be at least 1 day.');
        }

        if (!Validate::isMessage(Tools::getValue('reason'))) {
            $errors[] = $this->l('Your message can\'t contain HTML.');
        }

        if (count($errors)) {
            $this->_errors = array_merge($this->_errors, $errors);
            return false;
        }

        Configuration::updateValue('DELIVERYDATE_PREP_DAYS', implode(',', array(
            (int)Tools::getValue('monday'),
            (int)Tools::getValue('tuesday'),
            (int)Tools::getValue('wednesday'),
            (int)Tools::getValue('thursday'),
            (int)Tools::getValue('friday'),
            (int)Tools::getValue('saturday'),
            (int)Tools::getValue('sunday')
        )));

        Configuration::updateValue('DELIVERYDATE_PREP_TIME', (int)Tools::getValue('prep_time'));
        Configuration::updateValue('DELIVERYDATE_OUT_OF_STOCK', (int)Tools::getValue('outofstock'));
        Configuration::updateValue('DELIVERYDATE_POSITION', Tools::getValue('position'));
        Configuration::updateValue('DELIVERYDATE_PRODUCT', Tools::getValue('product'));
        Configuration::updateValue('DELIVERYDATE_TABS', (bool)Tools::getValue('tabs'));
        Configuration::updateValue('DELIVERYDATE_DATE_MAX', (bool)Tools::getValue('max_date'));

        $reasons = array();
        foreach (Language::getLanguages() as $lang) {
            $reasons[$lang['id_lang']] = Tools::getValue('reason_'.$lang['id_lang']);
        }
        Configuration::updateValue('DELIVERYDATE_REASON', $reasons);

        $this->_confirmations[] = $this->l('Settings saved with success');

        return true;
    }

    private function renderMainView()
    {
        $this->context->controller->addCSS($this->_path.'views/css/settings.css');

        $config = Configuration::getMultiple(array(
            'DELIVERYDATE_PREP_DAYS',
            'DELIVERYDATE_PREP_TIME',
            'DELIVERYDATE_OUT_OF_STOCK',
            'DELIVERYDATE_POSITION',
            'DELIVERYDATE_REASON',
            'DELIVERYDATE_TABS',
            'DELIVERYDATE_PRODUCT',
            'DELIVERYDATE_DATE_MAX'
        ));

        foreach (Language::getLanguages() as $lang) {
            $config['DELIVERYDATE_REASON'][$lang['id_lang']] = Configuration::get('DELIVERYDATE_REASON', $lang['id_lang']);
        }

        $carriers = Carrier::getCarriers((int)$this->context->cookie->lang, false, false, false, null, Carrier::ALL_CARRIERS);
        if (!$carriers) {
            $this->_errors[] = $this->l('You must create an active carrier before using this module');
            return false;
        }

        $zones = Zone::getZones(true);
        if (!$zones) {
            $this->_errors[] = $this->l('You must create an active zone before using this module');
            return false;
        }

        $conditions = Db::getInstance()->ExecuteS('
            SELECT dd.id, dd.id_carrier, dd.id_zone, dd.min, dd.max, dd.days, CONCAT(LPAD(dd.hours, 2, 0), CONCAT("h", LPAD(dd.minutes, 2, 0))) time_limit, z.name zone_name, c.name carrier_name
            FROM '._DB_PREFIX_.'delivery_date dd
            LEFT JOIN '._DB_PREFIX_.'zone z ON (z.id_zone = dd.id_zone)
            LEFT JOIN '._DB_PREFIX_.'carrier c ON c.id_carrier = dd.id_carrier
            ORDER BY dd.id_carrier, dd.id_zone ASC
        ');

        foreach ($conditions as &$condition) {
            $days = explode(',', $condition['days']);
            $condition['monday'] = isset($days[0]) ? (int)$days[0] : 0;
            $condition['tuesday'] = isset($days[1]) ? (int)$days[1] : 0;
            $condition['wednesday'] = isset($days[2]) ? (int)$days[2] : 0;
            $condition['thursday'] = isset($days[3]) ? (int)$days[3] : 0;
            $condition['friday'] = isset($days[4]) ? (int)$days[4] : 0;
            $condition['saturday'] = isset($days[5]) ? (int)$days[5] : 0;
            $condition['sunday'] = isset($days[6]) ? (int)$days[6] : 0;
            if (!$condition['id_zone']) {
                $condition['zone_name'] = $this->l('All ');
            }
        }

        $days = isset($config['DELIVERYDATE_PREP_DAYS']) ? explode(',', $config['DELIVERYDATE_PREP_DAYS']) : array();
        if (!is_array($days)) {
            $days = array();
        }

        $this->context->smarty->assign(array(
            'zones' => $zones,
            'carriers' => $carriers,
            'days' => $days,
            'config' => $config
        ));

        // Assign rules data
        $this->smarty->assign(array(
            'data_rules' => Tools::jsonEncode(array(
                'columns' => array(
                    array('content' => 'ID', 'key' => 'id', 'center' => true),
                    array('content' => $this->l('Carrier'), 'key' => 'carrier_name'),
                    array('content' => $this->l('Zone'), 'key' => 'zone_name'),
                    array('content' => $this->l('Min days'), 'key' => 'min', 'center' => true),
                    array('content' => $this->l('Max days'), 'key' => 'max', 'center' => true),
                    array('content' => $this->l('Monday'), 'key' => 'monday', 'bool' => true, 'center' => true),
                    array('content' => $this->l('Tuesday'), 'key' => 'tuesday', 'bool' => true, 'center' => true),
                    array('content' => $this->l('Wednesday'), 'key' => 'wednesday', 'bool' => true, 'center' => true),
                    array('content' => $this->l('Thursday'), 'key' => 'thursday', 'bool' => true, 'center' => true),
                    array('content' => $this->l('Friday'), 'key' => 'friday', 'bool' => true, 'center' => true),
                    array('content' => $this->l('Saturday'), 'key' => 'saturday', 'bool' => true, 'center' => true),
                    array('content' => $this->l('Sunday'), 'key' => 'sunday', 'bool' => true, 'center' => true),
                    array('content' => $this->l('Expedition limit'), 'key' => 'time_limit', 'center' => true),
                ),
                'rows' => $conditions,
                'rows_actions' => array(
                    array('title' => 'Edit', 'action' => 'edit_rule', 'icon' => 'pencil', 'img' => '../img/admin/edit.gif'),
                    array('title' => 'Delete', 'action' => 'delete_rule', 'icon' => 'trash', 'img' => '../img/admin/delete.gif')
                ),
                'top_actions' => array(
                    array('title' => $this->l('Add rule'), 'action' => 'add_rule', 'icon' => 'add', 'img' => 'themes/default/img/process-icon-new.png'),
                ),
                'url_params' => array('configure' => $this->name),
                'identifier' => 'id'
            ))
        ));

        $exceptions = Db::getInstance()->ExecuteS(
           'SELECT dde.id, dde.id_carrier, dde.id_zone, dde.date, dde.preparation, dde.delivery, z.name zone_name, c.name carrier_name
            FROM '._DB_PREFIX_.'delivery_date_exceptions dde
            LEFT JOIN '._DB_PREFIX_.'zone z ON (z.id_zone = dde.id_zone)
            LEFT JOIN '._DB_PREFIX_.'carrier c ON c.id_carrier = dde.id_carrier
            ORDER BY dde.date, dde.id_carrier, dde.id_zone ASC'
        );

        foreach ($exceptions as &$exception) {
            $exception['date'] = Tools::displayDate($exception['date'], $this->context->language->date_format_lite);
            if (!$exception['id_carrier']) {
                $exception['carrier_name'] = $this->l('All');
            }
            if (!$exception['id_zone']) {
                $exception['zone_name'] = $this->l('All ');
            }
        }

        // Assign exceptions data
        $this->smarty->assign(array(
            'data_exceptions' => Tools::jsonEncode(array(
                'columns' => array(
                    array('content' => 'ID', 'key' => 'id', 'center' => true),
                    array('content' => $this->l('Carrier'), 'key' => 'carrier_name'),
                    array('content' => $this->l('Zone'), 'key' => 'zone_name'),
                    array('content' => $this->l('Date'), 'key' => 'date'),
                    array('content' => $this->l('Preparation'), 'key' => 'preparation', 'bool' => true, 'center' => true),
                    array('content' => $this->l('Delivery'), 'key' => 'delivery', 'bool' => true, 'center' => true),
                ),
                'rows' => $exceptions,
                'rows_actions' => array(
                    array('title' => 'Edit', 'action' => 'edit_exception', 'icon' => 'pencil', 'img' => '../img/admin/edit.gif'),
                    array('title' => 'Delete', 'action' => 'delete_exception', 'icon' => 'trash', 'img' => '../img/admin/delete.gif')
                ),
                'top_actions' => array(
                    array('title' => $this->l('Add exception'), 'action' => 'add_exception', 'icon' => 'add', 'img' => 'themes/default/img/process-icon-new.png'),
                ),
                'url_params' => array('configure' => $this->name),
                'identifier' => 'id'
            ))
        ));

        $modules = $this->getAddonsModules();
        if (is_array($modules)) {
            shuffle($modules);
        }

        $this->smarty->assign(array(
            'current_url' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module=shipping_logistics&module_name='.$this->name,
            'dd_tab' => Tools::getValue('dd_tab'),
            'modules' => $modules,
            'ps17' => version_compare(_PS_VERSION_, '1.7.0.0', '>='),
            'doc_iso' => (file_exists(_PS_MODULE_DIR_.$this->name.'/docs/readme_'.$this->context->language->iso_code.'.pdf') ? $this->context->language->iso_code : 'en'),
            'active_lang' => $this->context->language->id,
        ));

        return $this->display(__FILE__, 'views/templates/admin/settings.tpl');
    }


    private function renderAddRuleView()
    {
        $id_rule = Tools::getValue('id');

        if ($id_rule) {
            $rule = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'delivery_date WHERE id = '.(int)$id_rule);
            if ($rule) {
                $days = explode(',', $rule['days']);
                $this->smarty->assign(array(
                    'rule' => $rule,
                    'days' => is_array($days) ? $days : array(),
                    'id_rule' => $id_rule
                ));
            }
        }

        $current_url = $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&dd_tab=rules&module_name='.$this->name.'&action=add_rule';
        if ($id_rule && $rule) {
            $current_url .= '&id_rule='.(int)$id_rule;
        }
        $cancel_url = $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module=shipping_logistics&dd_tab=rules&module_name='.$this->name;

        $zones = Zone::getZones(true);

        array_unshift($zones, array(
            'name' => $this->l('All '),
            'id_zone' => 0,
            'active' => 1,
        ));

        $this->smarty->assign(array(
            'current_url' => $current_url,
            'cancel_url' => $cancel_url,
            'carriers' => Carrier::getCarriers($this->context->cookie->lang, false, false, false, null, ALL_CARRIERS),
            'zones' => $zones,
            'doc_iso' => (file_exists(_PS_MODULE_DIR_.$this->name.'/docs/readme_'.$this->context->language->iso_code.'.pdf') ? $this->context->language->iso_code : 'en')
        ));

        $this->context->controller->addCSS($this->_path.'views/css/add_rule.css');

        return $this->display(__FILE__, 'views/templates/admin/add_rule.tpl');
    }


    private function renderAddExceptionView()
    {
        $id_exception = Tools::getValue('id');
        $exception = false;

        if ($id_exception) {
            $exception = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'delivery_date_exceptions WHERE id = '.(int)$id_exception);
            if ($exception) {
                $this->smarty->assign('exception', $exception);
            }
        }

        $current_url = $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&dd_tab=exceptions&module_name='.$this->name.'&action=add_exception';
        if ($id_exception && $exception) {
            $current_url .= '&id_exception='.(int)$id_exception;
        }
        $cancel_url = $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module=shipping_logistics&dd_tab=exceptions&module_name='.$this->name;

        $this->smarty->assign(array(
            'current_url' => $current_url,
            'cancel_url' => $cancel_url,
            'carriers' => Carrier::getCarriers($this->context->cookie->lang, false, false, false, null, ALL_CARRIERS),
            'zones' => Zone::getZones(true),
            'doc_iso' => (file_exists(_PS_MODULE_DIR_.$this->name.'/docs/readme_'.$this->context->language->iso_code.'.pdf') ? $this->context->language->iso_code : 'en')
        ));

        $this->context->controller->addJqueryUI('ui.datepicker');

        return $this->display(__FILE__, 'views/templates/admin/add_exception.tpl');
    }

    private function getAddonsModules()
    {
        if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            return false;
        }

        $modules = Configuration::get('DELIVERYDATE_MODULES');
        $modules_date = Configuration::get('DELIVERYDATE_MODULES_DATE');

        if ($modules && strtotime('+1 WEEK', $modules_date) > time()) {
            return Tools::jsonDecode($modules);
        }

        $post_data = http_build_query(array(
            'version' => _PS_VERSION_,
            'iso_lang' => Tools::strtolower(Context::getContext()->language->iso_code),
            'iso_code' => Tools::strtolower(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'))),
            'module_key' => $this->module_key,
            'method' => 'contributor',
            'action' => 'all_products'
        ));

        $context = stream_context_create(array(
            'http' => array(
                'method'    => 'POST',
                'content' => $post_data,
                'header'    => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 5
            )
        ));
        $content = Tools::file_get_contents('https://api.addons.prestashop.com', false, $context);

        if (!$content) {
            return false;
        }

        $json = Tools::jsonDecode($content);

        if (!isset($json->products)) {
            return false;
        }

        Configuration::updateValue('DELIVERYDATE_MODULES', Tools::jsonEncode($json->products));
        Configuration::updateValue('DELIVERYDATE_MODULES_DATE', time());

        return $json->products;
    }

    public function getContent()
    {
        // Checking if exceptions table exists (since 1.2.0)
        $exceptions_table = Db::getInstance()->ExecuteS('SHOW TABLES LIKE "'._DB_PREFIX_.'delivery_date_exceptions"');
        if (!$exceptions_table || empty($exceptions_table)) {
            $this->createExceptionsTable();
            $this->registerHook('productTab');
            $this->registerHook('productTabContent');
        }

        // Dispatcher
        switch (Tools::getValue('action')) {

            case 'delete_rule':
                $this->delCondition(Tools::getValue('id'));
                $html = $this->renderMainView();
                break;

            case 'delete_exception':
                $this->delException(Tools::getValue('id'));
                $html = $this->renderMainView();
                break;

            case 'save_settings':
                $this->saveSettings();
                $html = $this->renderMainView();
                break;

            case 'add_rule':
            case 'edit_rule':
                if ((Tools::isSubmit('addRule') && !$this->saveRule()) || !Tools::isSubmit('addRule')) {
                    $html = $this->renderAddRuleView();
                } else {
                    $html = $this->renderMainView();
                }
                break;

            case 'add_exception':
            case 'edit_exception':
                if (!(Tools::isSubmit('addException') && $this->saveException())) {
                    $html = $this->renderAddExceptionView();
                    break;
                }
                // no break

            default:
                $html = $this->renderMainView();
        }

        $this->context->controller->addJS($this->_path.'views/js/riot.js');

        if (!(int)Configuration::get('PS_DISPLAY_QTIES') && (int)Configuration::get('DELIVERYDATE_PRODUCT')) {
            $this->_warnings[] = $this->l('If you have issues with the dates on the product sheet, try yo activate the "Display available quantities on the product page" option in the "Preferences > Product".');
        }

        $this->smarty->assign(array(
            'errors' => $this->_errors,
            'confirmations' => $this->_confirmations,
            'warnings' => $this->_warnings
        ));

        $alerts = $this->display(__FILE__, 'views/templates/admin/alerts.tpl');

        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $satisfaction = $this->display(__FILE__, 'views/templates/admin/satisfaction.tpl');
        } else {
            $satisfaction = '';
        }

        return $alerts.$html.$this->getLaPosteAd().$satisfaction.$this->display(__FILE__, 'views/templates/admin/prestui/ps-tags.tpl');
    }

    private function getLaPosteAd()
    {
        if (!Module::isInstalled('lapostetracking')) {
            $laposte_carrier = Db::getInstance()->getValue('
                SELECT name FROM '._DB_PREFIX_.'carrier WHERE name REGEXP "chronopost|la ?poste|dpd" AND deleted = 0
            ');
            if ($laposte_carrier) {
                $this->context->smarty->assign(array(
                    'iso_lang' => Language::getIsoById(Context::getContext()->language->id)
                ));
                return $this->display(__FILE__, 'views/templates/admin/laposte.tpl');
            }
        }
        return '';
    }
}
