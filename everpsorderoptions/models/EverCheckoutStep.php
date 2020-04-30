<?php

use Symfony\Component\Translation\TranslatorInterface;

class EverCheckoutStep extends AbstractCheckoutStep
{
    protected $module;
    protected $everdata;

    public function __construct(
        Context $context,
        TranslatorInterface $translator,
        Everpsorderoptions $module
    )
    {
        parent::__construct($context, $translator);
        $this->context = $context;
        $this->translator = $translator;
        $this->module = $module;
        $title = Configuration::get(
            'EVERPSOPTIONS_TITLE',
            (int)Context::getContext()->language->id
        );
        $this->setTitle(
            $title
        );
    }


    /**
     * Récupération des données à persister
     *
     * @return array
     */
    public function getDataToPersist()
    {
        return array(
            'everdata' => $this->everdata,
            'evermessage' => Configuration::get(
                'EVERPSOPTIONS_MSG',
                (int)Context::getContext()->language->id
            )
        );
    }

    /**
     * Restoration des données persistées
     *
     * @param array $data
     * @return $this|AbstractCheckoutStep
     */
    public function restorePersistedData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->everdata = $data['everdata'];
        }
            // die(var_dump($data));

        return $this;
    }

    /**
     * Traitement de la requête ( ie = Variables Posts du checkout )
     * @param array $requestParameters
     * @return $this
     */
    public function handleRequest(array $requestParameters = array())
    {
        if (isset($requestParameters['submitCustomStep'])) {
            foreach ($requestParameters as $key => $value) {
                if ($this->orderFormStartsWith($key, 'everpsorderoptions')) {
                    $this->everdata[$key] = $value;
                }
            }
            $this->setComplete(true);
            if (version_compare(_PS_VERSION_, '1.7.6') > 0) {
                $this->setNextStepAsCurrent();
            } else {
                $this->setCurrent(false);
            }
        }

        return $this;
    }

    /**
     * Affichage de la step
     *
     * @param array $extraParams
     * @return string
     */
    public function render(array $extraParams = [])
    {
        $fields = EverpsorderoptionsField::getOptionsFields(
            (int)Context::getContext()->shop->id,
            (int)Context::getContext()->language->id,
            false,
            false
        );
        $options = EverpsorderoptionsOption::getFullOptions(
            (int)Context::getContext()->shop->id,
            (int)Context::getContext()->language->id,
            false
        );
        foreach ($options as $key => $value) {
            if ((bool)$value['manage_quantity']
                && (int)$value['quantity'] <= 0
            ) {
                unset($options[$key]);
            }
        }
        foreach ($fields as $key => $value) {
            if ($value['type'] == 'select'
                || $value['type'] == 'radio'
                || $value['type'] == 'checkbox'
            ) {
                $hasOptions = EverpsorderoptionsOption::fieldHasOptions(
                    (int)$value['id_everpsorderoptions_field'],
                    (int)Context::getContext()->shop->id
                );
                if ($hasOptions <= 0) {
                    unset($fields[$key]);
                }
            }
        }
        $defaultParams = array(
            'identifier' => 'everpsorderoptions',
            'position' => 3,
            'title' => $this->getTitle(),
            'step_is_complete' => (int)$this->isComplete(),
            'step_is_reachable' => (int)$this->isReachable(),
            'step_is_current' => (int)$this->isCurrent(),
            'fields' => $fields,
            'options' => $options,
            'everdata' => $this->everdata,
            'evermessage' => Configuration::get(
                'EVERPSOPTIONS_MSG',
                (int)Context::getContext()->language->id
            )
        );

        $this->context->smarty->assign($defaultParams);
        return $this->module->display(
            _PS_MODULE_DIR_ . $this->module->name,
            'views/templates/hook/everCheckoutStep.tpl'
        );
    }

    public function orderFormStartsWith($haystack, $needle)
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
