<?php

/**
 * This File is part of the Thapp\Jmg package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Http\Foundation;

use Thapp\Jmg\ParamGroup;
use Thapp\Jmg\FilterExpression;
use Thapp\Jmg\Resource\ResourceInterface;
use Thapp\Jmg\Resolver\ResolverInterface;
use Thapp\Jmg\Resolver\ImageResolverInterface;
use Thapp\Jmg\Http\Foundation\ImageResponse;
use Thapp\Jmg\Http\Foundation\UrlSignerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Thapp\Jmg\Exception\InvalidSignatureException;
use Thapp\Jmg\Http\Foundation\Exception\ImageNotFoundException;
use Thapp\Jmg\Http\Foundation\Exception\JmgImageNotFoundException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @class ImageControllerTrait
 * @package Thapp\Jmg
 * @version $Id$
 */
trait ImageControllerTrait
{
    /** @var Request */
    private $request;


    /** @var ResolverInterface */
    private $pathResolver;

    /** @var ParameterResolverInterface */
    private $imageResolver;

    /** @var UrlSignerInterface */
    private $signer;

    /** @var ResolverInterface */
    private $recipes;

    /**
     * pathResolver
     *
     * @param ResolverInterface $pathResolver
     *
     * @return void
     */
    public function setPathResolver(ResolverInterface $pathResolver)
    {
        $this->pathResolver  = $pathResolver;
    }

    /**
     * pathResolver
     *
     * @param ParameterResolverInterface $imageResolver
     *
     * @return void
     */
    public function setImageResolver(ImageResolverInterface $imageResolver)
    {
        $this->imageResolver  = $imageResolver;
    }

    /**
     * setUlrSigner
     *
     * @param HttpSignerInterface $signer
     *
     * @return void
     */
    public function setUrlSigner(UrlSignerInterface $signer)
    {
        $this->signer  = $signer;
    }

    /**
     * setRequest
     *
     * @param mixed $request
     *
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @param ResolverInterface $recipes
     *
     * @return void
     */
    public function setRecieps(ResolverInterface $recipes)
    {
        $this->recipes = $recipes;
    }

    /**
     * Resolve a dynamic route
     *
     * @param string $alias
     * @param string $params
     * @param string $source
     *
     * @throws NotFoundHttpException if image was not found
     * @return Response
     */
    public function getImage($path, $params = null, $source = null)
    {
        return $this->resolveImage($path, $source, ParamGroup::fromString($params));
    }

    /**
     * Resolve an aliased route
     *
     * @param string $route
     * @param string $alias
     * @param string $source
     *
     * @throws NotFoundHttpException if image was not found
     * @return ImageResponse
     */
    public function getResource($recipe, $source)
    {
        if (null === $this->recipes) {
            $this->notFound($source);
        }

        list($path, $params) = $this->recipes->resolve($recipe);

        return $this->resolveImage($path, $source, $params);
    }

    /**
     * Resolve a cache route
     *
     * @param string $path
     * @param string $id
     *
     * @throws NotFoundHttpException if image was not found
     * @return ImageResponse
     */
    public function getCached($path, $id)
    {
        if (!$resource = $this->imageResolver->resolveCached($path, $id)) {
            $this->notFound($id);
        }

        return $this->processResource($resource, $this->getRequest());
    }

    /**
     * resolveImage
     *
     * @param mixed $path
     * @param mixed $source
     * @param Parameters $params
     * @param FilterExpression $filter
     *
     * @throws NotFoundHttpException if image was not found
     * @return ImageResponse
     */
    protected function resolveImage($path, $source, ParamGroup $params)
    {
        $this->validateRequest($req = $this->getRequest(), $params);

        if (!$resource = $this->imageResolver->resolve($source, $params, $path)) {
            $this->notFound($source);
        }

        return $this->processResource($resource, $req);
    }

    /**
     * Validates current Request
     *
     * @param Request $request
     * @param ParamGroup $params
     *
     * @throws BadRequestHttpException if validation fails
     * @return boolean
     */
    private function validateRequest(Request $request, ParamGroup $params)
    {
        if (null === $this->signer) {
            return true;
        }

        try {
            return $this->signer->validateRequest($request, $params);
        } catch (InvalidSignatureException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return true;
    }

    /**
     * Get the current Request object.
     *
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * processResource
     *
     * @param ResourceInterface $resource
     * @param Request $request
     *
     * @return ImageResponse
     */
    private function processResource(ResourceInterface $resource, Request $request)
    {
        return (new ImageResponse($resource))->prepare($request);
    }

    /**
     * notFournd
     *
     * @throws NotFoundHttpException always
     *
     * @return void
     */
    private function notFound($source)
    {
        $msg = sprintf('Resource "%s" could not be found.', $source);

        if (class_exists('Symfony\Component\HttpKernel\Exception\NotFoundHttpException')) {
            throw new ImageNotFoundException($msg);
        }

        throw new JmgImageNotFoundException($msg);
    }
}
