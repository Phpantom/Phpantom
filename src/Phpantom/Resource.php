<?php
namespace Phpantom;

use Assert\Assertion;
use Zend\Diactoros\Request;

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
     * @var Request
     */
    private $httpRequest;

    public function __construct(Request $httpRequest, $type)
    {
        Assertion::string($type);
        $this->type = $type;
        $this->httpRequest = $httpRequest;
    }

    public function getUrl()
    {
        return (string) $this->httpRequest->getUri();
    }

    /**
     * @return Request
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
        $data = ['type'=> $this->type, 'meta'=>$this->meta, 'url'=>(string) $this->getHttpRequest()->getUri()];
        return sha1(json_encode($data));
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
        $result = call_user_func_array([$this->httpRequest, $method], $params);
        if (0 === strpos($method, 'with')) {
            $this->httpRequest = $result;
            return $this;
        }
        return $result;
    }
}
