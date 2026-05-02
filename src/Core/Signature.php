<?php

namespace App\Core;

use App\Core\EnvLoader;

/**
 * TikTok X-Gorgon Signature Generator
 * Converts the Python Signature algorithm to PHP OOP
 */
class Signature {

    /**
     * Đọc GORGON_KEY từ .env (không hardcode trong source code)
     */
    private function getKey(): array {
        $raw = EnvLoader::get('GORGON_KEY', 'DF,77,B9,40,B9,9B,84,83,D1,B9,CB,D1,F7,C2,B9,85,C3,D0,FB,C3');
        return array_map(fn($h) => hexdec(trim($h)), explode(',', $raw));
    }


    private string $params;
    private string $data;
    private string $cookies;

    public function __construct(string $params, string $data = '', string $cookies = '') {
        $this->params  = $params;
        $this->data    = $data;
        $this->cookies = $cookies;
    }

    private function md5Str(string $input): string {
        return md5($input);
    }

    private function reverseByte(int $n): int {
        $hex = sprintf('%02x', $n & 0xFF);
        return hexdec($hex[1] . $hex[0]);
    }

    /**
     * Generate X-Gorgon and X-Khronos headers
     * @return array<string, string>
     */
    public function generate(): array {
        $g  = $this->md5Str($this->params);
        $g .= !empty($this->data)    ? $this->md5Str($this->data)    : str_repeat('0', 32);
        $g .= !empty($this->cookies) ? $this->md5Str($this->cookies) : str_repeat('0', 32);
        $g .= str_repeat('0', 32);

        $timestamp = time();
        $payload   = [];

        // Build payload from MD5 chunks
        for ($i = 0; $i < 12; $i += 4) {
            $chunk = substr($g, $i * 8, 8);
            for ($j = 0; $j < 4; $j++) {
                $payload[] = hexdec(substr($chunk, $j * 2, 2));
            }
        }

        // Append fixed bytes
        array_push($payload, 0x0, 0x6, 0xB, 0x1C);

        // Append Unix timestamp bytes (big-endian)
        $payload[] = ($timestamp & 0xFF000000) >> 24;
        $payload[] = ($timestamp & 0x00FF0000) >> 16;
        $payload[] = ($timestamp & 0x0000FF00) >> 8;
        $payload[] = ($timestamp & 0x000000FF);

        // XOR with KEY from .env
        $KEY       = $this->getKey();
        $encrypted = [];
        for ($i = 0; $i < 20; $i++) {
            $encrypted[] = $payload[$i] ^ $KEY[$i];
        }

        // Bit-manipulation pass
        for ($i = 0; $i < 20; $i++) {
            $c = $this->reverseByte($encrypted[$i]);
            $d = $encrypted[($i + 1) % 20];
            // Reverse bits of XOR result
            $f = 0;
            $xor = $c ^ $d;
            for ($b = 0; $b < 8; $b++) {
                $f |= (($xor >> $b) & 1) << (7 - $b);
            }
            $h = (($f ^ 0xFFFFFFFF) ^ 0x14) & 0xFF;
            $encrypted[$i] = $h;
        }

        $signature = '';
        foreach ($encrypted as $byte) {
            $signature .= sprintf('%02x', $byte);
        }

        return [
            'X-Gorgon'  => '840280416000' . $signature,
            'X-Khronos' => (string)$timestamp,
        ];
    }
}
