<?php

/**
 * This File is part of the Thapp\Jmg\Tests\Http\Foundation package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\Http\Foundation;

use Thapp\Jmg\Http\Foundation\Controller;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceOf('Thapp\Jmg\Http\Foundation\Controller', $this->newController());
    }

    /** @test */
    public function itShouldHandleImageFromUrlParams()
    {
        $images = $this->mockImageResolver();
        $controller = $this->newController('images', 'cached', $images);

        $request = $this->mockRequest();
        $request->headers = $this->mockParameterBag();
        $images->expects($this->once())->method('resolve')->willReturnCallback(function ($src, $params) {
            $this->assertSame('myimage.jpg', $src);
            $this->assertInstanceof('Thapp\Jmg\Parameters', $params);

            return $this->mockImageResource();
        });

        $this->assertInstanceOf(
            'Thapp\Jmg\Http\Foundation\ImageResponse',
            $controller->getImageAction($request, '1/100/100', 'myimage.jpg')
        );
    }

    private function newController(
        $path = 'images',
        $cached = 'cached',
        $images = null,
        $recipes = null,
        $signer = null
    ) {
        return new Controller($path, $cached, $images ?: $this->mockImageResolver(), $recipes, $signer);
    }

    private function mockRequest()
    {
        return $this->getMockbuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()->getMock();
    }

    private function mockImageResolver()
    {
        return $this->getMockbuilder('Thapp\Jmg\Resolver\ImageResolverInterface')
            ->disableOriginalConstructor()->getMock();
    }

    private function mockRecipesResolver()
    {
        return $this->getMockbuilder('Thapp\Jmg\Resolver\RecipeResolverInterface')
            ->disableOriginalConstructor()->getMock();
    }

    private function mockParameterBag()
    {
        return $this->getMockbuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()->getMock();
    }

    private function mockImageResource()
    {
        return $this->getMockbuilder('Thapp\Jmg\Resource\ImageResourceInterface')
            ->disableOriginalConstructor()->getMock();
    }
}
