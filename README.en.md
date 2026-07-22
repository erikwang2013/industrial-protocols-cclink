# CC-Link RS-485 协议包 — 主从轮询，CRC-16/XMODEM 校验

> [中文](README.md)

CC-Link RS-485 协议包 — 主从轮询，CRC-16/XMODEM 校验。Pure PHP implementation, compatible with 6 PHP runtimes via kernel framework adapters.

## Installation

```bash
composer require erikwang2013/industrial-protocols-kernel erikwang2013/industrial-protocols-cclink
```

> Depends on [erikwang2013/industrial-protocols-kernel](https://github.com/erikwang2013/industrial-protocols-kernel) for connection management, protocol registry, coroutine adaptation, event system and more.

## Architecture

Built on kernel SDK interfaces (ProtocolInterface/ConnectorInterface/DriverInterface/FrameInterface), with CclinkDriver for transport and CclinkConnector for unified ConnectorInterface.

## Features

Complete cclink protocol frame encode/decode, driver transport, Connector wrapper, health check, connection strategies (Lazy/Eager/Pooled)

## Supported Frameworks

Compatible with 6 PHP runtimes via kernel framework adapters: Laravel (ServiceProvider+Facade+artisan), Webman (config/plugin auto-discovery+ProtocolProcess), Hyperf (ConfigProvider+DI+KernelFactory), ThinkPHP (services.php+IndustrialProtocolsService), Yii2 (Bootstrap+component), Plain PHP (direct Kernel instantiation)

### Laravel

```php
// AppServiceProvider::boot()
$kernel = app(Kernel::class);
$kernel->getProtocolRegistry()->register(new ModbusProtocol());
$kernel->boot();
$conn = $kernel->getConnectionManager()->connect('device-id');
```

### Webman

Auto-boot via ProtocolProcess on worker start. Configure at `config/plugin/erikwang2013/industrial-protocols-kernel/config/industrial-protocols.php`.

### Hyperf

```php
$kernel = \Hyperf\Context\ApplicationContext::getContainer()->get(Kernel::class);
```

## Usage

```php
$conn = $kernel->getConnectionManager()->connect('cclink-device');
$result = $conn->read('RWw0');               // remote register
```

## Configuration

```php
'devices' => [
    'device-id' => [
        'protocol' => 'cclink',
        'host'     => '192.168.1.10',
        'port'     => 0,
        'timeout'  => 3000,
    ],
],
```

## Adapter Vendors

HMS/Anybus (CC-Link Slave), Hilscher (netX), Moxa (MGate)

## Requirements

- PHP >= 8.1
- Composer
- erikwang2013/industrial-protocols-kernel

## Related Links

- [Industrial Protocols Main Project](https://github.com/erikwang2013/industrial-protocols)
- [Kernel](https://github.com/erikwang2013/industrial-protocols-kernel)
- [All 42 Protocol Packages](https://github.com/erikwang2013/industrial-protocols#supported-protocols)

## License

MIT — Copyright (c) 2026 erik <erik@erik.xyz> — https://erik.xyz
