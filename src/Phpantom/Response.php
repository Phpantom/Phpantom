<?php
namespace Phpantom;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response as HttpResponse;

class Response implements \Serializable
{
    /**
     * @var HttpResponse
     */
    private $httpResponse;

    public function __construct(ResponseInterface $httpResponse)
    {
        $this->httpResponse = $httpResponse;
    }

    /**
     * @return mixed
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    public function getContent()
    {
        return (string) $this->getHttpResponse()->getBody();
    }

    /**
     * Proxy
     * @param $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params = [])
    {
        $response = call_user_func_array([$this->httpResponse, $method], $params);
        if (0 === strpos($method, 'with')) {
            $this->httpResponse = $response;
            return $this;
        }
        return $response;
    }


    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        $data = ['httpResponse' => HttpResponse\Serializer::toString($this->getHttpResponse())];
        return serialize($data);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->httpResponse = HttpResponse\Serializer::fromString($data['httpResponse']);
    }
}
