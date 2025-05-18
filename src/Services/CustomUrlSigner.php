<?php
namespace App\Services;

use CoopTilleuls\UrlSignerBundle\UrlSigner\AbstractUrlSigner;

class CustomUrlSigner extends AbstractUrlSigner
{
    public static function getName(): string
    {
        return 'simpler_signer';
    }

    protected function createSignature(string $url, string $expiration, string $signatureKey): string
    {
        return substr(hash_hmac('sha256', "{$url}::{$expiration}", $signatureKey), 0, 10);
    }
}