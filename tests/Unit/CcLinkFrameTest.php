<?php

/*
 * Copyright (c) 2026 erik <erik@erik.xyz> — https://erik.xyz
 */

namespace Erikwang2013\IndustrialProtocols\CcLink\Tests\Unit;

use Erikwang2013\IndustrialProtocols\CcLink\Exception\CcLinkException;
use Erikwang2013\IndustrialProtocols\CcLink\Frame\CcLinkFrame;
use PHPUnit\Framework\TestCase;

class CcLinkFrameTest extends TestCase
{
    public function testCrc16XmodemKnownVector(): void
    {
        // CRC-16/XMODEM ("123456789" -> 0x31C3)
        $this->assertSame(0x31C3, CcLinkFrame::crc16('123456789'));
    }

    public function testCrc16EmptyData(): void
    {
        $this->assertSame(0x0000, CcLinkFrame::crc16(''));
    }

    public function testCyclicFrameBytesLayout(): void
    {
        $frame = CcLinkFrame::cyclic(0x01, chr(0x01));
        $bytes = $frame->toBytes();

        $this->assertSame(6, strlen($bytes));
        $this->assertSame(0x01, ord($bytes[0])); // StationNo
        $this->assertSame(0x00, ord($bytes[1])); // Flags: master->slave, cyclic
        $this->assertSame(0x01, ord($bytes[2])); // DataLen
        $this->assertSame(0x01, ord($bytes[3])); // Data
    }

    public function testTransientFrameSetsTypeFlag(): void
    {
        $frame = CcLinkFrame::transient(2, 'x');
        $this->assertSame(0x10, $frame->getFlags() & 0x10);
        $this->assertSame('transient', $frame->getData()['type']);
    }

    public function testResponseFrameSetsDirectionFlag(): void
    {
        $frame = CcLinkFrame::response(3, 'y');
        $this->assertSame(0x80, $frame->getFlags() & 0x80);
        $this->assertSame('slave_to_master', $frame->getData()['direction']);
        $this->assertSame('cyclic', $frame->getData()['type']);
    }

    public function testEncodeDecodeRoundTrip(): void
    {
        foreach ([[0, ''], [7, "\x00\x01\x02"], [255, 'ping'], [1, str_repeat('x', 255)]] as [$station, $data]) {
            $bytes = CcLinkFrame::cyclic($station, $data)->toBytes();
            $parsed = CcLinkFrame::fromBytes($bytes);

            $this->assertSame($station, $parsed->getStationNo());
            $this->assertSame($data, $parsed->getRawData());
            $this->assertSame(0x00, $parsed->getFlags() & 0x90);
            $this->assertSame(strlen($data), $parsed->getData()['data_len']);
        }
    }

    public function testMaxDataLength255(): void
    {
        $frame = CcLinkFrame::cyclic(1, str_repeat('A', 255));
        $bytes = $frame->toBytes();
        $this->assertSame(260, strlen($bytes)); // 3 header + 255 data + 2 CRC

        $parsed = CcLinkFrame::fromBytes($bytes);
        $this->assertSame(255, $parsed->getData()['data_len']);
    }

    public function testEmptyPayload(): void
    {
        $bytes = CcLinkFrame::cyclic(7, '')->toBytes();
        $this->assertSame(0, ord($bytes[2]));
        $this->assertSame(0, CcLinkFrame::fromBytes($bytes)->getData()['data_len']);
    }

    public function testFromBytesTooShortThrows(): void
    {
        $this->expectException(CcLinkException::class);
        $this->expectExceptionMessage('too short');
        CcLinkFrame::fromBytes("\x01\x00\x02");
    }

    public function testFromBytesIncompleteThrows(): void
    {
        // Header claims 4 data bytes but only 1 is present
        $bytes = CcLinkFrame::cyclic(1, 'abcd')->toBytes();
        $this->expectException(CcLinkException::class);
        $this->expectExceptionMessage('incomplete');
        CcLinkFrame::fromBytes(substr($bytes, 0, 5));
    }

    public function testFromBytesCrcMismatchThrows(): void
    {
        $bytes = CcLinkFrame::cyclic(1, 'abcd')->toBytes();
        $corrupt = $bytes[4] === "\x00" ? "\x01" : "\x00";
        $corrupt = substr_replace($bytes, $corrupt, 4, 1);

        $this->expectException(CcLinkException::class);
        $this->expectExceptionMessage('CRC mismatch');
        CcLinkFrame::fromBytes($corrupt);
    }

    public function testFromBytesRejectsFrameWithGarbageData(): void
    {
        // Corrupt the data byte, keep length: CRC check must catch it
        $bytes = CcLinkFrame::cyclic(2, 'OK')->toBytes();
        $corrupt = substr_replace($bytes, 'X', 3, 1);

        $this->expectException(CcLinkException::class);
        $this->expectExceptionMessage('CRC mismatch');
        CcLinkFrame::fromBytes($corrupt);
    }

    public function testGetDataSummary(): void
    {
        $data = CcLinkFrame::cyclic(9, 'ab')->getData();
        $this->assertSame(9, $data['station_no']);
        $this->assertSame('master_to_slave', $data['direction']);
        $this->assertSame('cyclic', $data['type']);
        $this->assertSame(2, $data['data_len']);
    }
}
