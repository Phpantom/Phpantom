<?php

namespace Phpantom\Client;

use Assert\Assertion;
use Phly\Http\Response as HttpResponse;
use Psr\Http\Message\RequestInterface;

class FileGetContents implements ClientInterface
{
    private $timeout = 10;
    private $proxy;

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setSiteout($timeout)
    {
        Assertion::numeric($timeout);
        $this->timeout = $timeout;
        return $this;
    }

    public function setProxy(Proxy $proxy)
    {
        $this->proxy = $proxy;
        return $this;
    }

    /**
     * @return Proxy|null
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * @return mixed|null
     */
    public function nextProxy()
    {
        $proxy = $this->getProxy();
        return $proxy? $proxy->nextProxy() : null;
    }


    public function load(RequestInterface $request)
    {
        $headersList = [];
        //'header'=>'Connection: close' @see http://php.net/manual/en/function.file-get-contents.php comments
        foreach ($request->getHeaders() as $key => $val) {
            $headersList[] = "$key: " . implode(", ", $val);
        }
        $opts = array('http' =>
            array(
                'method'  => $request->getMethod()?: 'GET',
                'header'  => implode("\r\n", $headersList),
                'content' => $request->getBody(),
                'timeout' => $this->getTimeout(),
                'ignore_errors' => true, //don't throw errors on 404 and so on
                'request_fulluri' => true,
//                'protocol_version' => 1.1
                'proxy' => $this->nextProxy()? : null
            )
        );

        $context  = stream_context_create($opts);
        $data = @file_get_contents($request->getUri(), false, $context);
        $httpResponse = new HttpResponse();
        $httpResponse->getBody()->write($data);
        if (!empty($http_response_header)) {
            foreach($http_response_header as $header) {
                if (strpos($header, ':')) {
                    list($k, $v) = explode(':', $header);
                    $httpResponse = $httpResponse->withAddedHeader($k, $v);
                } else {//status
                    list ($protocol, $status) = explode(" ", $header, 2);
                    $httpResponse = $httpResponse->withAddedHeader('status', $status)
                                         ->withStatus(intval($status));
                }
            }
        }
        return $httpResponse;
    }
}
