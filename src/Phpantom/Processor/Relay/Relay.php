<?php
/**
 *
 * This file is part of Relay for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @copyright 2015, Paul M. Jones
 *
 */
namespace Phpantom\Processor\Relay;

use Phpantom\Resource as Resource;
use Phpantom\Response as Response;
use Phpantom\ResultSet as ResultSet;

/**
 *
 * A multiple-use middleware dispatcher.
 *
 */
class Relay
{
    /**
     *
     * A factory to create Runner objects.
     *
     * @var RunnerFactory
     *
     */
    protected $runnerFactory;

    /**
     *
     * Constructor.
     *
     * @param RunnerFactory $runnerFactory A factory to create Runner objects.
     *
     */
    public function __construct(RunnerFactory $runnerFactory)
    {
        $this->runnerFactory = $runnerFactory;
    }

    /**
     *
     * Dispatches to a new Runner.
     *
     * @param Resource $resource
     * @param Response $response The response.
     * @param ResultSet $resultSet
     *
     */
    public function __invoke(Resource $resource, Response $response, ResultSet $resultSet)
    {
        $runner = $this->runnerFactory->newInstance();
        return $runner($resource, $response, $resultSet);
    }
}
