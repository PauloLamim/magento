<?php
/**
 * Created by PhpStorm.
 * User: paulo.lamim
 * Date: 01/08/2017
 * Time: 12:28
 */

$newlinefeed = "<br />";

//if (php_sapi_name() == "cli") { // Only want to be able to run this from command line

include_once 'app/Mage.php';

Mage::app();

$_productCollection = Mage::getModel('catalog/product')
    ->setStoreId(Mage::app()->getStore()->getId())
    ->getCollection()
    ->addAttributeToSort('created_at', 'DESC')
    ->addAttributeToSelect(array('sku','name','type','status'))
    ->addFieldToFilter('type_id', array('eq' => 'bundle'))
    ->setPageSize(50)
    ->load();

//Setting up CSV
$list = array(array());
$list[0][0] = 'Bundle Name';
$list[0][1] = 'SKU';
$list[0][2] = 'Item';
//Initialize rows
$r = 1; //starts at 1 because of header

//counter for first item
$counter = 0;

$fp = fopen('test2.csv', 'w');

foreach ($_productCollection as $_product){
    $_itemsCollection = $_product->getTypeInstance(true)->getSelectionsCollection($_product->getTypeInstance(true)->getOptionsIds($_product), $_product);
    if ($_product->getData('status') == 1) {
        $itemIds = [];
        foreach ($_itemsCollection as $_item) {
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_item->getId());
            if ((int)$stock->getQty() == 0) {
                $itemIds[] =  "{$_item->getSku()}: {$_item->getName()}";
            }
        }
        if ($itemIds) {
            //print "{$_product->getName()}, SKU: {$_product->getSku()}: Has the following items out of stock:{$newlinefeed}";

            //Fill Product Name Column
            $list[$r][0] = $_product->getName();
            //Fill SKU Collumn
            $list[$r][1] = $_product->getSku();

            foreach ($itemIds as $k => $v) {
                //print $v . $newlinefeed;
                //Fill item collumn
                //$list[$r][2] .= $v . ' ';
                //For 1 and 1st item else multiple items
                if($counter==0)
                {
                    $list[$r][2] = $v;
                    $counter++;
                }
                else{
                    $list[$r][0] = "";
                    $list[$r][1] = "";
                    $list[$r][2] = $v;
                }
                $r++;
            }
            //print "-----------------{$newlinefeed}{$newlinefeed}";
            $counter = 0;
        }
        $r++;
    }
}

//Send matrix to csv

foreach($list as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);

echo 'Done </br>';
//To be completed
print "Sending email to Danny...";

//}