# CC-Link 协议包 — RS-485 现场总线，主从轮询，CRC-16/XMODEM

> [English](README.en.md)

CC-Link (Control & Communication Link) RS-485 现场总线协议，主从轮询模式，CRC-16/XMODEM 校验。

## 安装

```bash
composer require erikwang2013/industrial-protocols-cclink
```

## 架构

CcLinkDriver（串口 RS-485）→ CcLinkFrame 帧编解码。主站模式（station=0），从站轮询。

## 功能

RS-485 串口通信（156000 bps）、主从轮询、CRC-16/XMODEM 校验、CcLinkException 异常

## 使用说明

```php
$conn = $kernel->getConnectionManager()->connect('cclink-device');
// CC-Link 主站轮询
```

## 配置示例

```php
'devices' => [
    'cclink-device' => [
        'protocol' => 'cc-link', 'variant' => 'rs485',
        'device' => '/dev/ttyUSB2',
        'baud_rate' => 156000, 'station' => 0,
        'timeout' => 3000,
    ],
],
```

## 兼容框架

Laravel / Webman / Hyperf / ThinkPHP / Yii2 / Yii3 / Plain PHP

## 系统要求

- PHP >= 8.1
- RS-485 接口
- erikwang2013/industrial-protocols-kernel

## License

MIT — Copyright (c) 2026 erik <erik@erik.xyz> — https://erik.xyz
