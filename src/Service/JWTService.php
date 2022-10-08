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
        if($validity > 0) {
            // on créé un nouvel objet Date Immutable
            $now = new DateTimeImmutable();

            // La date d'expiration sera l'heure + le temps de validité
            $exp = $now->getTimestamp() + $validity;

            // iat = issued at
            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }

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

    // on vérifie que le token est valide (en terme de forme)
    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    // On récupère le Header
    public function getHeader(string $token): array
    {
        // On démonte le token
        // A chaque point on créé une valeur
        $array = explode('.', $token);

        // On décode le payload (donc la 2ème partie du tableau)
        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }

    // On récupère le Payload
    public function getPayload(string $token): array
    {
        // On démonte le token
        // A chaque point on créé une valeur
        $array = explode('.', $token);

        // On décode le payload (donc la 2ème partie du tableau)
        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    // On vérifie si le token a expiré
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        $now = new DateTimeImmutable();

        // Si ma date d'exp est inférieur à maintenant, c'est que c'est expiré
        return $payload['exp'] < $now->getTimestamp();
    }

    // On vérifie la signature du token
    public function check(string $token, string $secret)
    {
        // On récupère le header et payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        // On régénère un token
        $verifToken = $this->generate($header, $payload, $secret, 0);
        // on met 0 à validity afin de ne pas regénérer le payload et donc
        // éviter d'avoir des dates modifiées

        return $token === $verifToken;
    }
}