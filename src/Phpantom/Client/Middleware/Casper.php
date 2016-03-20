<?php

namespace Phpantom\Client\Middleware;

use Assert\Assertion;
use Phpantom\Client\Proxy;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Relay\MiddlewareInterface;
use Zend\Diactoros\Response as HttpResponse;

/**
 * Class Casper
 * @package Phpantom\Client
 */
class Casper implements MiddlewareInterface
{
    /**
     * @var array
     */
    private $options = array();
    /**
     * @var string
     */
    private $debug = 'true';
    /**
     * @var string
     */
    private $script = '';

    /**
     * @var
     */
    private $scriptProvider;

    /**
     * @var string
     */
    private $defaultUserAgent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0';

    /**
     * @var Proxy
     */
    private $proxy;

    /**
     * enable debug logging into syslog
     *
     * @param string $debug
     * @return Casper
     */
    public function setDebug($debug)
    {
        Assertion::inArray($debug, ['true', 'false']);
        $this->debug = $debug;
        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function isDebug()
    {
        return 'true' === $this->debug ? true : false;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * @return string
     */
    public function getDefaultUserAgent()
    {
        return $this->defaultUserAgent;
    }

    /**
     * @return Proxy
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * set specific options to casperJS
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param string $script
     * @return $this
     */
    public function setScript($script)
    {
        Assertion::string($script);
        $this->script = $script;
        return $this;
    }

    /**
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * @return callable
     */
    public function getScriptProvider()
    {
        return $this->scriptProvider;
    }

    /**
     * @param mixed $scriptProvider
     * @return $this
     */
    public function setScriptProvider(callable $scriptProvider)
    {
        $this->scriptProvider = $scriptProvider;
        return $this;
    }

    /**
     * @param Proxy $proxy
     * @return $this
     */
    public function setProxy(Proxy $proxy)
    {
        $this->proxy = $proxy;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function nextProxy()
    {
        $proxy = $this->getProxy();
        return $proxy ?  $proxy->nextProxy() : null;
    }

    private function isValidJson($json)
    {
        json_decode($json);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Apply proxy if set
     */
    private function applyProxy()
    {
        $proxy = $this->nextProxy();
        if ($proxy !== null ) {
            $this->options['proxy'] = $proxy;
        }
    }

    /**
     * @return string
     */
    protected function getDefaultScript()
    {
        $script = <<<SCRIPT
    var casper = require('casper').create({
    verbose: false,
    logLevel: 'error',
    exitOnError: true,
    colorizerType: 'Dummy',
    onError: function(self, m) {
        self.echo('ERROR');
        self.exit();
    },
    onLoadError: function(self, m) {
        self.echo('LOAD ERROR');
        self.exit();
    },
    pageSettings: {
        javascriptEnabled: true,
        loadImages:false,
        loadPlugins: false,
        localToRemoteUrlAccessEnabled: false,
        userName: null,
        password: null,
        XSSAuditingEnabled: false,
    }
    });
    casper.userAgent('{{userAgent}}');
    casper.start().then(function() {
    this.open('{{url}}', {
        method: '{{method}}',
        headers: {{headers}},
        data: {{data}}
    });
    this.then(function(response) {
        this.echo(this.getPageContent());
        this.echo('[PHPANTOM-CASPER-DELIMITER]');
        require('utils').dump(response);
        this.exit();
    });
    });
    casper.run();
SCRIPT;
        return $script;
    }

    /**
     * @param Request $request the request
     * @param Response $response the response
     * @param callable|MiddlewareInterface|null $next the next middleware
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        if (!$script = $this->getScript()) {
            if ($provider = $this->getScriptProvider()) {
                $script = $provider($request);
            } else {
                $script = $this->getDefaultScript();
            }
        }
        $url = (string)$request->getUri();
        $method = strtolower($request->getMethod());
        $headersList = [];
        foreach ($request->getHeaders() as $key => $val) {
            $headersList[$key] = implode(', ', $val);
        }
        $headers = json_encode($headersList ?: [], JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES);
        $userAgent = isset($headers['User-Agent']) ? $headers['User-Agent'] : $this->getDefaultUserAgent();
        $json = (string)$request->getBody();
        if ($this->isValidJson($json)) {
            $script = strtr(
                $script,
                [
                    '{{userAgent}}' => $userAgent,
                    '{{url}}' => $url,
                    '{{method}}' => $method,
                    '{{headers}}' => $headers,
                    '{{data}}' => $json ?: 'null'
                ]
            );
        } else {
            throw new \InvalidArgumentException("$method data expected to be properly json encoded string");
        }
        $filename = tempnam(sys_get_temp_dir(), 'phpantom-casperjs');
        file_put_contents($filename, $script);
        $options = '';
        $this->applyProxy();
        foreach ($this->getOptions() as $option => $value) {
            $options .= ' --' . $option . '=' . $value;
        }
        exec('casperjs ' . $filename . $options, $output);
        $httpResponse = new HttpResponse();
        $content = '';
        $json = '';
        $output = implode('', $output);
        if (false !== strpos($output, '[PHPANTOM-CASPER-DELIMITER]')) {
            list ($content, $json) = explode('[PHPANTOM-CASPER-DELIMITER]', $output);
        }
        $httpResponse->getBody()->write($content);
        if ($json && ($meta = json_decode(trim($json)))) {
            foreach ($meta->headers as $header) {
                $header->value = explode("\n", $header->value);
                $httpResponse = $httpResponse->withAddedHeader($header->name, $header->value);
            }
            $status = intval($meta->status);
            $httpResponse = $httpResponse->withAddedHeader('status', strval($status))
                ->withStatus(intval($status));
        }
        @unlink($filename);
        return is_null($next)? $httpResponse : $next($request, $httpResponse);
    }
}
