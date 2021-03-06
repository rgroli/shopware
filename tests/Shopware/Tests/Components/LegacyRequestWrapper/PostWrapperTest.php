<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
/**
 * @covers \Shopware\Components\LegacyRequestWrapper\PostWrapper
 */
class Shopware_Tests_Components_LegacyRequestWrapper_PostWrapperTest extends Enlight_Components_Test_Controller_TestCase
{
    private static $resources = array(
        'Admin',
        'Articles',
        'Basket',
        'Categories',
        'cms',
        'Core',
        'Export',
        'Marketing',
        'Order',
        'RewriteTable'
    );

    public function setUp()
    {
        parent::setUp();

        $this->dispatch('/');
    }

    /**
     * @covers \Shopware\Components\LegacyRequestWrapper\PostWrapper::offsetSet()
     */
    public function testSet()
    {
        Shopware()->Modules()->System()->_POST->offsetSet('foo', 'bar');
        $this->assertEquals('bar', Shopware()->Front()->Request()->getPost('foo'));

        Shopware()->Modules()->System()->_POST->offsetSet('foo', null);
        $this->assertNull(Shopware()->Front()->Request()->getPost('bar'));

        Shopware()->Modules()->System()->_POST->offsetSet('foo', array());
        $this->assertEmpty(Shopware()->Front()->Request()->getPost('bar'));
        $this->assertInternalType('array', Shopware()->Front()->Request()->getPost('foo'));
    }

    /**
     * @covers \Shopware\Components\LegacyRequestWrapper\PostWrapper::offsetSet()
     */
    public function testGet()
    {
        Shopware()->Front()->Request()->setPost('foo', 'bar');
        $this->assertEquals('bar', Shopware()->Modules()->System()->_POST->offsetGet('foo'));

        Shopware()->Front()->Request()->setPost('foo', null);
        $this->assertNull(Shopware()->Modules()->System()->_POST->offsetGet('bar'));

        Shopware()->Front()->Request()->setPost('foo', array());
        $this->assertEmpty(Shopware()->Modules()->System()->_POST->offsetGet('bar'));
        $this->assertInternalType('array', Shopware()->Modules()->System()->_POST->offsetGet('foo'));
    }

    /**
     * @covers \Shopware\Components\LegacyRequestWrapper\PostWrapper::offsetUnset()
     */
    public function testUnset()
    {
        Shopware()->Modules()->System()->_POST->offsetSet('foo', 'bar');
        $this->assertEquals('bar', Shopware()->Front()->Request()->getPost('foo'));
        unset(Shopware()->Modules()->System()->_POST['foo']);
        $this->assertNull(Shopware()->Front()->Request()->getPost('foo'));
    }

    /**
     * @covers \Shopware\Components\LegacyRequestWrapper\PostWrapper::setAll()
     */
    public function testSetAll()
    {
        Shopware()->Modules()->System()->_POST->offsetSet('foo', 'bar');
        $this->assertEquals('bar', Shopware()->Front()->Request()->getPost('foo'));

        Shopware()->Modules()->System()->_POST = array('foo' => 'too');
        $this->assertNull(Shopware()->Front()->Request()->getPost('bar'));
        $this->assertEquals('too', Shopware()->Front()->Request()->getPost('foo'));
    }

    /**
     * @covers \Shopware\Components\LegacyRequestWrapper\PostWrapper::toArray()
     */
    public function testToArray()
    {
        Shopware()->Front()->Request()->setPost('foo', 'bar');
        $this->assertEquals(array('foo' => 'bar'), Shopware()->Modules()->System()->_POST->toArray());
    }

    /**
     * Tests that setting a value inside any core class is equivalent to setting it in the
     * global $_POST
     *
     * @return mixed
     */
    public function testSetPost()
    {
        $previousGetData = Shopware()->Front()->Request()->getPost();

        foreach (self::$resources as $name) {
            Shopware()->Front()->Request()->setPost($name, $name.'Value');
        }

        $getData = Shopware()->Front()->Request()->getPost();
        $this->assertNotEquals($previousGetData, $getData);

        foreach (self::$resources as $name) {
            if (property_exists($name, 'sSYSTEM')) {
                $this->assertEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_POST->toArray());
            }
        }

        return $getData;
    }

    /**
     * Tests that resetting POST data inside any core class is equivalent to resetting it in the
     * global $_POST
     *
     * @param $getData
     * @return mixed
     * @depends testSetPost
     */
    public function testOverwriteAndClearPost($getData)
    {
        $this->assertNotEquals($getData, Shopware()->Front()->Request()->getPost());

        foreach (self::$resources as $name) {
            if (property_exists($name, 'sSYSTEM')) {
                $this->assertEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_POST->toArray());
                Shopware()->Modules()->getModule($name)->sSYSTEM->_POST = array();
                Shopware()->Front()->Request()->setPost($getData);
                $this->assertNotEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_POST->toArray());
            }
        }

        return $getData;
    }

    /**
     * Tests that getting POST data inside any core class is equivalent to getting it from the
     * global $_POST
     *
     * @depends testSetPost
     */
    public function testGetPost()
    {
        $previousGetData = Shopware()->Front()->Request()->getPost();

        foreach (self::$resources as $name) {
            Shopware()->Modules()->getModule($name)->sSYSTEM->_POST[$name] = $name.'Value';
        }

        $getData = Shopware()->Front()->Request()->getPost();
        $this->assertNotEquals($previousGetData, $getData);

        foreach (self::$resources as $name) {
            if (property_exists($name, 'sSYSTEM')) {
                $this->assertEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_POST->toArray());
            }
        }
    }
}
