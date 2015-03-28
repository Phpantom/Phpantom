<?php

namespace Phantom\Tests\Processor;

class CsvTest extends \PHPUnit_Framework_TestCase
{

    public static function tearDownAfterClass()
    {
        @unlink(sys_get_temp_dir() . '/data_test.csv');
        @unlink(sys_get_temp_dir() . '/csv_processor/data_test2.csv');
        @rmdir(sys_get_temp_dir() . '/csv_processor');
    }


    public function testProcess()
    {
        $processor = $this->getMockBuilder('Phpantom\Processor\Csv')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $processor->process(['foo'=>'bar'], 'test', []);
        $this->assertFileExists(sys_get_temp_dir() . '/data_test.csv');

        $processor->process(['foo'=>'bar'], 'test', ['dir'=> sys_get_temp_dir() .'/csv_processor']);
        $this->assertFileNotExists(sys_get_temp_dir() .'/csv_processor/data_test.csv');//File handler cached by type

        $processor->process(['foo'=>'bar'], 'test2', ['dir'=> sys_get_temp_dir() .'/csv_processor']);
        $this->assertFileExists(sys_get_temp_dir() .'/csv_processor/data_test2.csv');
    }
}
