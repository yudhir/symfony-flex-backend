<?php
declare(strict_types = 1);
/**
 * /tests/Integration/AutoMapper/GenericRestRequestMapperTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Tests\Integration\AutoMapper;

use App\AutoMapper\RestRequestMapper;
use App\DTO\RestDtoInterface;
use InvalidArgumentException;
use LengthException;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class GenericRestRequestMapperTest
 *
 * @package App\Tests\Integration\AutoMapper
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class GenericRestRequestMapperTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testThatMapToObjectThrowsAnExceptionIfSourceIsNotRequest(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RestRequestMapper expects that $source is Request object, "stdClass" provided');

        /**
         * @var MockObject|RestRequestMapper $mockRestRequestMapper
         */
        $mockRestRequestMapper = $this->getMockForAbstractClass(RestRequestMapper::class, [], 'MockMapper');

        $mockRestRequestMapper->mapToObject(new stdClass(), new stdClass());
    }

    /**
     * @throws Throwable
     */
    public function testThatMapToObjectThrowsAnExceptionIfDestinationIsNotRestDtoInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'RestRequestMapper expects that $destination is instance of RestDtoInterface object, "stdClass" provided'
        );

        /**
         * @var MockObject|RestRequestMapper $mockRestRequestMapper
         */
        $mockRestRequestMapper = $this->getMockForAbstractClass(RestRequestMapper::class, [], 'MockMapper');

        $mockRestRequestMapper->mapToObject(new Request(), new stdClass());
    }

    /**
     * @throws Throwable
     */
    public function testThatMapToObjectThrowsAnExceptionIfThereIsNotPropertiesToConvert(): void
    {
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage(
            'RestRequestMapper expects that mapper "MockMapper::$properties" contains properties to convert'
        );

        /**
         * @var MockObject|RestRequestMapper $mockRestRequestMapper
         * @var MockObject|RestDtoInterface  $mockRestDtoInterface
         */
        $mockRestRequestMapper = $this->getMockForAbstractClass(RestRequestMapper::class, [], 'MockMapper');
        $mockRestDtoInterface = $this->getMockBuilder(RestDtoInterface::class)->getMock();

        $mockRestRequestMapper->mapToObject(new Request(), $mockRestDtoInterface);
    }
}
