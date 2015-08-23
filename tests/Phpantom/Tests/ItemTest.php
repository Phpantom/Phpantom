<?php

use Phpantom\Item;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    public function testAsArray()
    {
        $item = new Item();
        $item->id = '123abc';
        $item->type = 'foo123';
        $this->assertEquals(['id'=>'123abc', 'type'=>'foo123'], $item->asArray());
    }

    /**
     * @expectedException \DomainException
     */
    public function testGettingUnknownProperty()
    {
        $item = new Item();
        $item->foo = 'bar';
    }

    /**
     * @expectedException \DomainException
     */
    public function testSettingUnknownProperty()
    {
        $item = new Item();
        $foo = $item->foo;
    }

    /**
     * @expectedException \Respect\Validation\Exceptions\NestedValidationExceptionInterface
     */
    public function testDefaultIdValidationFail()
    {
        $item = new Item();
        $item->id = ['foo'=>'bar'];
        $item->validate();
    }

    /**
     * @expectedException \Respect\Validation\Exceptions\NestedValidationExceptionInterface
     */
    public function testDefaultTypeValidationFail()
    {
        $item = new Item();
        $item->type = ['foo'=>'bar'];
        $item->validate();
    }
}
