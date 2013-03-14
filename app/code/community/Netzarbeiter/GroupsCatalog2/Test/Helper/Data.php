<?php

/**
 * @loadSharedFixture global.yaml
 */
class Netzarbeiter_GroupsCatalog2_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{
    protected $configSection = 'netzarbeiter_groupscatalog2';
    protected $configGroup = 'general';
    /** @var Netzarbeiter_GroupsCatalog2_Helper_Data */
    protected $helper;

    public static function setUpBeforeClass() {
        // Fix SET @SQL_MODE='NO_AUTO_VALUE_ON_ZERO' bugs from shared fixture files
        /** @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = Mage::getSingleton('core/resource')->getConnection('customer_write');
        $db->update(
            Mage::getSingleton('core/resource')->getTableName('customer/customer_group'),
            array('customer_group_id' => 0),
            "customer_group_code='NOT LOGGED IN'"
        );

        Mage::getModel('index/indexer')->getProcessByCode('groupscatalog2_product')->reindexEverything();

        //print_r($db->fetchAll('SELECT * FROM groupscatalog_product_idx'));
    }

    /**
     * @return Mage_Core_Model_Store
     * @throwException Exception
     */
    protected function getFrontendStore($code = null)
    {
        foreach (Mage::app()->getStores() as $store) {
            if (null === $code) {
                if (! $store->isAdmin()) return $store;
            } else {
                if ($store->getCode() == $code) return $store;
            }
        }
        $this->throwException(new Exception('Unable to find frontend store'));
    }

    /**
     * @return Mage_Core_Model_Store
     */
    protected function getAdminStore()
    {
        return Mage::app()->getStore('admin');
    }

    /**
     * @return string
     */
    protected function getConfigPrefix()
    {
        return $this->configSection . '/' . $this->configGroup .'/';
    }

    public function setUp()
    {
        /** @var helper Netzarbeiter_GroupsCatalog2_Helper_Data */
        $this->helper = Mage::helper('netzarbeiter_groupscatalog2');
    }

    // Tests #######

    public function testGetConfig()
    {
        $store = $this->getFrontendStore('germany');
        $store->setConfig($this->getConfigPrefix() . 'test', 256);
        $this->assertEquals($this->helper->getConfig('test', $store), 256);
    }

    public function testGetGroups()
    {
        $groups = $this->helper->getGroups();

        $this->assertInstanceOf('Mage_Customer_Model_Resource_Group_Collection', $groups);
    }

    public function testGetGroupsContainsNotLoggedIn()
    {
        $group = $this->helper->getGroups()->getItemByColumnValue('customer_group_code', 'NOT LOGGED IN');
        $this->assertInstanceOf('Mage_Customer_Model_Group', $group);
    }

    public function testIsModuleActiveFrontend()
    {
        $store = $this->getFrontendStore();

        $store->setConfig($this->getConfigPrefix() . 'is_active', 1);
        $this->assertEquals(true, $this->helper->isModuleActive($store), 'Store config active');

        $this->helper->setModuleActive(false);
        $this->assertEquals(false, $this->helper->isModuleActive($store), 'ModuleActive Flag should override store config');

        $this->helper->resetActivationState();
        $this->assertEquals(true, $this->helper->isModuleActive($store), 'resetActivationState() should revert to store config');

        $store->setConfig($this->getConfigPrefix() . 'is_active', 0);
        $this->assertEquals(false, $this->helper->isModuleActive($store), 'Store config inactive');
    }

    public function testIsModuleActiveAdmin()
    {
        $store = $this->getAdminStore();

        $store->setConfig($this->getConfigPrefix() . 'is_active', 1);
        $this->assertEquals(false, $this->helper->isModuleActive($store), 'Admin store is always inactive by default');
        $this->assertEquals(true, $this->helper->isModuleActive($store, false), 'Admin check disabled should return store setting');

        $store->setConfig($this->getConfigPrefix() . 'is_active', 0);
        $this->helper->setModuleActive(true);
        $this->assertEquals(false, $this->helper->isModuleActive($store), 'Admin scope should ignore module state flag');
        $this->assertEquals(true, $this->helper->isModuleActive($store, false), 'Admin check disabled should return module state flag');

        $this->helper->resetActivationState();
    }

    /**
     * @param string $storeCode
     * @param int $customerGroupId
     *
     * @dataProvider dataProvider
     */
    public function testIsProductVisible($storeCode, $customerGroupId)
    {
        $this->setCurrentStore($storeCode);
        foreach (array(1, 2) as $productId) {
            $product = Mage::getModel('catalog/product')->load($productId);
            $expected = $this->expected('%s-%s-%s', $storeCode, $customerGroupId, $productId);
            $visible = $this->helper->isEntityVisible($product, $customerGroupId);
            $message = sprintf(
                "Visibility for product %d, store %s, customer group %s (%d) is expected to be %d but found to be %d",
                $productId, $storeCode,
                $this->helper->getGroups()->getItemById($customerGroupId)->getCustomerGroupCode(),
                $customerGroupId, $expected->getIsVisible(), $visible
            );
            $this->assertEquals($expected->getIsVisible(), $visible, $message);
        }
    }

    public function testGetEntityVisibleDefaultGroupIds()
    {
        $this->markTestIncomplete();
    }

    public function testGetModeSettingByEntityType()
    {
        $this->markTestIncomplete();
    }

    public function testApplyConfigModeSetting()
    {
        $this->markTestIncomplete();
    }
}