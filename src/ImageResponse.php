<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */


namespace Thapp\Jmg\Http\Foundation;

use DateTime;
use InvalidArgumentException;
use Thapp\Jmg\Resource\CachedResource;
use Thapp\Jmg\Resource\ImageResourceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @class ImageResponse
 * @see Response
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ImageResponse extends Response
{
    /** @var bool */
    private $useXsend;

    /** @var ResourceInterface */
    private $resource;

    /** @var bool */
    private $prepared;

    /** @var bool */
    private static $trustXSendFileHeader = true;

    /**
     * @param ResourceInterface $resource
     * @param int $status
     * @param array $headers
     */
    public function __construct(ImageResourceInterface $resource, $status = 200, array $headers = [])
    {
        $this->status   = $status;
        $this->resource = $resource;
        $this->headers  = new ResponseHeaderBag($headers);
        $this->prepared = false;
    }

    /**
     * create
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     *
     * @return Response
     */
    public static function create($content = '', $status = 200, $headers = [])
    {
        if (!$content instanceof ImageResourceInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Response payload must be instanceof %s.',
                    ImageResourceInterface::class
                )
            );
        }

        return new static($content, $status, $headers);
    }

    /**
     * trustXsendFileHeaders
     *
     * @param boolean $trust
     *
     * @return void
     *
     */
    public static function trustXsendFileHeaders($trust)
    {
        static::$trustXSendFileHeader = (bool)$trust;
    }

    /**
     * prepare
     *
     * @param Request $request
     *
     * @access public
     * @return void
     */
    public function prepare(Request $request)
    {
        if (false === $this->prepared) {
            $this->prepareResponse($request);
            $this->prepared = true;
        }

        return $this;
    }

    /**
     * prepareResponse
     *
     * @param Request $request
     *
     * @return void
     */
    private function prepareResponse(Request $request)
    {
        $this->headers->set('Content-Transfer-Encoding', 'binary');

        $this->useXsend = static::$trustXSendFileHeader && $request->headers->has('X-Sendfile-Type');

        $lastMod = (new DateTime)->setTimestamp($modDate = $this->resource->getLastModified());
        $mod = strtotime($request->headers->get('if-modified-since', $time = time()));

        if (($this->resource instanceof CachedResource || $this->resource->isFresh($time)) && $mod === $modDate) {
            $this->setHeadersIfNotProcessed($lastMod);
        } else {
            $this->setProcessedHeaders($this->resource, $lastMod);
        }
    }

    /**
     * void
     *
     * @param mixed $lastMod
     *
     * @access protected
     * @return mixed
     */
    private function setHeadersIfNotProcessed($lastMod)
    {
        $this->setNotModified();
        $this->setLastModified($lastMod);
    }

    /**
     * setProcessedHeaders
     *
     * @param ResourceInterface $resouce
     * @param mixed $lastMod
     *
     * @return void
     */
    private function setProcessedHeaders(ImageResourceInterface $resource, $lastMod)
    {
        $this->setLastModified($lastMod);
        $this->headers->set('Content-Type', $resource->getMimeType());
        $this->headers->set('Accept-ranges', 'bytes');
        $this->headers->set('Keep-Alive', 'timeout=15, max=200');
        $this->headers->set('Connection', 'Keep-Alive', true);

        if ($this->useXsend && $resource->isLocal()) {
            $this->setXsendFileHeaders($resource->getPath(), $lastMod);
            return;
        }

        $this->setContent($content = $resource->getContents());
        $this->headers->set('Content-Length', $len = mb_strlen($content, '8bit'));

        $this->setEtag($resource->getHash());
    }

    /**
     * setXsendFileHeaders
     *
     * @param ResourceInterface $resource
     * @param mixed $lastMod
     *
     * @return void
     */
    private function setXsendFileHeaders($file, $lastMod)
    {
        $this->setEtag(sha1_file($file));

        $this->headers->set('Content-Length', filesize($file));
        $this->headers->set('Content-Disposition', sprintf('inline; filename="%s"', basename($file)));
        $this->headers->set('X-Sendfile', $file);
    }
}
