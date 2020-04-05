<?php

class ZipCodeZone extends Module
{
    private $countries_with_letters = array('CA', 'GB', 'IE', 'NL','BJ', 'AR');
    private static $id_zones = array();

    public function __construct()
    {
        $this->name = 'zipcodezone';
        $this->tab = 'shipping_logistics';
        $this->version = '1.4.3';
        $this->module_key = '6c7a3d34fa01934e71f53aae16f2698b';
        $this->author = 'MARICHAL Emmanuel';

        parent::__construct();

        $this->displayName = $this->l('Shipping fees based on zipcodes');
        $this->description = $this->l('Assign zip codes to zones easily');

        $this->need_instance = false;
        $this->bootstrap = true;

        $this->id_lang = (int)$this->context->language->id;
        $this->iso_lang = Language::getIsoById($this->id_lang);

        $this->addons_id = 5711;

        $this->table_name = 'zip_code_zone';
    }

    public function install()
    {
        return $this->installDb() && parent::install() && $this->registerHook('actionGetIDZoneByAddressID');
    }

    private function installDb()
    {
        return Db::getInstance()->Execute('
                CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.pSQL($this->table_name).'` (
                    `id` int(10) NOT NULL AUTO_INCREMENT,
                    `id_country` int(10) DEFAULT NULL,
                    `id_zone` int(10) DEFAULT NULL,
                    `min` int(10) DEFAULT NULL,
                    `max` int(10) DEFAULT NULL,
                    `list` LONGTEXT NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `ZCZCountryIndex` (`id_country`),
                    KEY `ZCZZoneIndex` (`id_zone`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;');
    }

    public function hookActionGetIDZoneByAddressID($params)
    {
        $id_address = $params['id_address'];
        if (isset(self::$id_zones[$id_address])) {
            return self::$id_zones[$id_address];
        }

        $zone = false;

        $address = new Address((int)$id_address);
        $dbquery = new DbQuery();
        $iso_code = Country::getIsoById($address->id_country);
        $dbquery->from('zip_code_zone', 'zcz');
        $dbquery->where('zcz.id_country = '.(int)$address->id_country);
        if (in_array($iso_code, $this->countries_with_letters)) {
            $dbquery->select('id_zone, list');
            $rows = Db::getInstance()->Executes($dbquery->build());
            $postcode = preg_replace('/[^%0-9\-A-Z]+/i', '', $address->postcode);
            foreach ($rows as $row) {
                foreach (preg_split('/\r\n|\r|\n/', $row['list']) as $zipcode) {
                    $zipcode = preg_replace('/[^%0-9\-A-Z]+/i', '', $zipcode);
                    if (preg_match('/^'.preg_replace('/%/', '[0-9A-Z]', $zipcode).'$/i', $postcode)) {
                        $zone = $row['id_zone'];
                        break 2;
                    }
                }
            }
        } else {
            $dbquery->select('id_zone');
            $dbquery->where('zcz.min <= '.(int)preg_replace('/[^0-9]+/', '', $address->postcode));
            $dbquery->where('zcz.max >= '.(int)preg_replace('/[^0-9]+/', '', $address->postcode));
            $zone = Db::getInstance()->getValue($dbquery->build());
        }

        self::$id_zones[$id_address] = $zone;

        return self::$id_zones[$id_address];
    }

    // Check if zipcodes are not overlapping
    private function checkOverlap($min, $max, $id_country, $id_condition = false)
    {
        $conditions = $this->getConditions($id_country);
        if ($conditions) {
            foreach ($conditions as &$condition) {
                if ($id_condition == $condition['id']) {
                    continue;
                }

                if (
                    ($condition['min'] <= (int)$min && $condition['max'] >= (int)$min)
                    || ($condition['min'] <= (int)$max && $condition['max'] >= (int)$max)
                    || ((int)$min <= $condition['min'] && (int)$max >= $condition['min'])
                    || ((int)$min <= $condition['max'] && (int)$max >= $condition['max'])
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateSubmit($id_country, $id_zone, $min, $max, $zipcodes, $id_condition = false)
    {

        // Check if the country and the condition have the same zone
        $country = new Country($id_country);
        if (Validate::isLoadedObject($country) && $country->id_zone == $id_zone) {
            return $this->displayError($this->l('You don\'t need to create a condition with the same zone as the country\'s default zone. You can change the default zone for this country in Localization > Countries'));
        }

        if (in_array($country->iso_code, $this->countries_with_letters)) {
            if (!$zipcodes) {
                return $this->displayError($this->l('You must submit zipcodes'));
            }
        } else {
            // Check if there is already a condition with these zipcodes
            if (!$this->checkOverlap($min, $max, $id_country, $id_condition)) {
                return $this->displayError($this->l('You already have a rule containing these zipcodes. You can\'t have 2 rules with the same zipcode(s).'));
            }

            // Check if zipcodes are positive numbers
            if ((int)$min == 0 || (int)$max == 0) {
                return $this->displayError($this->l('You must submit valid zipcodes.'));
            }

            // Check if the zipcode is not too long, based on the zipcode's format
            $zip_code_format = preg_replace('/[^N]+/', '', $country->zip_code_format);
            if (Tools::strlen($zip_code_format) && ((Tools::strlen($zip_code_format) < Tools::strlen($min)) || (Tools::strlen($zip_code_format) < Tools::strlen($max)))) {
                return $this->displayError($this->l('You must submit valid zipcodes.'));
            }
        }

        return true;
    }

    private function addCondition($id_country, $id_zone, $min, $max, $zipcodes)
    {
        $data = array(
            'id_country' => (int)$id_country,
            'id_zone' => (int)$id_zone,
            'min' => (int)$min,
            'max' =>(int)$max,
            'list' => pSQL($zipcodes)
        );

        if (Db::getInstance()->insert($this->table_name, $data)) {
            return $this->displayConfirmation($this->l('Condition added with success. Remember to update your carrier(s) in Shipping > Carriers to set your shipping fees.'));
        } else {
            return $this->displayError($this->l('An error occurred'));
        }
    }

    private function updateCondition($id_condition, $id_country, $id_zone, $min, $max, $zipcodes)
    {
        $iso_code = Country::getIsoById($id_country);
        $zip_code_with_letters = in_array($iso_code, $this->countries_with_letters);
        $data = array(
            'id_country' => (int)$id_country,
            'id_zone' => (int)$id_zone,
            'min' => $zip_code_with_letters ? null : (int)$min,
            'max' =>$zip_code_with_letters ? null : (int)$max,
            'list' => $zip_code_with_letters ? pSQL($zipcodes) : null,
        );
        if (Db::getInstance()->update($this->table_name, $data, 'id = '.(int)$id_condition)) {
            return $this->displayConfirmation($this->l('Condition updated with success. Remember to update your carrier(s) in Shipping > Carriers to set your shipping fees.'));
        } else {
            return $this->displayError($this->l('An error occurred'));
        }
    }

    private function delCondition($id)
    {
        if (is_array($id)) {
            $id = implode(',', array_map('intval', $id));
        }

        if (Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'zip_code_zone WHERE id IN ('.pSQL($id).')')) {
            return $this->displayConfirmation($this->l('Condition(s) deleted with success'));
        } else {
            return $this->displayError($this->l('An error occurred'));
        }
    }

    private function addCSV($skip = false, $separator = ';')
    {
        ini_set('auto_detect_line_endings', true);

        if (!isset($_FILES['csv']) || $_FILES['csv']['error'] > 0) {
            return $this->displayError($this->l('You need to submit a valid file'));
        }

        $lines = file($_FILES['csv']['tmp_name']);
        if ($lines === false) {
            return $this->displayError($this->l('An error occurred'));
        }

        $html = '';
        $id_countries_with_letters = $this->getIdCountriesWithLetterZipcodes();
        $zipcode_letters = array();
        foreach ($lines as $line_num => $line) {
            if ($skip && $line_num == 0) {
                continue;
            }

            $line_num += 1;
            $line = array_map('trim', explode($separator, $line));

            if (count($line) > 4 || count($line) < 3) {
                $html .= $this->displayError($this->l('Invalid line:').' '.$line_num);
                continue;
            }

            if (preg_match('/[^0-9]/', $line[0]) || preg_match('/[^0-9]/', $line[1]) || !(int)$line[0] || !(int)$line[1]) {
                $html .= $this->displayError($this->l('Invalid id_country or id_zone (line').' '.$line_num.')');
                continue;
            }

            $data = array(
                'id_country' => $line[0],
                'id_zone' => $line[1],
            );

            if (in_array($line[0], $id_countries_with_letters)) {
                $key = $line[0].'-'.$line[1];
                if (!isset($zipcode_letters[$key])) {
                    $zipcode_letters[$key] = array();
                }
                $zipcode_letters[$key][] = trim($line[2]);
            } else {
                if (count($line) != 4) {
                    $html .= $this->displayError($this->l('Invalid line:').' '.$line_num);
                    continue;
                }

                if (preg_match('/[^0-9]/', $line[2]) || preg_match('/[^0-9]/', $line[3])) {
                    $html .= $this->displayError($this->l('Invalid zipcodes (line').' '.$line_num.')');
                    continue;
                }

                $data['min'] = $line[2];
                $data['max'] = $line[3];

                if (!Db::getInstance()->insert($this->table_name, $data)) {
                    $html .= $this->displayError($this->l('Error while saving line').' '.$line_num.'.');
                }
            }
        }

        if (!empty($zipcode_letters)) {
            foreach ($zipcode_letters as $index => $z) {
                list($id_country, $id_zone) = explode('-', $index);
                $data = array(
                    'id_country' => $id_country,
                    'id_zone' => $id_zone,
                    'list' => implode(PHP_EOL, $z),
                );
                if (!Db::getInstance()->insert($this->table_name, $data)) {
                    $html .= $this->displayError($this->l('Error while saving zipcodes for country ').' '.$id_country.' '.$this->l('and zone').' '.$id_zone);
                }
            }
        }

        return $html.$this->displayConfirmation($this->l('Conditions added with success'));
    }

    public function createTemplate($tpl_name)
    {
        return $this->context->smarty->createTemplate($this->context->smarty->getTemplateDir(0).$tpl_name, $this->context->smarty);
    }

    public function getContent()
    {
        $this->context->controller->addJS($this->_path.'views/js/zipcodezone.js');
        $this->context->controller->addCSS($this->_path.'views/css/modules.css');

        $html = '';

        if (Tools::isSubmit('submitUpdateCondition') || Tools::isSubmit('submitAddCondition')) {
            $id_zone = Tools::getValue('id_zone');

            if (!$id_zone && Tools::getValue('zone_name')) {
                $id_zone = Zone::getIdByName(Tools::getValue('zone_name'));
                if (!$id_zone) {
                    $zone = new Zone();
                    $zone->name = Tools::getValue('zone_name');
                    if ($zone->add()) {
                        $id_zone = $zone->id;
                    } else {
                        $html .= $this->displayError($this->l('An error occurred while creating the new zone'));
                    }
                }
            }

            if ($id_zone) {
                if (Tools::getValue('multiple')) {
                    $min = Tools::getValue('min');
                    $max = Tools::getValue('max');
                } else {
                    $min = $max = Tools::getValue('zipcode');
                }

                $min = preg_replace('/[^0-9]+/', '', $min);
                $max = preg_replace('/[^0-9]+/', '', $max);

                if ($min > $max) {
                    $temp = $max;
                    $max = $min;
                    $min = $temp;
                }

                $zipcodes = Tools::getValue('zipcodes');
                if ($zipcodes) {
                    $zipcodes = preg_replace('/[^%0-9 \-A-Z\n]+/i', '', $zipcodes);
                }

                $id_condition = Tools::getValue('id_condition');

                $submit_validation = $this->validateSubmit(
                    Tools::getValue('id_country'),
                    $id_zone,
                    $min,
                    $max,
                    $zipcodes,
                    $id_condition
                );
                if ($submit_validation === true) {
                    if (Tools::isSubmit('submitUpdateCondition')) {
                        $html .= $this->updateCondition(
                            $id_condition,
                            Tools::getValue('id_country'),
                            $id_zone,
                            $min,
                            $max,
                            $zipcodes
                        );
                    } else {
                        $html .= $this->addCondition(
                            Tools::getValue('id_country'),
                            $id_zone,
                            $min,
                            $max,
                            $zipcodes
                        );
                    }
                } else {
                    $html .= $submit_validation;
                }
            }
        }

        if (Tools::isSubmit($this->table_name.'Box')) {
            $html .= $this->delCondition(Tools::getValue($this->table_name.'Box'));
        }

        if (Tools::isSubmit('delete'.$this->table_name) && (int)Tools::getValue('id')) {
            $html .= $this->delCondition(Tools::getValue('id'));
        }

        if (Tools::isSubmit('addCSV') && isset($_FILES['csv'])) {
            $html .= $this->addCSV(Tools::getIsset('ignore'), Tools::getValue('separator'));
        }

        $doc_iso = file_exists(_PS_MODULE_DIR_.$this->name.'/docs/readme_'.$this->iso_lang.'.pdf') ? $this->iso_lang : 'en';
        $this->context->smarty->assign(array(
            'doc_link' => '../modules/'.$this->name.'/docs/readme_'.$doc_iso.'.pdf',
            'add_link' => $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&newCondition=1&token='.Tools::getAdminTokenLite('AdminModules'),
            'cancel_link' => $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'support_link' => 'http://addons.prestashop.com/'.$this->iso_lang.'/contact-community.php?id_product='.$this->addons_id,
            'rating_link' => 'http://addons.prestashop.com/'.$this->iso_lang.'/ratings.php'
        ));

        // Check if delivery is restricted
        if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES')) {
            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                $html .= $this->displayError($this->l('The countries are restricted on your shop. Go in International > Locations, at the bottom of the page, to deactivate the restriction.'));
            } else {
                $html .= $this->displayError($this->l('The countries are restricted on your shop. Go in Localization > Countries, at the bottom of the page, to deactivate the restriction.'));
            }
        }

        if (Tools::getValue('newCondition')) {
            $html .= $this->renderForm();
        } elseif (Tools::isSubmit('update'.$this->table_name) && Tools::getValue('id')) {
            $html .= $this->renderForm(Tools::getValue('id'));
        } else {
            $html .= $this->renderList();
        }

        $html .= $this->display(__FILE__, 'views/templates/admin/js_assign.tpl');

        return $html;
    }

    private function getIdCountriesWithLetterZipcodes()
    {
        $dbquery = new DbQuery();
        $dbquery->select('id_country');
        $dbquery->from('country', 'c');
        $dbquery->where('iso_code IN ("'.implode('","', array_map('pSQL', $this->countries_with_letters)).'")');

        $results = Db::getInstance()->ExecuteS($dbquery->build());
        $ids = array();
        if ($results) {
            foreach ($results as $result) {
                $ids[] = (int)$result['id_country'];
            }
        }
        return $ids;
    }

    private function renderForm($id_condition = false)
    {
        $condition = false;
        if ($id_condition) {
            $dbquery = new DbQuery();
            $dbquery->select('id, zcz.*,
            LPAD(zcz.min, LENGTH(IF(zip_code_format IS NULL OR zip_code_format = "", zcz.min, REPLACE(zip_code_format, "-", ""))), 0) as min_formatted,
            LPAD(zcz.max, LENGTH(IF(zip_code_format IS NULL OR zip_code_format = "", zcz.max, REPLACE(zip_code_format, "-", ""))), 0) as max_formatted,
            zip_code_format');
            $dbquery->from(pSQL($this->table_name), 'zcz');
            $dbquery->leftJoin('country', 'c', 'c.id_country = zcz.id_country');
            $dbquery->where('id = '.(int)$id_condition);
            $condition = Db::getInstance()->getRow($dbquery->build());
            if ($condition) {
                $condition = $this->formatCondition($condition);
            }
        }

        $add_condition = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Add condition'),
                    'icon' => 'icon-plus'
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Country'),
                        'name' => 'id_country',
                        'desc' => $this->l('Select the country in which you would like to create multiple delivery rates.'),
                        'options' => array(
                            'query' => Country::getCountries($this->id_lang, true),
                            'id' => 'id_country', 'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Zone'),
                        'name' => 'id_zone',
                        'desc' => $this->l('Select an existing delivery zone or create a new one (one zone = one delivery rate).'),
                        'options' => array(
                            'query' => array_merge(Zone::getZones(true), array(array('id_zone' => 0, 'name' => '+ '.$this->l('New zone')))),
                            'id' => 'id_zone', 'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Zone name'),
                        'name' => 'zone_name',
                        'class' => 'fixed-width-xl'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Multiple zipcodes'),
                        'name' => 'multiple',
                        'desc' => $this->l('Select yes if you want to choose a range of zipcodes.'),
                        'values' => array(
                            array(
                                'id' => 'multiple_on',
                                'value' => true,
                            ),
                            array(
                                'id' => 'multiple_off',
                                'value' => false,
                            )
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Zip code min'),
                        'name' => 'min',
                        'class' => 'fixed-width-md'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Zip code max'),
                        'name' => 'max',
                        'class' => 'fixed-width-md'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Zip code'),
                        'name' => 'zipcode',
                        'class' => 'fixed-width-md'
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => 'Zipcodes',
                        'name' => 'zipcodes',
                        'desc' => $this->l('One zipcode per line. You can use % to match any character (Eg: HAJ %%%)'),
                        'class' => 'fixed-width-xxl'
                    ),
                ),
                'submit' => array(
                    'name' => $condition ? 'updateCondition' : 'addCondition',
                    'title' => $condition ? $this->l('Update') : $this->l('Add')
                )
            ),
        );

        $csv = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Import CSV'),
                    'icon' => 'icon-upload'
                ),
                'input' => array(
                    array(
                        'type' => 'file',
                        'label' => $this->l('CSV file'),
                        'name' => 'csv',
                        'desc' => '<a href="../modules/zipcodezone/example.csv">'.$this->l('Click to see a CSV sample').'</a>'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Ignore first line'),
                        'name' => 'ignore',
                        'values' => array(
                            array(
                                'id' => 'ignore_on',
                                'value' => true,
                            ),
                            array(
                                'id' => 'ignore_off',
                                'value' => false,
                            )
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Separator'),
                        'name' => 'separator',
                        'class' => 'fixed-width-xs'
                    ),
                ),
                'submit' => array(
                    'name' => 'addCSV',
                    'title' => $this->l('Import')
                )
            ),
        );

