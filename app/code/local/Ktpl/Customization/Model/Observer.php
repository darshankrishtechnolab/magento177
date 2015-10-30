<?php

class Ktpl_Customization_Model_Observer extends Varien_Object {

    public function salesQuoteAddItem($observer) {
        $item = $observer->getEvent()->getQuoteItem();
        $params = new Varien_Object();
        $params->setBuyRequest($item->getBuyRequest());
        $product = $item->getProduct();
        $additionalOption = array();
        $productOptionArray = array();
        $itemFlagVar = false;
        if (Mage::app()->getRequest()->getParam('document_id') && Mage::app()->getRequest()->getParam('filename')) {
            $documentID = Mage::app()->getRequest()->getParam('document_id');
            $fileName = Mage::app()->getRequest()->getParam('filename');

            $additionalOption = array(array(
                    'label' => 'document_id',
                    'option_value' => $documentID,
                    'value' => $documentID,
                ),
                array(
                    'label' => 'filename',
                    'option_value' => $fileName,
                    'value' => $fileName,
            ));
            $itemFlagVar = true;
        }

        if (Mage::app()->getRequest()->getParam('pricegroup') && Mage::app()->getRequest()->getParam('variety')) {
            $price = 0;
            $paramPriceGroup = Mage::app()->getRequest()->getParam('pricegroup');
            $paramVariety = Mage::app()->getRequest()->getParam('variety');
            $paramOptions = Mage::app()->getRequest()->getParam('options');

            $pricegroup = Mage::getModel('pricegroups/pricegroup')->load($paramPriceGroup);
            $variety = Mage::getModel('pricegroups/variety')->load($paramVariety);
            $productOptions = array();

            foreach ($paramOptions[$paramPriceGroup][$paramVariety] as $oId => $optionId) {
                $option = Mage::getModel('pricegroups/option')->load($oId);
                $title = '';
                $productOptions[$option->getId()]['title'] = $option->getTitle();
                foreach ($option->getOptions() as $opt) {

                    if ($opt['ID'] == $optionId) {
                        $title = isset($opt['Titel']) ? $opt['Titel'] : '';
                        if (!isset($opt['Titel'])) {
                            $second = array_values($opt);
                            $title = $second[1];
                        }
                        $price += floatval($opt['Preis']);
                    }
                }
                $productOptions[$option->getId()]['options'][$opt['ID']] = $title;
            }
            $productOptionArray = array();
            foreach ($productOptions as $id => $value) {
                $values = array_values($value['options']);
                $productOptionArray[$id] = array(
                    'label' => $value['title'],
                    'title' => $value['title'],
                    'option_value' => $values[0],
                    'value' => $values[0],
                    'options' => $value['options'],
                );
            }
            $item->getProduct()->setPricegroupView($pricegroup->getTitle());
            $item->getProduct()->setVarietyView($variety->getTitle());
            $item->getProduct()->setIsSuperMode(true);
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->setPricegroupView($item->getPricegroupView());
            $item->setVarietyView($item->getVarietyView());
            $item->setOptionsView($item->getOptionsView());
            $itemFlagVar = true;
        }
        $mergeArray = array_merge($additionalOption, $productOptionArray);
        $option = $item->getOptionByCode('additional_options');

        if ($option == null) {
            $value = $mergeArray;
            $value = serialize($value);
            $item->addOption(array('code' => 'additional_options', 'product_id' => $item->getProductId(), 'value' => $value));
        } else {
            $additional = unserialize($option->getValue());
            $additional = array_merge($additional, $mergeArray);
            $option->setValue(serialize($additional));
        }
        if (Mage::app()->getRequest()->getParam('document_id')) {
            $item->setDocumentId(Mage::app()->getRequest()->getParam('document_id'));
        }

        if (Mage::app()->getRequest()->getParam('uid')) {
            $item->setUid(Mage::app()->getRequest()->getParam('uid'));
        }
        if (Mage::app()->getRequest()->getParam('job_id')) {
            $item->setJob_id(Mage::app()->getRequest()->getParam('job_id'));
        }
        if (Mage::app()->getRequest()->getParam('obility_order_id')) {
            $item->setObility_order_id(Mage::app()->getRequest()->getParam('obility_order_id'));
        }

        if (Mage::app()->getRequest()->getParam('filename')) {
            $item->setFilename(Mage::app()->getRequest()->getParam('filename'));
        }
        if($itemFlagVar){
            $item->save();
        }
        
    }

}
