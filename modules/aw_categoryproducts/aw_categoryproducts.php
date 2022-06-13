<?php
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Aw_CategoryProducts extends Module implements WidgetInterface
{
    private $templateFile;

    public function __construct()
    {
        $this->name = 'aw_categoryproducts';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Adrian Wądrzyk';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => '1.7.99',
        ];
        $this->bootstrap = true;
        $this->table_name = 'aw_categoryproducts';
        parent::__construct();

        $this->displayName = $this->l('Home page category products');
        $this->description = $this->l('Display products from category on main page.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->templateFile = 'module:aw_categoryproducts/views/templates/hook/aw_categoryproducts_home.tpl';
    }

    public function install()
    {
        return (parent::install()
            && $this->registerHook('displayHome')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->createTable()); 
    }

    public function uninstall()
    {
        return (parent::uninstall()
                && $this->unregisterHook("displayHome")
                && $this->unregisterHook("actionFrontControllerSetMedia")
            );
    }

    private function createTable() {
      return (bool)Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'aw_categoryproducts`(
            `id_category` int(11) NOT NULL PRIMARY KEY
          )  ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    public function getContent() {
        $output = '';

        if(Tools::isSubmit("addCategoryProducts") && Tools::getIsset("id_category")) {
            $this->updateDataBase(['id_category' => (int) Tools::getValue("id_category")]);
        }

        if(Tools::isSubmit("deletemodule") && Tools::getIsset("id_category")) {
            $this->deleteCategoryProducts((int)Tools::getValue("id_category"));
        }
        
        return $output .= $this->renderForm().$this->renderSelectetCategoryList();
    }

    private function renderForm() {
        $categories = $this->getCategories($this->context->language->id);

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Select category', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l("Choose category to display products on main page:"),
                        'name' => 'id_category',
                        'multiple' => false,
                        'options' => array(
                            'query' => $categories,
                            'id' => 'id_category',
                            'name' => 'name',
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Add', array(), 'Admin.Actions'),
                )
            )
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = true;
        $helper->table = $this->table_name;
        $helper->default_form_language = $lang->id;
        $helper->languages = $this->context->controller->getLanguages();
        $helper->module = $this;
        $helper->identifier = 'id_category';
        $helper->submit_action = 'addCategoryProducts';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->fields_value = ['id_category' => null];
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false, [], [
            'configure' => $this->name,
            'view' => 'aw_categoryproducts',
        ]);

        return $helper->generateForm(array($fields_form));
    }

    private function renderSelectetCategoryList()
    {
        $fields_list = array(
            'id_category' => array(
                'title' => $this->l("Category name"),
                'type' => 'text',
                'orderby' => false,
                'search' => false,
                'callback_object' => $this,
                'callback' => 'getCategoryName'
            )
        );
        
        $selected_category = $this->getSelectedCategory();
        $helper = new HelperList();
        $helper->show_toolbar = false;
        $helper->simple_header = true;
        $helper->shopLinkType = '';
        $helper->actions = ['delete'];
        $helper->module = $this;
        $helper->orderBy = 'id_category';
        $helper->position_identifier = 'id_category';
        $helper->identifier = 'id_category';
        $helper->title = $this->l('Edit category list');
        $helper->listTotal = count($selected_category);
        $helper->table = $this->table;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false, [], [
            'configure' => $this->name,
            'view' => 'aw_categoryproducts',
        ]);

        return $helper->generateList($selected_category, $fields_list);
    }

    public function getCategoryName($id_category)
    {
        $category = new Category($id_category, $this->context->language->id);
        return $category->name;
    }

    private function getSelectedCategory() {
        return Db::getInstance()->executes('SELECT * FROM `' . _DB_PREFIX_ . $this->table_name .'`');
    }

    private function updateDataBase($id_category) { 
        return Db::getInstance()->insert( $this->table_name, $id_category, false, true, Db::INSERT_IGNORE);
    }

    private function deleteCategoryProducts($id_category) {
        return Db::getInstance()->delete( $this->table_name, "id_category = $id_category");
    }

    private function getCategories($idLang = false, $active = true, $order = true, $sqlFilter = '', $orderBy = '', $limit = '')
    {
        return Db::getInstance()->executeS(
            'SELECT c.`id_category`, cl.`name`
			FROM `' . _DB_PREFIX_ . 'category` c
			' . Shop::addSqlAssociation('category', 'c') . '
			LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . '
			WHERE 1 ' . $sqlFilter . ' ' . ($idLang ? 'AND `id_lang` = ' . (int) $idLang : '') . '
			AND c.`id_category` > 1
			' . ($active ? 'AND `active` = 1' : '') . '
			' . (!$idLang ? 'GROUP BY c.id_category' : '') . '
			' . ($orderBy != '' ? $orderBy : 'ORDER BY c.`level_depth` ASC, category_shop.`position` ASC') . '
			' . ($limit != '' ? $limit : '')
        );
    }

    public function renderWidget($hookName, array $configuration)  {
        $template_variables = $this->getWidgetVariables($hookName, $configuration);

        $this->context->smarty->assign("blocks_variables", $template_variables);

        return $this->fetch($this->templateFile);
    }
    
    public function getWidgetVariables($hookName, array $configuration) {
        $categoriesIds = $this->getSelectedCategory();

        if(!empty($categoriesIds)){
            $templateVariables = [];

            $assembler = new ProductAssembler($this->context);
            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );

            foreach($categoriesIds as $categoryId) {
                $products = $this->getProducts($this->context->language->id, 0, 10, 'position', 'ASC', reset($categoryId), true, null, true);
                $category_info = Category::getCategoryInformation($categoryId, $this->context->language->id);
                    if (!empty($products)) {
                        $productsForTemplate = [];
                        $presentationSettings->showPrices = true;
            
                        if (is_array($products)) {
                            foreach ($products as $productId) {
                                $productsForTemplate[] = $presenter->present(
                                    $presentationSettings,
                                    $assembler->assembleProduct(['id_product' => $productId['id_product']]),
                                    $this->context->language
                                );
                            }
                        }
                    
                        $templateVariables[] = [
                            'category_info' => reset($category_info), 
                            'products' => $productsForTemplate]; 
                    }
            }       

            return $templateVariables;
        }
    }

    public function hookDisplayHome($params) {
       return $this->renderWidget("displayHome", $params);
    }

    public function hookActionFrontControllerSetMedia() {
        $this->context->controller->registerStylesheet('aw_categoryproducts-style','modules/'.$this->name.'/views/css/style.css',['media' => 'css','priority' => 999]);
        $this->context->controller->registerStylesheet('slick-style','modules/'.$this->name.'/views/js/slick/slick.css',['media' => 'css','priority' => 100]);
        $this->context->controller->registerJavascript('slick-js', 'modules/'.$this->name.'/views/js/slick/slick.min.js', ['position' => 'bottom', 'priority' => 0]);
        $this->context->controller->registerJavascript('aw_categoryproducts-js', 'modules/'.$this->name.'/views/js/module.js', ['position' => 'bottom', 'priority' => 0]);
    }


        /**
     * Get all available products.
     *
     * @param int $id_lang Language identifier
     * @param int $start Start number
     * @param int $limit Number of products to return
     * @param string $order_by Field for ordering
     * @param string $order_way Way for ordering (ASC or DESC)
     * @param int|false $id_category Category identifier
     * @param bool $only_active
     * @param Context|null $context
     * @param bool $only_in_stock
     *
     * @return array Products details
     */
    private function getProducts(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $id_category = false,
        $only_active = false,
        Context $context = null,
        $only_in_stock = true
    ) {
        if (!$context) {
            $context = Context::getContext();
        }

        $front = true;
        if (!in_array($context->controller->controller_type, ['front', 'modulefront'])) {
            $front = false;
        }

        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            die(Tools::displayError());
        }
        if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add' || $order_by == 'date_upd') {
            $order_by_prefix = 'p';
        } elseif ($order_by == 'name') {
            $order_by_prefix = 'pl';
        } elseif ($order_by == 'position') {
            $order_by_prefix = 'c';
        }

        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by_prefix = $order_by[0];
            $order_by = $order_by[1];
        }
        
        $sql = 'SELECT p.*, product_shop.*, pl.* , m.`name` AS manufacturer_name, s.`name` AS supplier_name
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
                LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON (p.`id_product` = sa.`id_product` AND p.`cache_default_attribute` = sa.`id_product_attribute`)
                LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`)' .
                ($id_category ? 'LEFT JOIN `' . _DB_PREFIX_ . 'category_product` c ON (c.`id_product` = p.`id_product`)' : '') . '
                WHERE pl.`id_lang` = ' . (int) $id_lang .
                    ($id_category ? ' AND c.`id_category` = ' . (int) $id_category : '') .
                    ($only_in_stock ? ' AND sa.`quantity` > 0': '') .
                    ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') .
                    ($only_active ? ' AND product_shop.`active` = 1' : '') . '
                ORDER BY ' . (isset($order_by_prefix) ? pSQL($order_by_prefix) . '.' : '') . '`' . pSQL($order_by) . '` ' . pSQL($order_way) .
                ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');
        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if ($order_by == 'price') {
            Tools::orderbyPrice($rq, $order_way);
        }
        
        foreach ($rq as &$row) {
            $row = Product::getTaxesInformations($row);
        }

        return $rq;
    }
}
