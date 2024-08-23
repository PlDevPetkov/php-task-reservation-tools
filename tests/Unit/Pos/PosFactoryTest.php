<?php

namespace App\Tests\Unit\Pos;

use App\Pos\PosFactory;
use App\Pos\Providers\Barsy;
use App\Pos\Providers\PosInterface;
use App\Pos\Providers\Rkeeper;
use PHPUnit\Framework\TestCase;

/**
 * @class PosFactoryTest
 * @package App\Tests\Unit\Pos
 */
class PosFactoryTest extends TestCase
{
    /** @var PosInterface|\PHPUnit\Framework\MockObject\MockObject  */
    private $providerMock1;

    /** @var PosInterface|\PHPUnit\Framework\MockObject\MockObject  */
    private $providerMock2;

    /** @var PosFactory  */
    private $posFactory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->providerMock1 = $this->createMock(PosInterface::class);
        $this->providerMock2 = $this->createMock(PosInterface::class);

        $this->providerMock1->method('getName')
            ->willReturn(Barsy::BARSY_PROVIDER_NAME);
        $this->providerMock2->method('getName')
            ->willReturn(Rkeeper::RKEEPER_PROVIDER_NAME);

        $this->posFactory = new PosFactory([$this->providerMock1, $this->providerMock2]);
    }

    /**
     * @return void
     */
    public function testGetProvidersNames()
    {
        $expected = [Barsy::BARSY_PROVIDER_NAME, Rkeeper::RKEEPER_PROVIDER_NAME];
        $this->assertEquals($expected, $this->posFactory->getProvidersNames());
    }

    /**
     * @return void
     */
    public function testGetProviders()
    {
        $providers = $this->posFactory->getProviders();

        $this->assertCount(2, $providers);
        $this->assertSame($this->providerMock1, $providers[Barsy::BARSY_PROVIDER_NAME]);
        $this->assertSame($this->providerMock2, $providers[Rkeeper::RKEEPER_PROVIDER_NAME]);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetProviderByName()
    {
        $this->assertSame(
            $this->providerMock1,
            $this->posFactory->getProviderByName(Barsy::BARSY_PROVIDER_NAME)
        );
        $this->assertSame(
            $this->providerMock2,
            $this->posFactory->getProviderByName(Rkeeper::RKEEPER_PROVIDER_NAME)
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetProviderByNameInvalid()
    {
        $name = 'InvalidProvider';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf('Provider "%s" not found.', $name));
        $this->posFactory->getProviderByName($name);
    }
}
