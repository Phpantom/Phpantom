<?php

namespace Phpantom;

use Symfony\Component\DomCrawler\Crawler as BaseCrawler;

/**
 * Class Crawler
 * @package Phpantom
 */
class Crawler extends BaseCrawler
{

    /**
     * @param string $attribute
     * @param null $default
     * @return null|string
     */
    public function attr($attribute, $default = null)
    {
        if (!count($this)) {
            return $default;
        }

        return parent::attr($attribute);
    }

    /**
     * @param null $default
     * @return null|string
     */
    public function text($default = null)
    {
        if (!count($this)) {
            return $default;
        }
        return parent::text();
    }

    /**
     * @param null $default
     * @return null|string
     */
    public function html($default = null)
    {
        if (!count($this)) {
            return $default;
        }
        return parent::html();
    }
}