        $helper = new HelperForm();

        if ($condition) {
            $helper->id = $condition['id'];
        } // Hack in JS

        $helper->show_toolbar = false;
        $helper->table = $this->table_name;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->submit_action = $condition ? 'submitUpdateCondition' : 'submitAddCondition';

        $fields_value = array(
            'min' => $condition ? $condition['min_formatted'] : false,
            'max' => $condition ? $condition['max_formatted'] : false,
            'id_zone' => $condition ? $condition['id_zone'] : -1,
            'zipcode' => $condition ? $condition['min_formatted'] : false,
            'zipcodes' => $condition ? $condition['list'] : '',
            'multiple' => $condition && $condition['min'] != $condition['max'],
            'zone_name' => '',
            'id_country' => $condition ? $condition['id_country'] : 0,
            'csv' => '',
            'first' => '',
            'separator' => ';',
            'ignore' => true
        );

        $helper->tpl_vars = array(
            'fields_value' => $fields_value,
        );

        $this->context->smarty->assign('id_countries_with_letters', $this->getIdCountriesWithLetterZipcodes());
        $html = $this->display(__FILE__, 'views/templates/admin/countries.tpl');

        return $html.$helper->generateForm(array($add_condition)).($condition ? '' : $helper->generateForm(array($csv)));
    }

    private function formatCondition($condition)
    {
        $dash_pos = strpos($condition['zip_code_format'], '-');
        if ($dash_pos !== false) {
            $condition['min_formatted'] = substr_replace($condition['min_formatted'], '-', $dash_pos, 0);
            $condition['max_formatted'] = substr_replace($condition['max_formatted'], '-', $dash_pos, 0);
        }
        $condition['zipcodes'] = $condition['min_formatted'];
        if ($condition['min'] !== $condition['max']) {
            $condition['zipcodes'] .= ' '.$this->l('to').' '.$condition['max_formatted'];
        }
        return $condition;
    }

    private function getConditions($id_country = false)
    {
        $dbquery = new DbQuery();
        $dbquery->select('zcz.id, zcz.id_country, zcz.id_zone, z.name AS zone_name, cl.name AS country_name,
        LPAD(zcz.min, LENGTH(IF(zip_code_format IS NULL OR zip_code_format = "", zcz.min, REPLACE(zip_code_format, "-", ""))), 0) as min_formatted,
        LPAD(zcz.max, LENGTH(IF(zip_code_format IS NULL OR zip_code_format = "", zcz.max, REPLACE(zip_code_format, "-", ""))), 0) as max_formatted
        , zcz.min, zcz.max, zcz.list, SUM(ca.active) as nb_carriers, zip_code_format, iso_code as iso_country, c.id_zone as country_zone');
        $dbquery->from(pSQL($this->table_name), 'zcz');
        $dbquery->leftJoin('zone', 'z', 'z.id_zone = zcz.id_zone');
        $dbquery->leftJoin('country_lang', 'cl', 'cl.id_country = zcz.id_country AND cl.id_lang = '.(int)$this->id_lang);
        $dbquery->leftJoin('country', 'c', 'c.id_country = cl.id_country');
        $dbquery->leftJoin('carrier_zone', 'caz', 'caz.id_zone = zcz.id_zone');
        $dbquery->leftJoin('carrier', 'ca', 'ca.id_carrier = caz.id_carrier');
        if ($id_country) {
            $dbquery->where('zcz.id_country = '.(int)$id_country);
        }
        $dbquery->orderBy('zcz.id_country, zcz.min ASC');
        $dbquery->groupBy('zcz.id');

        $results = Db::getInstance()->ExecuteS($dbquery->build());

        if ($results) {
            foreach ($results as &$result) {
                if (!in_array($result['iso_country'], $this->countries_with_letters)) {
                    $result = $this->formatCondition($result);
                } else {
                    $nb_zipcodes = count(explode(PHP_EOL, $result['list']));
                    if ($nb_zipcodes === 1) {
                        $result['zipcodes'] = $result['list'];
                    } else {
                        $result['zipcodes'] = count(explode(PHP_EOL, $result['list'])).' '.$this->l('rule(s)');
                    }
                }
            }
        } else {
            $results = array();
        }
        return $results;
    }

    public function paginateConditions($conditions, $page = 1, $pagination = 50)
    {
        if (count($conditions) > $pagination) {
            $conditions = array_slice($conditions, $pagination * ($page - 1), $pagination);
        }

        return $conditions;
    }

    private function renderList()
    {
        $fields_list = array(
            'country_name' => array(
                'title' => $this->l('Country'),
                'type' => 'text',
                'search' => false
            ),
            'zipcodes' => array(
                'title' => $this->l('Zip code(s)'),
                'type' => 'text',
                'search' => false
            ),
            'zone_name' => array(
                'title' => $this->l('New Delivery Zone'),
                'type' => 'text',
                'search' => false,
                'color' => 'color'
            )
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->identifier = 'id';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->simple_header = false;
        $helper->module = $this;
        $helper->no_link = true;
        $helper->title = $this->l('Conditions');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->table = $this->table_name;

        $conditions = $this->getConditions();
        $zones_without_carriers = $countries_with_same_zone = false;

        if ($conditions) {
            $helper->bulk_actions = array(
                'delete' => array(
                    'text' => $this->l('Delete selected'),
                    'confirm' => $this->l('Delete selected items?'),
                    'icon' => 'icon-trash'
                )
            );

            $helper->force_show_bulk_actions = true;

            foreach ($conditions as &$condition) {
                if ($condition['nb_carriers'] < 1) {
                    $condition['color'] = '#EC2E15';
                    $zones_without_carriers = true;
                }
                if ($condition['id_zone'] == $condition['country_zone']) {
                    $condition['color'] = '#fcc94f';
                    $countries_with_same_zone = true;
                }
            }
        }
        $helper->listTotal = count($conditions);

        $page = ($page = Tools::getValue('submitFilter'.$this->table_name)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($this->table_name.'_pagination')) ? $pagination : 50;
        $conditions = $this->paginateConditions($conditions, $page, $pagination);

        // Adding 'Add' button
        $helper->toolbar_btn['new'] = array(
            'href' => $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
            .'&newCondition=1&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new condition')
        );

        // Adding 'Documentation' button
        $doc_iso = file_exists(_PS_MODULE_DIR_.$this->name.'/docs/readme_'.$this->iso_lang.'.pdf') ? $this->iso_lang : 'en';
        $helper->toolbar_btn['help'] = array(
            'href' => '../modules/'.$this->name.'/docs/readme_'.$doc_iso.'.pdf',
            'target' => '_blank',
            'desc' => $this->l('View documentation')
        );

        $html = '';

        if ($zones_without_carriers) {
            $html .= $this->displayError($this->l('The zones in red are not currently assigned to any carrier. If you want these zones to be delivered, please edit your carriers to set a price.'));
        }

        if ($countries_with_same_zone) {
            $html .= $this->displayError($this->l('The rules in orange are useless, you can delete them since the zones are the same as the default zone of the country.'));
        }

        $html .= $helper->generateList($conditions, $fields_list);

        if ($helper->listTotal > 0) {
            $this->context->smarty->assign('iso_lang', $this->iso_lang);
            $html .= $this->display(__FILE__, 'views/templates/admin/satisfaction.tpl');
            $html .= $this->getLaPosteAd();
            $html .= $this->display(__FILE__, 'views/templates/admin/addons.tpl');
        }
        return $html;
    }

    private function getLaPosteAd()
    {
        if (!Module::isInstalled('lapostetracking')) {
            $laposte_carrier = Db::getInstance()->getValue('
                SELECT name FROM '._DB_PREFIX_.'carrier WHERE name REGEXP "chronopost|la ?poste|dpd" AND deleted = 0
            ');
            if ($laposte_carrier) {
                return $this->display(__FILE__, 'views/templates/admin/laposte.tpl');
            }
        }
        return '';
    }
}
