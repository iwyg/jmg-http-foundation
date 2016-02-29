<?php

namespace Thapp\Jmg\Tests\Http\Foundation;

use Thapp\Jmg\Http\Foundation\ImageResponse;

class ImageResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            new ImageResponse($this->mockImageResource())
        );
    }

    private function mockRequest()
    {
        return $this->getMockbuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()->getMock();
    }

    private function mockImageResource()
    {
        return $this->getMockbuilder('Thapp\Jmg\Resource\ImageResourceInterface')
            ->disableOriginalConstructor()->getMock();
    }

    private function mockCachedResource()
    {
        return $this->getMockbuilder('Thapp\Jmg\Resource\CachedResourceInterface')
            ->disableOriginalConstructor()->getMock();
    }
}
