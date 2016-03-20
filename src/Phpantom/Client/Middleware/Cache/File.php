<?php

namespace Phpantom\Client\Middleware\Cache;

use Assert\Assertion;
use Zend\Diactoros\Response;
use Phpantom\Client\Middleware\Cache;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class File
 * @package Phpantom\Client\Middleware\Cache
 */
class File extends Cache
{

    /**
     * @var string
     */
    protected $dir = './cache';

    /**
     * @param string $dir
     */
    public function setDir($dir)
    {
        Assertion::string($dir);
        $this->dir = $dir;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }


    /**
     * @param RequestInterface $resource
     * @return string
     */
    private function getPath(RequestInterface $resource)
    {
        $hash = sha1($resource->getUri());
        return rtrim($this->getDir(), '/')
        . '/'
        . implode('/',  preg_split('//', substr($hash, 0, 3), -1, PREG_SPLIT_NO_EMPTY))
        . '/' . $hash;
    }

    /**
     * @param RequestInterface $resource
     * @return mixed|null|Response
     */
    public function get(RequestInterface $resource)
    {
        $path = $this->getPath($resource);
        if (file_exists($path) && @filemtime($path) > time()) {
            $data = file_get_contents($path);
            $content = @unserialize($data);
            if (false === $content) {
                return null;
            } else {
                $response = $content->getHttpResponse();
            }
            return $response;
        } else {
            return null;
        }
    }

    /**
     * @param RequestInterface $resource
     * @param ResponseInterface $response
     * @param int $duration
     * @return mixed|string
     */
    public function cache(RequestInterface $resource, ResponseInterface $response, $duration = 0)
    {
        $serializable = new \Phpantom\Response($response);
        $path = $this->getPath($resource);
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        //writeStream? separate stream and other params and save in different files?
        //@see https://github.com/oscarotero/psr7-middlewares/blob/master/src/Middleware/SaveResponse.php
        if (@file_put_contents($path, serialize($serializable), LOCK_EX) !== false) {
            if ($duration <= 0) {
                $duration = 31536000; // 1 year
            }
            clearstatcache(true, $path);
            return @touch($path, $duration + time());
        } else {
            return false;
        }
    }

    /**
     * @param RequestInterface $resource
     * @return mixed|void
     */
    public function clear(RequestInterface $resource)
    {
        $path = $this->getPath($resource);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     *
     */
    public function purge()
    {
        if (file_exists($this->dir)) {
            exec('rm -r ' . escapeshellarg($this->dir));
        }
    }

}
