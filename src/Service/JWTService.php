<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    // On génère le token
    
    public function generate(
        array $header,
        array $payload,
        string $secret,
        int $validity = 10800
    ): string
    {
        if($validity <= 0) {
            return "";
        }

        // on créé un nouvel objet Date Immutable
        $now = new DateTimeImmutable();

        // La date d'expiration sera l'heure + le temps de validité
        $exp = $now->getTimestamp() + $validity;

        // iat = issued at
        $payload['iat'] = $now->getTimestamp();
        $payload['exp'] = $exp;

        // On encode en base 64
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        // On 'nettoie' les valeurs encodées (retrait des +, / et =)
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);


        // On génère la signature avec un secret
        // (pour des raisons de sécurité on le faire dans .env.local)
        $secret = base64_encode($secret);
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);

        $base64Signature = base64_encode($signature);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

        // On créé le token :
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

        return $jwt;
    }
}