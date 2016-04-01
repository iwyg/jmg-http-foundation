<?php

/*
 * This File is part of the Thapp\Jmg\Http\Foundation package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\Http\Foundation;

use Thapp\Jmg\Http\Foundation\UrlSigner;
use Thapp\Jmg\Exception\InvalidSignatureException;

/**
 * @class UrlSignerTest
 *
 * @package Thapp\Jmg\Http\Foundation
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class UrlSignerTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\Jmg\Http\Foundation\UrlSignerInterface', new UrlSigner('secret-key'));
    }

    /** @test */
    public function itShouldSignUrl()
    {
        $signer = new UrlSigner('my-key', 's');

        $signature = $signer->sign('/image/0/cat.jpg', $this->mockParameters());

        $this->assertTrue(0 === strpos($signature, '/image/0/cat.jpg?s='));
    }

    /** @test */
    public function itShouldvalidateRequestRequest()
    {
        $signer = new UrlSigner('my-key');
        $signature = $signer->sign($path = '/image/0/cat.jpg', $params = $this->mockParameters());

        $parts = parse_url($signature);
        parse_str($parts['query'], $q);

        $rq = $this->prepareRequest($path, $q['token']);

        $this->assertTrue($signer->validateRequest($rq, $params));
    }

    /** @test */
    public function itShouldThrowIfTokenIsMissing()
    {
        $signer = new UrlSigner('my-key');
        $rq = $this->prepareRequest(null, null);

        try {
            $signer->validateRequest($rq, $this->mockParameters());
        } catch (InvalidSignatureException $e) {
            $this->assertSame($e->getMessage(), 'Signature is missing.');
        }
    }

    /** @test */
    public function itShouldThrowIfTokenIsInvalid()
    {
        $signer = new UrlSigner('my-key');

        $rq = $this->prepareRequest(null, 'invalidtoken');

        try {
            $signer->validateRequest($rq, $this->mockParameters());
        } catch (InvalidSignatureException $e) {
            $this->assertSame($e->getMessage(), 'Signature is invalid.');
        }
    }

    /**
     * prepareRequest
     *
     * @param mixed $path
     * @param mixed $query
     * @param string $key
     *
     * @return Symfony\Component\HttpFoundation\Request
     */
    protected function prepareRequest($path = null, $query = null, $key = 'token')
    {
        $q = $this->mockQuery();
        $q->method('get')->with($key)->willReturn($query);
        $rq = $this->mockRequest(['getPathInfo']);

        $rq->method('getPathInfo')->willReturn($path);
        $rq->query = $q;

        return $rq;
    }

    /**
     * mockQuery
     *
     * @return Symfony\Component\HttpFoundation\ParameterBag
     */
    protected function mockQuery()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * mockParameters
     *
     * @param string $str
     *
     * @return Thapp\JitImage\Parameters;
     */
    protected function mockParameters($str = '0')
    {
        $mock = $this->getMockBuilder('Thapp\Jmg\ParamGroup')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('__toString')->willReturn($str);

        return $mock;
    }

    /**
     * mockRequest
     *
     * @param array $methods
     *
     * @return Symfony\Component\HttpFoundation\Request
     */
    protected function mockRequest(array $methods = [])
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
