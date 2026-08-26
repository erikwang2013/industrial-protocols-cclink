<?php

/*
 * Copyright (c) 2026 erik <erik@erik.xyz> — https://erik.xyz
 */

namespace Erikwang2013\IndustrialProtocols\CcLink\Tests\Unit;

use Erikwang2013\IndustrialProtocols\CcLink\CcLinkConnector;
use Erikwang2013\IndustrialProtocols\CcLink\CcLinkProtocol;
use Erikwang2013\IndustrialProtocols\CcLink\Exception\CcLinkException;
use Erikwang2013\IndustrialProtocols\Connection\ConnectionState;
use PHPUnit\Framework\TestCase;

class CcLinkConnectorTest extends TestCase
{
    public function testProtocolMetadata(): void
    {
        $p = new CcLinkProtocol();
        $this->assertSame('cc-link', $p->getName());
        $this->assertSame('1.1.1', $p->getVersion());
        $this->assertSame(0, $p->getDefaultPort());
        $this->assertSame(['rs485', 'v1', 'v2'], $p->getSupportedVariants());
    }

    public function testCreateConnector(): void
    {
        $connector = (new CcLinkProtocol())->createConnector(['device' => '/dev/null']);
        $this->assertInstanceOf(CcLinkConnector::class, $connector);
        $this->assertFalse($connector->isConnected());
    }

    public function testConnectMissingDeviceThrows(): void
    {
        $connector = new CcLinkConnector(['device' => '/nonexistent/cclink-port']);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to open CC-Link serial port');
        $connector->connect();
    }

    public function testSendWithoutConnectThrows(): void
    {
        $connector = new CcLinkConnector(['device' => '/dev/null']);
        $this->expectException(CcLinkException::class);
        $this->expectExceptionMessage('not connected');
        $connector->read('X1');
    }

    public function testHealthClosedWhenNotConnected(): void
    {
        $connector = new CcLinkConnector(['device' => '/dev/null']);
        $health = $connector->getHealth();
        $this->assertSame(ConnectionState::CLOSED, $health->state);
    }

    public function testConnectDevNullReadFailsGracefully(): void
    {
        // /dev/null opens as a character device, so connect() succeeds; the
        // subsequent read hits EOF and must raise a CcLinkException, not hang.
        $connector = new CcLinkConnector(['device' => '/dev/null', 'timeout' => 500]);
        $connector->connect();
        $this->assertTrue($connector->isConnected());

        try {
            $connector->read('X1');
            $this->fail('Expected CcLinkException on EOF read');
        } catch (CcLinkException $e) {
            $this->assertStringContainsString('read failed', $e->getMessage());
        }

        $connector->disconnect();
        $this->assertFalse($connector->isConnected());
    }

    public function testDefaultsApplied(): void
    {
        $connector = new CcLinkConnector([]);
        $this->assertFalse($connector->isConnected());
    }
}
