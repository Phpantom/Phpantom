<?php
namespace Phpantom;

use Assert\Assertion;
use Phly\Http\Request;

class Resource
{

    /**
     * @var array
     */
    private $meta;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \Phly\Http\Request
     */
    private $httpRequest;

    public function __construct(Request $httpRequest, $type)
    {
        Assertion::string($type);
        $this->type = $type;
        $this->httpRequest = $httpRequest;
    }

    /**
     * @return mixed
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @return string
     */
    public function getHash()
    {
        return sha1(json_encode((array)$this));
    }

    /**
     * @param array $meta
     * @return $this
     */
    public function setMeta(array $meta = [])
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @param array $meta
     * @return $this
     */
    public function addMeta(array $meta = [])
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Proxy
     * @param $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params = [])
    {
        return call_user_func_array([$this->httpRequest, $method], $params);
    }
}
