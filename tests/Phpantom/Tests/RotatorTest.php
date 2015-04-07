<?php

use Zoya\Coin\Always;

class RotatorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSetStrategy()
    {
        $rotator = $this->getMockBuilder('Phpantom\\Rotator')->setMethods(null)->getMockForTrait();
        $strategy = new Always();
        $rotator->setStrategy($strategy);
        $this->assertEquals($strategy, $rotator->getStrategy());
    }

    public function testDefaultStrategy()
    {
        $rotator = $this->getMockBuilder('Phpantom\\Rotator')->setMethods(null)->getMockForTrait();
        $this->assertInstanceOf('Zoya\\Coin\\Batch', $rotator->getStrategy());
    }

    public function testFirstRotate()
    {
        $rotator = $this->getMockBuilder('Phpantom\\Rotator')->setMethods(['getStrategy'])->getMockForTrait();
        $callback = $this->getMockBuilder('RotatorTest')->disableOriginalConstructor()
            ->setMethods(['rotateCallback'])->getMock();
        $callback->expects($this->once())->method('rotateCallback')->with('foo');
        $rotator->rotate([$callback, 'rotateCallback'], 'foo');
    }

    public function rotateProvider()
    {
        return [
            [$this->once(), false],
            [$this->exactly(2), true]
        ];
    }

    public function rotateCallback()
    {

    }
    /**
     * @dataProvider rotateProvider
     */
    public function testRotate($times, $isLucky)
    {
        $rotator = $this->getMockBuilder('Phpantom\\Rotator')->setMethods(['getStrategy'])->getMockForTrait();
        $coin = $this->getMockBuilder('Zoya\\Coin\\Always')->setMethods(['flip','isLucky'])->getMock();
        $coin->expects($this->once())->method('isLucky')->will($this->returnValue($isLucky));
        $rotator->expects($this->any())->method('getStrategy')->will($this->returnValue($coin));
        $callback = $this->getMockBuilder('RotatorTest')->disableOriginalConstructor()
            ->setMethods(['rotateCallback'])->getMock();
        $callback->expects($times)->method('rotateCallback')->with('foo')->will($this->returnValue('value'));
        //first time
        $rotator->rotate([$callback, 'rotateCallback'], 'foo');
        //next times
        $rotator->rotate([$callback, 'rotateCallback'], 'foo');
    }
}
