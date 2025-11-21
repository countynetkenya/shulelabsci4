<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Jwt_service
{
    protected $secret;
    protected $algorithm;
    protected $issuer;
    protected $ttl;
    protected $leeway;

    public function __construct()
    {
        $CI =& get_instance();
        $settings = $CI->config->item('shulelabs');

        $jwtConfig = isset($settings['jwt']) ? $settings['jwt'] : [];
        $this->secret = isset($jwtConfig['secret']) ? $jwtConfig['secret'] : 'please-change-me';
        $this->algorithm = isset($jwtConfig['algorithm']) ? $jwtConfig['algorithm'] : 'HS256';
        $this->issuer = isset($jwtConfig['issuer']) ? $jwtConfig['issuer'] : 'shulelabs.local';
        $this->ttl = isset($jwtConfig['ttl']) ? (int) $jwtConfig['ttl'] : 3600;
        $this->leeway = isset($jwtConfig['leeway']) ? (int) $jwtConfig['leeway'] : 0;
        JWT::$leeway = $this->leeway;
    }

    public function encode(array $claims)
    {
        $now = time();
        $defaults = [
            'iss' => $this->issuer,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->ttl,
        ];

        $payload = array_merge($defaults, $claims);
        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function decode($token)
    {
        if (empty($token)) {
            throw new InvalidArgumentException('Missing JWT token.');
        }

        $key = new Key($this->secret, $this->algorithm);
        JWT::$leeway = $this->leeway;
        $decoded = JWT::decode($token, $key);
        return (array) $decoded;
    }

    public function decodeFromHeader($authorizationHeader)
    {
        $token = $this->extractToken($authorizationHeader);
        return $this->decode($token);
    }

    public function extractToken($authorizationHeader)
    {
        if (empty($authorizationHeader)) {
            return null;
        }

        if (preg_match('/Bearer\s+(\S+)/i', $authorizationHeader, $matches)) {
            return $matches[1];
        }

        return $authorizationHeader;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function getLeeway()
    {
        return $this->leeway;
    }
}
