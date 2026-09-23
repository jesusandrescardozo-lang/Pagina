<?php

class GoogleAuthenticatorService
{
    private static $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(int $length = 16): string
    {
        $secret = '';
        $charsLen = strlen(self::$base32Chars);
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::$base32Chars[random_int(0, $charsLen - 1)];
        }
        return $secret;
    }

    public static function getCode(string $secret, ?int $timeSlice = null): string
    {
        if ($timeSlice === null) {
            $timeSlice = (int)floor(time() / 30);
        }

        $secretKey = self::base32Decode($secret);

        // Pack 64-bit integer into big-endian bytes
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
        $hmac = hash_hmac('sha1', $time, $secretKey, true);

        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashPart = substr($hmac, $offset, 4);

        $value = unpack('N', $hashPart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;

        $modulo = 10 ** 6;
        return str_pad((string)($value % $modulo), 6, '0', STR_PAD_LEFT);
    }

    public static function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $code = trim($code);
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }

        $currentTimeSlice = (int)floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    public static function getOtpAuthUrl(string $issuer, string $accountName, string $secret): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($accountName) .
            '?secret=' . $secret .
            '&issuer=' . rawurlencode($issuer) .
            '&algorithm=SHA1&digits=6&period=30';
    }

    public static function getSecondsRemaining(): int
    {
        return 30 - (time() % 30);
    }

    private static function base32Decode(string $b32): string
    {
        $b32 = strtoupper(trim($b32));
        $lut = array_flip(str_split(self::$base32Chars));
        $buffer = 0;
        $bufferSize = 0;
        $binary = '';

        for ($i = 0; $i < strlen($b32); $i++) {
            $char = $b32[$i];
            if ($char === '=') break;
            if (!isset($lut[$char])) continue;

            $buffer = ($buffer << 5) | $lut[$char];
            $bufferSize += 5;

            if ($bufferSize >= 8) {
                $bufferSize -= 8;
                $binary .= chr(($buffer >> $bufferSize) & 0xFF);
            }
        }

        return $binary;
    }
}

// Test validation
$secret = GoogleAuthenticatorService::generateSecret();
$code = GoogleAuthenticatorService::getCode($secret);
$valid = GoogleAuthenticatorService::verifyCode($secret, $code);

echo "Secret: {$secret}\n";
echo "Current Code: {$code}\n";
echo "Seconds remaining: " . GoogleAuthenticatorService::getSecondsRemaining() . "s\n";
echo "Verify result: " . ($valid ? "SUCCESS" : "FAIL") . "\n";
echo "Url: " . GoogleAuthenticatorService::getOtpAuthUrl('REFUGIO-IO', 'admin@refugios.ve', $secret) . "\n";
