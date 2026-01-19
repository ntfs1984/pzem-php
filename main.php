#!/usr/bin/php
<?php

// Функция для расчета CRC16 Modbus
// Function to calculate CRC

function crc16($data) {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($data); $i++) {
        $crc ^= ord($data[$i]);
        for ($j = 8; $j != 0; $j--) {
            if (($crc & 0x0001) != 0) {
                $crc >>= 1;
                $crc ^= 0xA001;
            } else {
                $crc >>= 1;
            }
        }
    }
    return pack('v', $crc); // Возвращает Low byte, High byte / Returns Low byte, High byte
}

$device = "/dev/ttyS0"; // Порт, к которому подключен PZEM / Port which is PZEM connected
$fd = dio_open($device, O_RDWR | O_NOCTTY | O_NONBLOCK);

if (!$fd) {
    die("Unable to open $device");
}

dio_tcsetattr($fd, [
    'baud'   => 9600,
    'bits'   => 8,
    'stop'   => 1,
    'parity' => 0
]);

$address = 0x01;
$request = pack('C*', $address, 0x04, 0x00, 0x00, 0x00, 0x0A);
$request .= crc16($request);
dio_write($fd, $request);
usleep(100000); 
$response = dio_read($fd, 25);
dio_close($fd);
$payload = substr($response, 3, 20);
$val = unpack('n*', $payload);

/**
 * PZEM-014 Регистры:
 * 1: Напряжение (0.1V)
 * 2-3: Ток (0.001A) - Low 16 + High 16
 * 4-5: Мощность (0.1W) - Low 16 + High 16
 * 6-7: Энергия (1Wh) - Low 16 + High 16
 * 8: Частота (0.1Hz)
 * 9: Power Factor (0.01)
 * 10: Аварийный статус
 */

$results = [
    'voltage'     => $val[1] / 10.0,
    'current'     => ($val[2] + ($val[3] << 16)) / 1000.0,
    'power'       => ($val[4] + ($val[5] << 16)) / 10.0,
    'energy'      => ($val[6] + ($val[7] << 16)),
    'frequency'   => $val[8] / 10.0,
    'pf'          => $val[9] / 100.0,
    'alarm'       => $val[10] == 0xFFFF ? 'Alarm' : 'OK'
];

  print_r ($results);

