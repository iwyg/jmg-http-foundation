<?php

/*
 * This File is part of the Thapp\Jmg\Http\Foundation package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Http\Foundation;

use Thapp\Jmg\Parameters;
use Thapp\Jmg\FilterExpression;
use Thapp\Jmg\Http\UrlSigner as BaseSigner;
use Symfony\Component\HttpFoundation\Request;
use Thapp\Jmg\Exception\InvalidSignatureException;

/**
 * @class UrlSigner
 *
 * @package Thapp\Jmg\Http\Foundation
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class UrlSigner extends BaseSigner implements UrlSignerInterface
{
    /**
     * {@inheritdoc}
     */
    public function validateRequest(Request $request, Parameters $params, FilterExpression $filters = null)
    {
        if (null === $token = $request->query->get($this->getQParamKey())) {
            throw InvalidSignatureException::missingSignature();
        }

        if (0 !== strcmp($token, $this->createSignature($request->getPathInfo(), $params, $filters))) {
            throw InvalidSignatureException::invalidSignature();
        }

        return true;
    }
}
