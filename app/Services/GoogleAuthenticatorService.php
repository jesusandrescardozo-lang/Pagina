<?php

namespace App\Services;

/**
 * Servicio de Autenticación de Dos Factores (2FA) compatible con Google Authenticator.
 * Implementación nativa de Time-Based One-Time Password (TOTP) según RFC 6238 y HMAC-SHA1.
 */
class GoogleAuthenticatorService
{
    /**
     * Alfabeto estándar Base32 según RFC 3548 / RFC 4648.
     */
    private static string $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Genera una clave secreta Base32 criptográficamente segura para un usuario.
     */
    public static function generateSecret(int $length = 16): string
    {
        $secret = '';
        $charsLen = strlen(self::$base32Chars);
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::$base32Chars[random_int(0, $charsLen - 1)];
        }
        return $secret;
    }

    /**
     * Calcula el código numérico de 6 dígitos para la clave secreta y el intervalo de tiempo especificado.
     * Si no se indica $timeSlice, se utiliza el intervalo actual de 30 segundos: floor(time() / 30).
     */
    public static function getCode(string $secret, ?int $timeSlice = null): string
    {
        if ($timeSlice === null) {
            $timeSlice = (int)floor(time() / 30);
        }

        $secretKey = self::base32Decode($secret);

        // Empaquetar el contador de 64 bits en formato big-endian
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
        $hmac = hash_hmac('sha1', $time, $secretKey, true);

        // Truncamiento dinámico según RFC 4226 / RFC 6238
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashPart = substr($hmac, $offset, 4);

        $value = unpack('N', $hashPart);
        $value = $value[1] & 0x7FFFFFFF;

        $modulo = 10 ** 6;
        return str_pad((string)($value % $modulo), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica si un código de 6 dígitos ingresado coincide con la clave secreta del usuario.
     * $discrepancy define la cantidad de ventanas de 30s de tolerancia hacia adelante y atrás (default 1 = ±30 segundos).
     */
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

    /**
     * Construye el URI oficial 'otpauth://' para registrar la cuenta en Google Authenticator.
     */
    public static function getOtpAuthUrl(string $issuer, string $accountName, string $secret): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($accountName) .
            '?secret=' . rawurlencode($secret) .
            '&issuer=' . rawurlencode($issuer) .
            '&algorithm=SHA1&digits=6&period=30';
    }

    /**
     * Genera el código QR localmente en SVG (sin servicios externos).
     * El secreto 2FA NUNCA sale del servidor: la imagen se construye aquí.
     */
    public static function getQrCodeSvg(string $otpAuthUrl, int $size = 200): string
    {
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle($size),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );

        $writer = new \BaconQrCode\Writer($renderer);

        return $writer->writeString($otpAuthUrl);
    }

    /**
     * Retorna los segundos restantes (1 a 30) de la ventana de tiempo actual del código TOTP.
     */
    public static function getSecondsRemaining(): int
    {
        $sec = 30 - (time() % 30);
        return $sec === 0 ? 30 : $sec;
    }

    /**
     * Decodifica una cadena Base32 a bytes binarios.
     */
    public static function base32Decode(string $b32): string
    {
        $b32 = strtoupper(trim($b32));
        $lut = array_flip(str_split(self::$base32Chars));
        $buffer = 0;
        $bufferSize = 0;
        $binary = '';

        for ($i = 0; $i < strlen($b32); $i++) {
            $char = $b32[$i];
            if ($char === '=') {
                break;
            }
            if (!isset($lut[$char])) {
                continue;
            }

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
