<?php

/*
 * This File is part of the Thapp\Jmg\Http package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Http\Foundation;

use Thapp\Jmg\Resource\ResourceInterface;
use Thapp\Jmg\Http\Foundation\ImageResponse;
use Symfony\Component\HttpFoundation\Request;
use Thapp\Jmg\Resolver\ImageResolverInterface;
use Thapp\Jmg\Resolver\RecipeResolverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @class Controller
 *
 * @package Thapp\Jmg\Http
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Controller
{
    use ImageControllerTrait;

    /** @var string */
    private $path;

    /** @var string */
    private $cachePath;

    /**
     * Constructor.
     *
     * @param mixed $path
     * @param mixed $cachePath
     * @param ImageResolverInterface $imageResolver
     * @param RecipeResolverInterface $recipes
     * @param UrlSignerInterface $signer
     */
    public function __construct(
        $path,
        $cachePath,
        ImageResolverInterface $imageResolver,
        RecipeResolverInterface $recipes = null,
        UrlSignerInterface $signer = null
    ) {
        $this->path = $path;
        $this->setImageResolver($imageResolver);

        if (null !== $recipes) {
            $this->setRecieps($recipes);
        }

        if (null !== $signer) {
            $this->setUrlSigner($signer);
        }
    }
    /**
     * getImage
     *
     * @param Request $request
     * @param string $path
     * @param string $params
     * @param string $source
     * @param string $filter
     *
     * @return Response
     */
    public function getImageAction(Request $request, $params, $source, $filter = null)
    {
        $this->setRequest($request);

        return $this->getImage($this->path, $params, $source, $filter);
    }

    /**
     * getFromQueryAction
     *
     * @param Request $request
     * @param string $source
     *
     * @return Response
     */
    public function getFromQueryAction(Request $request, $source)
    {
        $this->setRequest($request);

        $params = Parameters::fromQuery($query = $request->query->all());
        $filters = Filters::fromQuery($query);

        if (0 === count($filters->all())) {
            $filters = null;
        }

        return $this->resolveImage($this->path, $source, $params, $filters);
    }

    /**
     * getResource
     *
     * @param Request $request
     * @param string $recipe
     * @param string $source
     *
     * @return Response
     */
    public function getResource(Request $request, $recipe, $source)
    {
        $this->setRequest($request);
        $this->getResource($recipe, $source);
    }

    /**
     * getCached
     *
     * @param Request $request
     * @param string $prefix
     * @param string $id
     *
     * @return Response
     */
    public function getCachedAction(Request $request, $path, $id)
    {
        $this->setRequest($request);

        $this->getCached($path, $id);
    }
}
