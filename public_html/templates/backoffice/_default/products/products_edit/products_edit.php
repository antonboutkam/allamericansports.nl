<?php
class Products_edit
{
    function run($params)
    {
        if (!isset($params['_do'])) {
            $params['_do'] = null;
        }

        if (isset($params['id']) && is_numeric($params['id'])) {
            // "Voorraad real-time ophalen.";
            file_get_contents('https://backoffice.allamericansports.nl/stock?product_id=' . $params['id']);
        }

        $oExactApi = ExactHandleOath::handle($_SERVER['REQUEST_URI']);

        if (isset($_GET['sync_suppliers'])) {
            $oExactSupplier = new ExactSupplier($oExactApi, Cfg::get('EXACT_DIVISION'));
            $oExactSupplier->syncAllSuppliers();
        }

        ini_set('upload_max_filesize', '128M');
        $plugindir = dirname(__FILE__) . '/plugin';
        $aPlugins = BackofficePlugin::loadPlugins($plugindir);

        $params = BackofficePlugin::trigger('_doFirst', $aPlugins, $params);

        $params['sync_email'] = Webshop::getWebshopSetting('allamericansports.nl', 'cc_ordermailaddress', true);
        if ($params['_do'] == 'plan_sync') {
            if ($params['add_email']) {
                file_put_contents('./tmp/syncsuppliers', $params['sync_email']);
            } else {
                file_put_contents('./tmp/syncsuppliers', 'false');
            }
        }
        if (file_exists('./tmp/syncsuppliers')) {
            $params['supplier_sync_planned'] = true;
        }


        // Only allow module if active.
        $aAllModules = [
            'has_is_part',
            'product_options',
            'product_groups',
            'multi_img',
            'relate_products',
            '3d_img',
            'webshop_menu_editor',
            'module_metatags',
            'module_metakeywords',
            'module_washing',
            'module_barcodes',
            'exact_online'
        ];

        $params['modules'] = Cfg::areModulesActive($aAllModules);

        echo "Active modules"
        print_r($params['modules']);

        if ($params['_do'] == 'update_image') {
            if ($params['image_type'] == 'altimage') {
                $imgeData = file_get_contents($params['image']);
                file_put_contents('./' . $params['overwrite'], $imgeData);
            } else {
                Image::updateFromUrl($params['id'], $params['image']);
            }
            redirect('/products/edit.html?id=' . $params['id']);
        }
        // Check for duplicate Ean Code
        if ($params['_do'] == 'check_ean') {
            $out['duplicate_ean'] = ProductDao::eanExists($params['ean'], $params['id']);
            exit(json_encode($out));
        }


        $params['suppliers'] = RelationDao::getSuppliers();

        $params['btw'] = Cfg::getPref('btw');
        if ($params['_do'] == 'delete_grouplink')
            if ($params['table'] == 'product_part')
                ProductParts::delete($params['linkid']);
            else
                ProductOptions::delete($params['linkid']);

        if ($params['_do'] == 'add_color')
            ColorDao::addProductColor($params['id'], $params['color_id']);
        if ($params['_do'] == 'remove_color')
            ColorDao::removeColor($params['id'], $params['color_id']);

        if ($params['_do'] == 'add_usage')
            ProductUsageDao::addProductUsage($params['id'], $params['usage_id']);
        if ($params['_do'] == 'remove_usage')
            ProductUsageDao::removeUsage($params['id'], $params['usage_id']);

        if ($params['_do'] == 'remove_relation')
            ProductDao::unRelateProduct($params['id'], $params['related']);
        if ($params['_do'] == 'relate')
            ProductDao::relateProduct($params['id'], $params['addProduct']);
        if ($params['_do'] == 'is_part_of')
            ProductParts::add($params['id'], $params['addProduct']);
        if ($params['_do'] == 'has_part')
            ProductParts::add($params['addProduct'], $params['id']);
        if ($params['_do'] == 'add_option')
            ProductOptions::add($params['id'], $params['addProduct']);

        if ($params['_do'] == 'check_article_num') {
            $duplicate['duplicate'] = ProductDao::articleNumExists($params['articlenum']);
            exit(json_encode($duplicate));
        }

        if ($params['_do'] == 'duplicate_article') {
            $duplicate['duplicate'] = ProductDao::articleNumExists($params['articlenum']);
            if ($duplicate['duplicate']) {
                exit(json_encode($duplicate));
            }
            $params['copy_photos'] = ($params['copy_photos'] == 'true') ? 1 : 0;
            $duplicate['id'] = ProductDao::duplicate($params['articlenum'], $params['src_id'], $params['copy_photos']);

            if ($duplicate['id']) {
                TranslateWebshop::duplicate($params['src_id'], $duplicate['id']);
                ProductGroup::copyGroupMembership($params['src_id'], $duplicate['id']);
                Webshop::duplicate($params['src_id'], $duplicate['id']);
                if ($params['copy_photos']) {
                    Image::copyUploaded($params['src_id'], $duplicate['id']);
                    Image::copyProduct($params['src_id'], $duplicate['id']);
                }
            } else {
                exit('duplication error');
            }
            exit(json_encode($duplicate));
        }


        if ($params['_do'] == 'move_img')
            Image::moveExtraImage($params['imgid'], $params['direction']);
        if ($params['_do'] == 'delete_img')
            Image::removeExtraImage($params['imgid']);
        if ($params['_do'] == 'delete_img3d')
            Image3d::delete($params['id']);

        $params['rand'] = rand(0, 99999999999);

        $onlyStoreChanges = false;
        if ($params['_do'] == 'store') {   #pre_r($params);pre_r($_FILES);die();
            $id = null;

            if ($params['overwrite_group'] == 1) {
                $ids = ProductGroup::getAllSiblings($params['id']);
                $id = $params['id'];
                $onlyStoreChanges = true;
            } else if (isset($params['id'])) {
                $ids[] = $params['id'];
            } else {
                $ids[] = 'new';
            }

            $overwriteData = [];
            // Do not overwrite all the values that have remained the same.
            if ($onlyStoreChanges) {
                $currentProductCompl = ProductDao::getById($params['id']);

                foreach ($currentProductCompl as $key => $val) {
                    if (isset($params['product'][$key]) && $params['product'][$key] != $val) {
                        $overwriteData[$key] = $params['product'][$key];
                    }
                }


                if ($currentProductCompl['sale_price'] . '.' . $currentProductCompl['sale_price_ct'] == $params['product']['sale_price'])
                    unset($overwriteData['sale_price']);
            }

            foreach ($ids as $currId) {
                $tmpId = ProductEditor::store($params, $currId, ($currId != $params['id']), $aPlugins);
                if ($id == 'new' || $currId == 'new') {
                    $id = $params['id'] = $tmpId;
                }
            }
            if ($_FILES['extraimage']['name']) {
                Image::storeExtra($params['id']);
            }

            if (isset($params['stock']) && $params['stock']) {
                // directly add stock record
                $did = DeliveryDao::createBlank();
                DeliveryDao::addProductToStock($did, $id);
                $stockId = DeliveryDao::productInCurrentDelivery($did, $id);
                DeliveryDao::updateQuantity($params['stock'], $stockId);
                DeliveryDao::updateLocation($params['configuration'], $stockId);
                DeliveryDao::complete($did);
            }

            $oExactProduct = new ExactProduct($oExactApi, Cfg::get('EXACT_DIVISION'));
            $oExactProduct->upload($params['id']);
        }

        if ($params['id'] != 'new') {
            $params['from'] = 'product_part';
            $params['options'] = ProductParts::getAllPartsFor($params['id']);
            $params['partsoftable'] = parse('inc/partsoftable', $params);
            $params['from'] = 'partofproduct';
            $params['options'] = ProductParts::getAllPartsOf($params['id']);
            $params['haspartstable'] = parse('inc/partsoftable', $params);
            $params['from'] = 'product_option';
            $params['options'] = ProductOptions::getAllOptionsOf($params['id']);
            $params['optionoftable'] = parse('inc/partsoftable', $params);
            unset($params['options'], $params['type']);
            if (isset($params['ajax']) && $params['ajax'] == 1 && in_array($params['_do'], array('delete_grouplink', 'add_option', 'is_part_of'))) {
                exit(json_encode($params));
            }
        }
        $params['delivery_time_ranges'] = Db::instance()->getAll('delivery_time', 'label');
        $params['sizes'] = Db::instance()->getAll('size_table', 'title');
        $params['location'] = (isset($params['location']) && $params['location']) ? $params['location'] : User::getLocaton();
        $params['warehouse'] = WarehouseDao::getWarehouseConfiguration($params['location'], 'path, rack, shelf');

        if (isset($params['clearafterstore']) && $params['clearafterstore'] == 'true') {
            $params['id'] = 'new';
        }

        if ($params['_do'] == 'warehouseconfig') {
            exit(json_encode($params));
        }

        $params['warehouses'] = WarehouseDao::getLocations();

        $params = BarcodeDao::getProductProps($params, $params['id']);
        $defaults = ProductDao::getDefaults();

        $product = [];
        if ($params['id']) {
            $product = ProductDao::getById($params['id']);
        } else
            $params['id'] = 'new';

        #pre_r($product);

        foreach ($defaults as $field => $value)
            if ($product[$field] == '')
                $product[$field] = $value;

        if (is_array($product))
            $params = array_merge($params, $product);

        $params['product_types'] = ProductTypeDao::getAll();
        $params['product_sizes'] = TranslatedLookup::getDropDownTags('product_size');
        $params['product_sport'] = TranslatedLookup::getDropDownTags('product_sport');
        $params['colors'] = ColorDao::getUnused($params['id']);
        $params['product_colors'] = ColorDao::getProductColors($params['id']);
        $params['product_edit_colors'] = parse('inc/product_edit_colors', $params);
        $params['usages'] = ProductUsageDao::getUnused($params['id']);
        #pre_r($params['usages'] );
        $params['product_usages'] = ProductUsageDao::getProductUsages($params['id']);
        $params['product_edit_usage'] = parse('inc/product_edit_usage', $params);

        if (in_array($params['_do'], array('remove_color', 'add_color'))) {
            exit(json_encode(array('product_edit_colors' => $params['product_edit_colors'])));
        }

        if ($params['modules']['3d_img']) {
            $params['max_filesize'] = str_replace('M', '', ini_get('upload_max_filesize'));
            if ($params['max_filesize'] < 20)
                $params['low_max_filesize'] = true;
            Image3d::store($params['id']);
            $params['image_3d'] = Image3d::has3d($params['id']);
        }

        if ($params['modules']['has_is_part'] || $params['modules']['product_options'])
            $params['module_grouping'] = parse('inc/module_grouping', $params);

        if ($params['modules']['3d_img'])
            $params['module_3dimg'] = parse('inc/module_3dimg', $params);

        foreach (array('module_altproductnumbers', 'module_washing', 'module_metatags', 'module_barcodes', 'module_metatags', 'module_metakeywords') as $module) {
            if (isset($params['modules'][$module]) && $params['modules'][$module]) {
                $params[$module] = parse('inc/' . $module, $params);
            }
        }

        if ($params['modules']['relate_products']) {
            $params['related_products'] = ProductDao::getRelatedProducts($params['id']);
            $params['module_relate_products'] = parse('inc/module_relate_products', $params);
        }

        if ($params['modules']['product_groups'] && is_numeric($params['id'])) {
            $params['product_member_groups'] = ProductGroup::getProductMemberOf($params['id']);
        }

        $params['materials'] = Lookup::getItems('material');
        $params['brands'] = Lookup::getItems('brand');
        $params['ledgers'] = Lookup::getItems('ledger');

        if ($params['id'] != 'new')
            $params['webshop_root_menu_structures'] = Webshop::getProductMenuStructures(null, $params['id']);
        $params['module_prices'] = parse('inc/module_prices', $params);
        $params['required_translations'] = TranslateWebshop::getAllAvailableTranslations($params['id']);


echo __METHOD__;
echo "Plugins<br>";
echo "<pre>" . print_r($aPlugins, true) . "</pre>";


        if (!empty($aPlugins)) {
            foreach ($aPlugins as $oPlugin) {
                if($oPlugin instanceof Products_edit_abstract) {
                    $params = $oPlugin->addContents($params);
                }
            }
        }

        $params['content'] = parse('products_edit', $params, __FILE__);

        return $params;
    }

}
