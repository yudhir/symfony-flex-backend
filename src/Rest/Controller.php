<?php
declare(strict_types=1);
/**
 * /src/Rest/Controller.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Rest;

/**
 * Class Controller
 *
 * @package App\Rest
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
abstract class Controller implements ControllerInterface
{
    /**
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * @var ResponseHelperInterface
     */
    protected $responseHelper;

    /**
     * Controller constructor.
     *
     * @param ResourceInterface       $resource
     * @param ResponseHelperInterface $responseHelper
     */
    public function __construct(ResourceInterface $resource, ResponseHelperInterface $responseHelper)
    {
        $this->resource = $resource;
        $this->responseHelper = $responseHelper;

        $this->responseHelper->setResource($this->resource);
    }

    /**
     * @return ResourceInterface
     */
    public function getResource(): ResourceInterface
    {
        return $this->resource;
    }

    /**
     * @return ResponseHelperInterface
     */
    public function getResponseHelper(): ResponseHelperInterface
    {
        return $this->responseHelper;
    }
}
