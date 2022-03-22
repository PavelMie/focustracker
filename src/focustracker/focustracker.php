<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class focustracker extends Module
{

    public function __construct()
    {
        $this->config_fields = [
            'FOCUSTRACKER_POPUP_TEXT',
            'FOCUSTRACKER_POPUP_COLOR',
            'FOCUSTRACKER_PAGE_index',
            'FOCUSTRACKER_PAGE_category',
            'FOCUSTRACKER_PAGE_product',
            'FOCUSTRACKER_CATEGORY_ID',
            'FOCUSTRACKER_PRODUCT_ID',
            'FOCUSTRACKER_DATE_FROM',
            'FOCUSTRACKER_DATE_TO',
            'FOCUSTRACKER_ALL_TIME',
            'FOCUSTRACKER_IMAGE_FILE',
            'FOCUSTRACKER_IMAGE',
            'FOCUSTRACKER_BREAK'
        ];

        $this->name = 'focustracker';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'PavelMie';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('focustracker');
        $this->description = $this->l('Popup display module.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayBackOfficeHeader')
            || !$this->registerHook('displayHeader')
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') != 'AdminModules' && Tools::getValue('configure') != $this->name)
            return;
        $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/showHide.js');
    }

    public function hookDisplayHeader($params)
    {
        //get current page
        $page = Tools::getValue('controller');

        //check module config display page
        $show = Tools::getValue('FOCUSTRACKER_PAGE_'.$page, Configuration::get('FOCUSTRACKER_PAGE_'.$page));

        if($show != 1)
            return;

        //check module config category id
        if($page == 'category')
            if(Tools::getValue('id_category') != Tools::getValue('FOCUSTRACKER_CATEGORY_ID', Configuration::get('FOCUSTRACKER_CATEGORY_ID')))
                return;

        //check module config product id
        if($page == 'product')
            if(Tools::getValue('id_product') != Tools::getValue('FOCUSTRACKER_PRODUCT_ID', Configuration::get('FOCUSTRACKER_PRODUCT_ID')))
                return;

        $this->displayPopup();
    }

    private function displayPopup(){

        //Date validation
        $show_all_time = Tools::getValue('FOCUSTRACKER_ALL_TIME', Configuration::get('FOCUSTRACKER_ALL_TIME'));
        if(!$show_all_time || empty($show_all_time)){

            $date_from = Tools::getValue('FOCUSTRACKER_DATE_FROM', Configuration::get('FOCUSTRACKER_DATE_FROM'));
            $date_to = Tools::getValue('FOCUSTRACKER_DATE_TO', Configuration::get('FOCUSTRACKER_DATE_TO'));
            $date_now = date('Y-m-d');

            if(empty($date_from) || empty($date_to))
                return;

            if(($date_now > $date_to) || ($date_now < $date_from))
                return;
        }

        //background validation
        $bg_color           = Tools::getValue('FOCUSTRACKER_POPUP_COLOR', Configuration::get('FOCUSTRACKER_POPUP_COLOR'));
        $background_image   = Tools::getValue('FOCUSTRACKER_IMAGE', Configuration::get('FOCUSTRACKER_IMAGE'));
        $link = new Link;
        $imagePath = $link->getBaseLink().'upload/'.$background_image;

        $background = empty($background_image) || !file_exists($imagePath) ? "background: $bg_color;" : "background-image: url('$imagePath');";

        //get rest of the data
        $popup_text = Tools::getValue('FOCUSTRACKER_POPUP_TEXT', Configuration::get('FOCUSTRACKER_POPUP_TEXT'));
        $break      = Tools::getValue('FOCUSTRACKER_BREAK', Configuration::get('FOCUSTRACKER_BREAK'));

        //smarty assign
        $smarty = new Smarty;
        $smarty->assign('break', $break);
        $smarty->assign('background', $background);
        $smarty->assign('popup_text', $popup_text);

        $html = $smarty->fetch(_PS_MODULE_DIR_.$this->name.'/templates/'.'popup.tpl');

        echo($html);
    }

    public function getContent()
    {
        $output = '';
        //if config submit
        if (Tools::isSubmit('submit' . $this->name)) {

            $output = $this->displayConfirmation($this->l('Configuration saved'));

            //todo showing & deleting image in one field instead of saving file name in separate field
            $newFile = '';
            if (!empty($_FILES['FOCUSTRACKER_IMAGE_FILE']['name'])){
                $output = $this->uploadImage('FOCUSTRACKER_IMAGE_FILE');
                //name to save in new config field
                $newFile = (string) Tools::getValue('FOCUSTRACKER_IMAGE_FILE');
            }

            //save module config fields
            foreach ($this->config_fields as $field){

                //if new file is uploaded, save in file name field its name rather than old value
                if($field == 'FOCUSTRACKER_IMAGE' && !empty($newFile)){
                    Configuration::updateValue($field, $newFile,true);
                    continue;
                }

                //validating data from request
                $configValue = (string) Tools::getValue($field);
                $condition = Validate::isCleanHtml($configValue);

                if(!empty($configValue))
                    if ($field == 'FOCUSTRACKER_CATEGORY_ID'
                        || $field == 'FOCUSTRACKER_PRODUCT_ID'
                        || $field == 'FOCUSTRACKER_BREAK')
                        $condition = Validate::isInt($configValue);

                if (!$condition){
                    $output = $this->displayError($this->l('Invalid Configuration value'));
                    break;
                }

                Configuration::updateValue($field, $configValue,true);

            }
        }

        return $output . $this->displayForm();
    }

    private function uploadImage($tmp){

        $target_dir = _PS_UPLOAD_DIR_;
        $target_file = $target_dir . basename($_FILES[$tmp]["name"]);
        $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"]))
            if(getimagesize($_FILES[$tmp]["tmp_name"]) === false)
                $error = "File is not an image.";

        // Check if file already exists
        if (file_exists($target_file))
            unlink($target_file);


        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg")
            $error = "Invalid file format.";

        // Check if $uploadOk is set to 0 by an error
        if (isset($error))
            return $this->displayError($this->l($error));

        //moves the file
        if (!move_uploaded_file($_FILES[$tmp]["tmp_name"], $target_file))
            return $this->displayError($this->l('Invalid File'));

        return $this->displayConfirmation($this->l('Zapisano zmiany'));
    }


    public function displayForm()
    {
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Ustawienia'),
                ],
                'input' => [
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Popup html content:'),
                        'name' => 'FOCUSTRACKER_POPUP_TEXT',
                    ],
                    [
                        'type' => 'checkbox',
                        'label' => $this->l('Popup display pages'),
                        'name' => 'FOCUSTRACKER_PAGE',
                        'values'  => [
                            'query' => [
                                [
                                    'id_option' => 'index',
                                    'name' => 'Home page',
                                    'val' => 1,
                                ],
                                [
                                    'id_option' => 'category',
                                    'name' => 'Category page',
                                    'val' => 1,
                                ],
                                [
                                    'id_option' => 'product',
                                    'name' => 'Product page',
                                    'val' => 1,
                                ],
                            ],
                            'id'    => 'id_option',
                            'name'  => 'name'
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Category ID:'),
                        'name' => 'FOCUSTRACKER_CATEGORY_ID',
                        'desc' => $this->l('To display popup on every category, empty this field.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Product ID:'),
                        'name' => 'FOCUSTRACKER_PRODUCT_ID',
                        'desc' => $this->l('To display popup on every product, empty this field.'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Active all the time:'),
                        'name' => 'FOCUSTRACKER_ALL_TIME',
                        'required' => false,
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ]
                    ],
                    [
                        'type' => 'date',
                        'label' => $this->l('Date from:'),
                        'name' => 'FOCUSTRACKER_DATE_FROM',
                    ],
                    [
                        'type' => 'date',
                        'label' => $this->l('Date to:'),
                        'name' => 'FOCUSTRACKER_DATE_TO',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Minutes of display break:'),
                        'name' => 'FOCUSTRACKER_BREAK',
                        'desc' => $this->l('Set 0 or empty this field to turn off the break.'),
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l('Popup background color:'),
                        'name' => 'FOCUSTRACKER_POPUP_COLOR',
                    ],
                    [
                        'type' => 'file',
                        'label' => $this->l('Background image (500x250):'),
                        'name' => 'FOCUSTRACKER_IMAGE_FILE',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l(''),
                        'name' => 'FOCUSTRACKER_IMAGE',
                        'desc' => $this->l('To change backgroung from image to color, delete image name from this field.'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        // Default language
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        // Load current value into the form
        foreach ($this->config_fields as $field){
            $helper->fields_value[$field] = Tools::getValue($field, Configuration::get($field));
        }

        //todo (from line 142) showing & deleting image in one field instead of saving file name in separate field
        //updating file name after sending new file
        $helper->fields_value['FOCUSTRACKER_IMAGE'] = empty($helper->fields_value['FOCUSTRACKER_IMAGE_FILE'])? $helper->fields_value['FOCUSTRACKER_IMAGE'] :$helper->fields_value['FOCUSTRACKER_IMAGE_FILE'];

        return $helper->generateForm([$form]);
    }
}
