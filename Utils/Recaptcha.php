<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Utils;

final class Recaptcha
{
    private string $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify?secret=%s&response=%s&remoteip=%s';

    public function __construct(
        private readonly string $secretKey
    ) {
    }

    public function responseIsValid(string $response, string $ip = ''): bool
    {
        if (empty($response)) {
            return false;
        }

        $url = sprintf($this->verifyUrl, $this->secretKey, $response, $ip);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $content = curl_exec($ch);
        curl_close($ch);

        $recaptchaValidation = json_decode($content, true);

        if (isset($recaptchaValidation['success']) && $recaptchaValidation['success'] === true) {
            return true;
        }

        return false;
    }
}
