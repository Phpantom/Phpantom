<?php

namespace Phpantom\Client\Middleware\Cache;

use Assert\Assertion;
use Phly\Http\Response;
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
        if (file_exists($path)) {
            $data = file_get_contents($path);
            $content = @unserialize($data);
            if (false === $content) {
                $response = new Response('php://memory', 200, ['status'=>200]);
                $response->getBody()->write($data);

            } else {
                $response = $content;//throw Exception??
            }
            return $response;
        } else {
            return null;
        }
    }

    /**
     * @param RequestInterface $resource
     * @param ResponseInterface $response
     * @return mixed|string
     */
    public function cache(RequestInterface $resource, ResponseInterface $response)
    {
        $path = $this->getPath($resource);
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, serialize($response));
        clearstatcache(true, $path);
        return $path;
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
