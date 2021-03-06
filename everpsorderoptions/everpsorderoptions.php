<?php
/**
 * Project : everpsorderoptions
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'everpsorderoptions/models/EverpsorderoptionsField.php';
require_once _PS_MODULE_DIR_.'everpsorderoptions/models/EverpsorderoptionsOption.php';
require_once _PS_MODULE_DIR_.'everpsorderoptions/models/EverCheckoutStep.php';

class Everpsorderoptions extends Module
{
    private $html;
    private $postErrors = array();
    private $postSuccess = array();

    public function __construct()
    {
        $this->name = 'everpsorderoptions';
        $this->tab = 'front_office_features';
        $this->version = '2.1.2';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Ever Order Options');
        $this->description = $this->l('Add options on extra order step');
        $this->confirmUninstall = $this->l('Are you sure ?');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
        return (parent::install()
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('displayAdminOrder')
            && $this->registerHook('header')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('actionOrderStatusUpdate')
            && $this->installModuleTab(
                'AdminEverPsOptions',
                'SELL',
                $this->l('Order options')
            )
            && $this->installModuleTab(
                'AdminEverPsOptionsField',
                'AdminEverPsOptions',
                $this->l('Form fields')
            )
            && $this->installModuleTab(
                'AdminEverPsOptionsOption',
                'AdminEverPsOptions',
                $this->l('Form options')
            ));
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
        return parent::uninstall()
            && $this->uninstallModuleTab('AdminEverPsOptions')
            && $this->uninstallModuleTab('AdminEverPsOptionsGroup')
            && $this->uninstallModuleTab('AdminEverPsOptionsField')
            && $this->uninstallModuleTab('AdminEverPsOptionsOption');
    }

    private function installModuleTab($tabClass, $parent, $tabName)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $tabClass;
        $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        $tab->position = Tab::getNewLastPosition($tab->id_parent);
        $tab->module = $this->name;
        if ($tabClass == 'AdminEverPsOptions' && $this->isSeven) {
            $tab->icon = 'icon-team-ever';
        }

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int)$lang['id_lang']] = $tabName;
        }

        return $tab->add();
    }

    private function uninstallModuleTab($tabClass)
    {
        $tab = new Tab((int)Tab::getIdFromClassName($tabClass));
        return $tab->delete();
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitEverpsorderoptionsModule')) {
            $this->postValidation();

            if (!count($this->postErrors)) {
                $this->postProcess();
            }
        }

        // Display errors
        if (count($this->postErrors)) {
            foreach ($this->postErrors as $error) {
                $this->html .= $this->displayError($error);
            }
        }

        // Display confirmations
        if (count($this->postSuccess)) {
            foreach ($this->postSuccess as $success) {
                $this->html .= $this->displayConfirmation($success);
            }
        }

        $this->context->smarty->assign('everpsorderoptions_dir', $this->_path);

        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/header.tpl');
        $this->html .= $this->renderForm();
        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/footer.tpl');

        return $this->html;
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEverpsorderoptionsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        $order_states = OrderState::getOrderStates(
            $this->context->language->id
        );
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Forms are validated when the order is'),
                        'name' => 'EVERPSOPTIONS_VALIDATION',
                        'options' => array(
                            'query' => $order_states,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Forms are cancelled when the order is'),
                        'name' => 'EVERPSOPTIONS_CANCEL',
                        'options' => array(
                            'query' => $order_states,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->l('Order step title'),
                        'desc' => $this->l('Please specify order step title'),
                        'hint' => $this->l('Order step is number 3'),
                        'name' => 'EVERPSOPTIONS_TITLE',
                        'required' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'autoload_rte' => true,
                        'lang' => true,
                        'label' => $this->l('Custom text'),
                        'desc' => $this->l('Please specify text on custom order step'),
                        'hint' => $this->l('Will be shown before form'),
                        'name' => 'EVERPSOPTIONS_MSG',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        $form_title = array();
        $form_text = array();
        foreach (Language::getLanguages(false) as $lang) {
            $form_title[$lang['id_lang']] = (Tools::getValue(
                'EVERPSOPTIONS_TITLE_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERPSOPTIONS_TITLE_'.$lang['id_lang']
            ) : '';
            $form_text[$lang['id_lang']] = (Tools::getValue(
                'EVERPSOPTIONS_MSG_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERPSOPTIONS_MSG_'.$lang['id_lang']
            ) : '';
        }
        return array(
            'EVERPSOPTIONS_VALIDATION' => Configuration::get('EVERPSOPTIONS_VALIDATION'),
            'EVERPSOPTIONS_CANCEL' => Configuration::get('EVERPSOPTIONS_CANCEL'),
            'EVERPSOPTIONS_TITLE' => (!empty(
                $form_title[(int)Configuration::get('PS_LANG_DEFAULT')]
            )) ? $form_title : Configuration::getInt(
                'EVERPSOPTIONS_TITLE'
            ),
            'EVERPSOPTIONS_MSG' => (!empty(
                $form_text[(int)Configuration::get('PS_LANG_DEFAULT')]
            )) ? $form_text : Configuration::getInt(
                'EVERPSOPTIONS_MSG'
            ),
        );
    }

    public function postValidation()
    {
        if (Tools::isSubmit('submitEverpsorderoptionsModule')) {
            if (!Tools::getValue('EVERPSOPTIONS_VALIDATION')
                || !Validate::isUnsignedInt(Tools::getValue('EVERPSOPTIONS_VALIDATION'))) {
                $this->postErrors[] = $this->l(
                    'Error: Validated orders is not valid'
                );
            }
            if (!Tools::getValue('EVERPSOPTIONS_CANCEL')
                || !Validate::isUnsignedInt(Tools::getValue('EVERPSOPTIONS_CANCEL'))) {
                $this->postErrors[] = $this->l(
                    'Error: Cancelled orders is not valid'
                );
            }
            foreach (Language::getLanguages(false) as $lang) {
                if (!Tools::getValue('EVERPSOPTIONS_TITLE_'.$lang['id_lang'])
                    || !Validate::isString(Tools::getValue('EVERPSOPTIONS_TITLE_'.$lang['id_lang']))
                ) {
                    $this->postErrors[] = $this->l(
                        'Error: title is not valid for lang '
                    ).$lang['iso_code'];
                }
                if (Tools::getValue('EVERPSOPTIONS_MSG_'.$lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('EVERPSOPTIONS_MSG_'.$lang['id_lang']))
                ) {
                    $this->postErrors[] = $this->l(
                        'Error: text is not valid for lang '
                    ).$lang['iso_code'];
                }
            }
        }
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        $form_title = array();
        $form_text = array();
        foreach (Language::getLanguages(false) as $lang) {
            $form_title[$lang['id_lang']] = (
                Tools::getValue('EVERPSOPTIONS_TITLE_'
                    .$lang['id_lang'])
            ) ? Tools::getValue(
                'EVERPSOPTIONS_TITLE_'
                .$lang['id_lang']
            ) : '';
            $form_text[$lang['id_lang']] = (
                Tools::getValue('EVERPSOPTIONS_MSG_'
                    .$lang['id_lang'])
            ) ? Tools::getValue(
                'EVERPSOPTIONS_MSG_'
                .$lang['id_lang']
            ) : '';
        }
        Configuration::updateValue(
            'EVERPSOPTIONS_VALIDATION',
            Tools::getValue('EVERPSOPTIONS_VALIDATION')
        );
        Configuration::updateValue(
            'EVERPSOPTIONS_CANCEL',
            Tools::getValue('EVERPSOPTIONS_CANCEL')
        );
        Configuration::updateValue(
            'EVERPSOPTIONS_MSG',
            $form_text,
            true
        );
        Configuration::updateValue(
            'EVERPSOPTIONS_TITLE',
            $form_title,
            true
        );
        $this->postSuccess[] = $this->l('All settings have been saved');
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addCss($this->_path.'views/css/ever.css');
        $controller_name = Tools::getValue('controller');
        if ($controller_name == 'AdminEverPsOptionsField'
            || $controller_name == 'AdminEverPsOptionsOption'
        ) {
            $this->context->controller->addJs($this->_path.'views/js/ever.js');
        }
    }

    public function hookDisplayOrderConfirmation($params)
    {
        $order = $params['order'];
        $sql = new DbQuery;
        $sql->select('checkout_session_data');
        $sql->from(
            'cart'
        );
        $sql->where(
            'id_cart = '.(int)$order->id_cart
        );
        $checkout_session_data = json_decode(
            Db::getInstance()->getValue($sql)
        );
        foreach ($checkout_session_data as $key => $value) {
            if ($key == 'ever-checkout-step') {
                $orderedOptions = $value->everdata;
            }
        }
        if (isset($orderedOptions)) {
            foreach ($orderedOptions as $key => $value) {
                $option = new StdClass();
                $key = substr($key, 19);
                $field = new EverpsorderoptionsField(
                    (int)$key,
                    (int)Context::getContext()->shop->id,
                    (int)Context::getContext()->language->id
                );
                $option->name = $field->field_title;
                if ($field->type == 'select' || $field->type == 'radio' || $field->type == 'checkbox') {
                    $fieldValue = new EverpsorderoptionsOption(
                        (int)$value,
                        (int)Context::getContext()->shop->id,
                        (int)Context::getContext()->language->id
                    );
                    $option->value = $fieldValue->option_title;
                } else {
                    $option->value = $value;
                }
                $options[] = $option;
            }
            $this->context->smarty->assign(array(
                'everimg_dir' => $this->_path,
                'everoptions' => $options,
            ));
            return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        $options = array();
        $order = new Order((int)$params['id_order']);
        $sql = new DbQuery;
        $sql->select('checkout_session_data');
        $sql->from(
            'cart'
        );
        $sql->where(
            'id_cart = '.(int)$order->id_cart
        );
        $checkout_session_data = json_decode(
            Db::getInstance()->getValue($sql)
        );
        foreach ($checkout_session_data as $key => $value) {
            if ($key == 'ever-checkout-step') {
                $orderedOptions = $value->everdata;
            }
        }
        if (isset($orderedOptions)) {
            foreach ($orderedOptions as $key => $value) {
                $option = new StdClass();
                $key = substr($key, 19);
                $field = new EverpsorderoptionsField(
                    (int)$key,
                    (int)Context::getContext()->shop->id,
                    (int)Context::getContext()->language->id
                );
                $option->name = $field->field_title;
                if ($field->type == 'select' || $field->type == 'radio' || $field->type == 'checkbox') {
                    $fieldValue = new EverpsorderoptionsOption(
                        (int)$value,
                        (int)Context::getContext()->shop->id,
                        (int)Context::getContext()->language->id
                    );
                    $option->value = $fieldValue->option_title;
                } else {
                    $option->value = $value;
                }
                $options[] = $option;
            }
            $this->context->smarty->assign(array(
                'everimg_dir' => $this->_path,
                'everoptions' => $options,
            ));
            return $this->display(__FILE__, 'views/templates/admin/order.tpl');
        }
    }

    public function hookActionValidateOrder($params)
    {
        return $this->hookActionOrderStatusUpdate($params);
    }

    public function hookActionOrderStatusUpdate($params)
    {
        $validated = (int)Configuration::get('EVERPSOPTIONS_VALIDATION');
        $cancelled = (int)Configuration::get('EVERPSOPTIONS_CANCEL');
        $optionsStatus = array(
            (int)$validated,
            (int)$cancelled
        );
        if (isset($params['newOrderStatus'])) {
            $orderStatus = $params['newOrderStatus'];
        } else {
            $orderStatus = $params['orderStatus'];
        }
        if (!in_array((int)$orderStatus->id, $optionsStatus)) {
            return;
        }
        if (isset($params['id_order'])) {
            $order = new Order((int)$params['id_order']);
        } else {
            $order = $params['order'];
        }
        $options = array();
        $sql = new DbQuery;
        $sql->select('checkout_session_data');
        $sql->from(
            'cart'
        );
        $sql->where(
            'id_cart = '.(int)$order->id_cart
        );
        $checkout_session_data = json_decode(
            Db::getInstance()->getValue($sql)
        );
        foreach ($checkout_session_data as $key => $value) {
            if ($key == 'ever-checkout-step') {
                $orderedOptions = $value->everdata;
            }
        }
        if (isset($orderedOptions)) {
            foreach ($orderedOptions as $key => $value) {
                if (!Validate::isUnsignedInt($value)) {
                    $selected = 1;
                } else {
                    $selected = (int)$value;
                }
                $option = new StdClass();
                $key = substr($key, 19);
                $field = new EverpsorderoptionsField(
                    (int)$key,
                    (int)Context::getContext()->shop->id,
                    (int)Context::getContext()->language->id
                );
                if ((bool)$field->manage_quantity) {
                    if ($params['newOrderStatus']->id == $cancelled) {
                        $field->quantity = (int)$field->quantity + (int)$selected;
                    } else {
                        $field->quantity = (int)$field->quantity - (int)$selected;
                        if ($field->quantity < 0) {
                            $field->quantity = 0;
                        }
                    }
                    $field->save();
                }
                $option->name = $field->field_title;
                if ($field->type == 'select' || $field->type == 'radio' || $field->type == 'checkbox') {
                    $fieldValue = new EverpsorderoptionsOption(
                        (int)$value,
                        (int)Context::getContext()->shop->id,
                        (int)Context::getContext()->language->id
                    );
                    if ((bool)$fieldValue->manage_quantity) {
                        if ($params['newOrderStatus']->id == $validated) {
                            $fieldValue->quantity = (int)$fieldValue->quantity - (int)$selected;
                        } else {
                            $fieldValue->quantity = (int)$fieldValue->quantity + (int)$selected;
                            if ($fieldValue->quantity < 0) {
                                $fieldValue->quantity = 0;
                                $fieldValue->active = 0;
                            }
                        }
                        $fieldValue->save();
                    }
                    $option->value = $fieldValue->option_title;
                } else {
                    $option->value = $value;
                }
                $options[] = $option;
            }
        }
    }

    public function getStepFormGroupTypes()
    {
        $formGroupTypes = array(
            array(
                'id_form_type' => 1,
                'name' => $this->l('text'),
                'type' => 'text',
                'validate' => 'isGenericName',
            ),
            array(
                'id_form_type' => 2,
                'name' => $this->l('textarea'),
                'type' => 'textarea',
                'validate' => 'isCleanHtml',
            ),
            array(
                'id_form_type' => 3,
                'name' => $this->l('select'),
                'type' => 'select',
                'validate' => 'isUnsignedInt',
            ),
            array(
                'id_form_type' => 4,
                'name' => $this->l('checkbox'),
                'type' => 'checkbox',
                'validate' => 'isUnsignedInt',
            ),
            array(
                'id_form_type' => 5,
                'name' => $this->l('radio'),
                'type' => 'radio',
                'validate' => 'isUnsignedInt',
            ),
            array(
                'id_form_type' => 6,
                'name' => $this->l('number'),
                'type' => 'number',
                'validate' => 'isUnsignedInt',
            ),
            array(
                'id_form_type' => 7,
                'name' => $this->l('email'),
                'type' => 'email',
                'validate' => 'isEmail',
            ),
            array(
                'id_form_type' => 8,
                'name' => $this->l('tel'),
                'type' => 'tel',
                'validate' => 'isPhoneNumber',
            ),
            array(
                'id_form_type' => 9,
                'name' => $this->l('password'),
                'type' => 'password',
                'validate' => 'isPasswd',
            ),
            array(
                'id_form_type' => 10,
                'name' => $this->l('url'),
                'type' => 'url',
                'validate' => 'isUrl',
            ),
            array(
                'id_form_type' => 11,
                'name' => $this->l('date'),
                'type' => 'date',
                'validate' => 'isDate',
            ),
            array(
                'id_form_type' => 12,
                'name' => $this->l('time'),
                'type' => 'time',
                'validate' => 'isDate',
            ),
            array(
                'id_form_type' => 13,
                'name' => $this->l('datetime'),
                'type' => 'datetime',
                'validate' => 'isDate',
            ),
        );
        return $formGroupTypes;
    }

    public function sessionDataStartsWith($haystack, $needle)
    {
        $needle = $needle.'_';
        $length = Tools::strlen($needle);

        if (Tools::substr($haystack, 0, $length) === $needle) {
            return true;
        } else {
            return false;
        }
    }
}
