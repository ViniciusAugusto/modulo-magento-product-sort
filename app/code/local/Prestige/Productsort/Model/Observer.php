<?php
/**
 * @category   Prestige
 * @package    Prestige_Productsort
 * @author     vinicius@prestige.com.br
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Prestige_Productsort_Model_Observer
{
 
    public function catalogProductCollectionLoadBefore(Varien_Event_Observer $observer)
    {
        $collection = $observer->getCollection();
        $collection->getSelect()->joinLeft(
            array('_inventory_table'=>$collection->getTable('cataloginventory/stock_item')),
            "_inventory_table.product_id = e.entity_id",
            array('is_in_stock', 'manage_stock')
        );
        $collection->addExpressionAttributeToSelect(
            'on_top',
            '(CASE WHEN (((_inventory_table.use_config_manage_stock = 1) AND (_inventory_table.is_in_stock = 1)) OR  ((_inventory_table.use_config_manage_stock = 0) AND (1 - _inventory_table.manage_stock + _inventory_table.is_in_stock >= 1))) THEN 1 ELSE 0 END)',
            array()
        );
        $collection->getSelect()->order('on_top DESC');
        // Make sure on_top is the first order directive
        $order = $collection->getSelect()->getPart('order');
        array_unshift($order, array_pop($order));
        $collection->getSelect()->setPart('order', $order);
    }
}