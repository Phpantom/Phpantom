<?php

namespace Phpantom\PostProcessor;

use Assert\Assertion;

/**
 * @todo make this more configurable
 * Class Csv
 * @package Phpantom
 */
class Csv extends PostProcessor
{
    /**
     * @var
     */
    private $handlers;
    /**
     * @var string
     */
    private $delimiter = '~';
    /**
     * @var string
     */
    private $enclosure = '"';
    /**
     * @var string
     */
    private $itemsSeparator = '#$#';
    /**
     * @var string
     */
    private $subItemsSeparator = '#;#';
    /**
     * @var string
     */
    private $encoding = 'utf-8';

    /**
     * @param $type
     * @return string
     */
    protected function getFileName($type)
    {
        Assertion::string($type);
        return 'data_' . preg_replace('~\W~iu', '_', $type) . '.csv';
    }

    /**
     * @param $type
     * @param $params
     * @return string
     */
    protected function getFilePath($type, array $params = [])
    {
        Assertion::string($type);
        $dir = isset($params['dir'])? $params['dir'] : sys_get_temp_dir();
        return rtrim($dir, '/') . '/' . $this->getFileName($type);
    }

    /**
     * @param $type
     * @param array $params
     * @return mixed
     */
    protected function getHandler($type, array $params = [])
    {
        Assertion::string($type);
        if (!isset($this->handlers[$type])) {
            $path = $this->getFilePath($type, $params);
            $dir = dirname($path);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $this->handlers[$type] = fopen($path, 'w');
        }
        return $this->handlers[$type];
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @return string
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }


    /**
     * @return string
     */
    public function getItemsSeparator()
    {
        return $this->itemsSeparator;
    }

    /**
     * @return string
     */
    public function getSubItemsSeparator()
    {
        return $this->subItemsSeparator;
    }


    /**
     * @param array $document
     * @param $type
     * @param array $params
     * @return mixed|void
     */
    public function process(array $document, $type, array $params = [])
    {
        Assertion::string($type);
        $handler = $this->getHandler($type, $params);
        $this->formatRow($handler, $document, $params);
    }

    /**
     * @param array $array
     * @param array $glues
     * @return string
     */
    public function implode_recursive(array $array, array $glues)
    {
        $out = '';
        $g = count($glues) > 1? array_shift($glues) : $glues[0];
        $c = count($array);
        $i = 0;
        foreach ($array as $val) {
            if (is_array($val)) {
                $out .= $this->implode_recursive($val, $glues);
            } else {
                $out .= (string) $val;
            }
            $i++;
            if ($i < $c) {
                $out .= $g;
            }
        }

        return $out;
    }

    /**
     * @param $handler
     * @param $row
     * @param array $params
     */
    protected function formatRow($handler, array $row, array $params = [])
    {
        foreach ($row as &$_) {
            if (is_array($_)) {
                $_ = $this->implode_recursive($_, [$this->getItemsSeparator(), $this->getSubItemsSeparator()]);
            }
            if (isset($params['encoding']) && strtolower($params['encoding']) !== 'utf-8') {
                $_ = iconv('utf-8', $params['encoding'], $_);
            }
         }
        fputcsv($handler, $row, $this->getDelimiter(), $this->getEnclosure());
    }
}
