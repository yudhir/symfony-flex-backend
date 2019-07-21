<?php
declare(strict_types = 1);
/**
 * /tests/Integration/EventSubscriber/BodySubscriberTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Tests\Integration\EventSubscriber;

use App\EventSubscriber\BodySubscriber;
use Generator;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class BodySubscriberTest
 *
 * @package App\Tests\Integration\EventSubscriber
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class BodySubscriberTest extends KernelTestCase
{
    public function testThatEmptyBodyWorksLikeExpected(): void
    {
        static::bootKernel();

        $request = new Request();

        $event = new GetResponseEvent(static::$kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $subscriber = new BodySubscriber();
        $subscriber->onKernelRequest($event);

        static::assertEmpty($request->query->all());
        static::assertEmpty($request->request->all());

        unset($subscriber, $event, $request);
    }

    public function testThatNonJsonContentTypeWorksLikeExpected(): void
    {
        static::bootKernel();

        $inputQuery = [
            'foo' => 'bar',
        ];

        $inputRequest = [
            'bar' => 'foo',
        ];

        $request = new Request($inputQuery, $inputRequest, [], [], [], [], 'Some content');
        $request->headers->set('Content-Type', 'text/xml');

        $event = new GetResponseEvent(static::$kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $subscriber = new BodySubscriber();
        $subscriber->onKernelRequest($event);

        static::assertSame($inputQuery, $request->query->all());
        static::assertSame($inputRequest, $request->request->all());
    }

    /**
     * @dataProvider dataProviderTestThatJsonContentReplaceParametersAsExpected
     *
     * @param array  $expectedRequestParameters
     * @param string $contentType
     * @param string $content
     */
    public function testThatJsonContentReplaceParametersAsExpected(
        array $expectedRequestParameters,
        string $contentType,
        string $content
    ): void {
        static::bootKernel();

        $request = new Request([], ['foobar' => 'foobar'], [], [], [], [], $content);
        $request->headers->set('Content-Type', $contentType);

        $event = new GetResponseEvent(static::$kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $subscriber = new BodySubscriber();
        $subscriber->onKernelRequest($event);

        static::assertSame($expectedRequestParameters, $request->request->all());
    }

    public function testThatInvalidJsonContentThrowsAnException(): void
    {
        $this->expectException(LogicException::class);

        static::bootKernel();

        $request = new Request([], [], [], [], [], [], '{"Some": "not", "valid" JSON}');

        $event = new GetResponseEvent(static::$kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $subscriber = new BodySubscriber();
        $subscriber->onKernelRequest($event);
    }

    public function testThatWithNullBodyReplaceIsNotCalled(): void
    {
        static::bootKernel();

        /**
         * @var MockObject|Request      $request
         * @var MockObject|ParameterBag $paramegerBag
         */
        $request = $this->getMockBuilder(Request::class)->getMock();
        $paramegerBag = $this->getMockBuilder(ParameterBag::class)->getMock();

        $request->request = $paramegerBag;

        $request
            ->expects(static::exactly(2))
            ->method('getContent')
            ->willReturn(null);

        $paramegerBag
            ->expects(static::never())
            ->method('replace');

        $event = new GetResponseEvent(static::$kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $subscriber = new BodySubscriber();
        $subscriber->onKernelRequest($event);
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatJsonContentReplaceParametersAsExpected(): Generator
    {
        yield [
            ['foo' => 'bar'],
            '',
            '{"foo": "bar"}',
        ];

        yield [
            ['foo' => 'bar'],
            'application/json',
            '{"foo": "bar"}',
        ];

        yield [
            ['foo' => 'bar'],
            'application/x-json',
            '{"foo": "bar"}',
        ];

        yield [
            ['foo' => 'bar'],
            'text/plain',
            '{"foo": "bar"}',
        ];
    }
}
