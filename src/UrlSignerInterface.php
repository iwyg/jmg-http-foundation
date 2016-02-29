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
use Symfony\Component\HttpFoundation\Request;

/**
 * @interface UrlSignerInterface
 *
 * @package Thapp\Jmg\Http\Foundation
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface UrlSignerInterface
{
    /**
     * validateRequest
     *
     * @param Request $request
     * @param Parameters $params
     * @param FilterExpression $filters
     *
     * @return bool
     */
    public function validateRequest(Request $request, Parameters $params, FilterExpression $filters = null);
}
