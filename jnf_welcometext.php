<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Jnf_Welcometext extends Module
{
    public function __construct()
    {
        $this->name = 'jnf_welcometext';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'JnfDev';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Welcome Text');
        $this->description = $this->l('This plugins show two customizable messages at the Front-end. This plugin is an "admission test" for Interfell.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }
    
    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayHome') &&
            $this->registerHook('displayFooterBefore');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /** Hooks */

    public function hookDisplayHome($params)
    {
        if ( isset( $params['hook'] ) && $params['hook'] === 'displayFooterBefore' ) {
            $welcomeText = Configuration::get( 'JNF_WELCOMETEXT_FOOTER' );
        } else {
            $welcomeText = Configuration::get( 'JNF_WELCOMETEXT_HOME' );
        }

        $this->context->smarty->assign([
            'welcome_text' => $welcomeText,
        ]);

        return $this->display(__FILE__, 'welcome-text.tpl');
    }

    public function hookDisplayFooterBefore($params)
    {
        $params['hook'] = 'displayFooterBefore';

        return $this->hookDisplayHome( $params );
    }

    /** Admin Configuration Page */

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $welcomeTextHome   = strval(Tools::getValue('JNF_WELCOMETEXT_HOME'));
            $welcomeTextFooter = strval(Tools::getValue('JNF_WELCOMETEXT_FOOTER'));

            Configuration::updateValue('JNF_WELCOMETEXT_HOME', $welcomeTextHome);
            Configuration::updateValue('JNF_WELCOMETEXT_FOOTER', $welcomeTextFooter);

            $output .= $this->displayConfirmation($this->l('Settings updated'));

        }

        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        // Get default language
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Welcome Texts'),
            ],
            'input' => [
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Welcome Text Home'),
                    'name'  => 'JNF_WELCOMETEXT_HOME',
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Welcome Text Footer'),
                    'name'  => 'JNF_WELCOMETEXT_FOOTER',
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];

        // Load current value0
        $helper->fields_value['JNF_WELCOMETEXT_HOME']   = Tools::getValue('JNF_WELCOMETEXT_HOME', Configuration::get('JNF_WELCOMETEXT_HOME'));
        $helper->fields_value['JNF_WELCOMETEXT_FOOTER'] = Tools::getValue('JNF_WELCOMETEXT_FOOTER', Configuration::get('JNF_WELCOMETEXT_FOOTER'));


        return $helper->generateForm($fieldsForm);
    }

}