<?php

/*
 * Stworzono przez SEIGI http://seigi.eu/
 * Ten moduł jest jedynie przykładem, jakie są możliwości samodzielnego rozszerzania importu produktów za pomocą modułu SEIGI MobyDick
 * Utworzono  : 2022-09-27
 * Author     : SEIGI - Grzegorz Zawadzki
 */

if (!defined('_PS_VERSION_'))
    exit;

class seigimobycustom extends Module {

    protected $_html = '';
    protected $_postErrors = array();


    public function __construct() {
        $this->name = 'seigimobycustom';
        $this->version = '1.0.0';
        $this->tab = 'frontend';
        $this->bootstrap = true;

        $this->author = 'SEIGI Grzegorz Zawadzki';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.7');

        parent::__construct();

        $this->displayName = $this->l('MobyDick Custom');
        $this->description = $this->l('Moduł zawiera dodatkowe funkcje, które pozwolą wpłynąć na końcowy efekt produktu');
    }

    public function install() {
        if (!parent::install()
            || !$this->registerHook('mobyDickProductBeforeSave')
            || !$this->registerHook('mobyDickProductInit')
            || !$this->registerHook('mobyDickCombinationBeforeSave')
            || !Configuration::updateValue('MOBY_DICK_CUST_MULTI', '0.95')
        )
            return false;
        return true;
        return parent::install();
    }

    public function uninstall() {
        if (!Configuration::deleteByName('MOBY_DICK_CUST_MULTI')
                || !parent::uninstall())
            return false;
        return true;
    }

    public function getContent() {

        if(Tools::isSubmit('MOBY_DICK_CUST_MULTI')){
            Configuration::updateValue('MOBY_DICK_CUST_MULTI', Tools::getValue('MOBY_DICK_CUST_MULTI'));
        }
        $helper = new HelperForm();
        $helper->fields_value = [
            'MOBY_DICK_CUST_MULTI' => Configuration::get('MOBY_DICK_CUST_MULTI'),
        ];
        $form = [
            [
                'form' => [
                    'legend' => [
                        'title' => $this->l('SpectrumLed'),
                        'icon' => 'icon-cogs'
                    ],
                    'input' => [
                        [
                            'label' => 'Mnożnik Ceny',
                            'desc' => 'Tutaj możesz wpisać mnożnik ceny',
                            'type' => 'text',
                            'name' => 'MOBY_DICK_CUST_MULTI',
                        ],
                    ],
                    'submit' => [
                        'title' => $this->l('Save'),
                        'class' => 'btn btn-default pull-right'
                    ],
                ],
            ],
        ];
        return $helper->generateForm($form);
    }

    public function hookMobyDickProductBeforeSave($params){
        $product = $params['product'];
        $settings = $params['settings'];
        if(strtolower($params['wholesaler']) === 'spectrumled') {
            $multiplier = floatval(Configuration::get('MOBY_DICK_CUST_MULTI'));
            if($multiplier > 0) {
                $product->price = $product->price * $multiplier;
            }
            if($settings['UPDATE_DESC'])
                $product->description .= 'Ten Produkt został zaimportowany przez moduł <a href="https://seigi.eu/modul-prestashop/seigimobydick.html">MobyDick</a>';
        }
    }
    public function hookMobyDickCombinationBeforeSave($params){
        $product = $params['product'];
        $settings = $params['settings'];
        $combination = $params['combination'];

        if(strtolower($params['wholesaler']) === 'spectrumled') {
            $combination->weight = 1.5; // Ustawia wagę kombinacji na 1.5
        }
    }
    public function hookMobydickProductInit($params){
        $raw = $params['raw'];
        $settings = $params['settings'];
        if(strtolower($params['wholesaler']) === 'spectrumled') {
            if('5904433108874' === (string)$raw->Description->EANCode){
                throw new skipException('(Module::'.$this->name.') Pomijam produkt bo EAN 5904433108874 mi się nie podoba');

            }
        }
    }
}
