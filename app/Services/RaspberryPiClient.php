<?php

namespace App\Services;

use RuntimeException;

class RaspberryPiClient
{
    protected $baseUrl;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            (string) config('pingpong.raspberry_url'),
            '/'
        );

        $this->timeout = max(
            1,
            (int) config('pingpong.request_timeout', 5)
        );

        if (
            $this->baseUrl === '' ||
            strpos($this->baseUrl, 'http://') !== 0
        ) {
            throw new RuntimeException(
                'Alamat Pico WH belum dikonfigurasi dengan benar.'
            );
        }

        if (!function_exists('curl_init')) {
            throw new RuntimeException(
                'Ekstensi cURL PHP belum aktif.'
            );
        }
    }

    public function status()
    {
        return $this->request('/status');
    }

    // START wajib membawa seluruh pengaturan latihan.
    public function start(array $settings = [])
    {
        $settings['action'] = 'start';

        return $this->send($settings);
    }

    public function stop()
    {
        return $this->send([
            'action' => 'stop'
        ]);
    }

    public function configure(array $settings)
    {
        $settings['action'] = 'configure';

        return $this->send($settings);
    }

    protected function request(
        $endpoint,
        $method = 'GET',
        $payload = null
    ) {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);

        $headers = [
            'Accept: application/json'
        ];

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ];

        if ($payload !== null) {
            $json = json_encode($payload);

            if ($json === false) {
                throw new RuntimeException(
                    'Gagal membuat data JSON untuk Pico WH.'
                );
            }

            $options[CURLOPT_POSTFIELDS] = $json;
            $options[CURLOPT_HTTPHEADER][] =
                'Content-Type: application/json';
        }

        curl_setopt_array($ch, $options);

        $body = curl_exec($ch);

        if ($body === false) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new RuntimeException(
                'Koneksi ke Pico WH gagal: ' . $error
            );
        }

        $httpStatus = curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

        curl_close($ch);

        if ($httpStatus < 200 || $httpStatus >= 300) {
            throw new RuntimeException(
                'Pico WH memberikan HTTP ' .
                $httpStatus . '. Respons: ' . $body
            );
        }

        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            throw new RuntimeException(
                'Respons Pico WH bukan JSON yang valid.'
            );
        }

        if (
            isset($decoded['success']) &&
            !$decoded['success']
        ) {
            throw new RuntimeException(
                isset($decoded['message'])
                    ? $decoded['message']
                    : 'Pico WH menolak perintah.'
            );
        }

        return $decoded;
    }

    public function send(array $command)
    {
        $action = isset($command['action'])
            ? $command['action']
            : '';

        if (!in_array(
            $action,
            ['start', 'configure', 'stop']
        )) {
            throw new RuntimeException(
                'Perintah Pico WH tidak dikenali.'
            );
        }

        // START dan CONFIGURE membutuhkan pengaturan lengkap.
        if (
            $action === 'start' ||
            $action === 'configure'
        ) {
            $required = [
                'mode',
                'speed',
                'interval_seconds',
                'direction'
            ];

            foreach ($required as $field) {
                if (
                    !isset($command[$field]) ||
                    $command[$field] === ''
                ) {
                    throw new RuntimeException(
                        'Pengaturan ' . $field .
                        ' belum tersedia.'
                    );
                }
            }
        }

        return $this->request(
            '/' . $action,
            'POST',
            $command
        );
    }
}
