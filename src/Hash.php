<?php

namespace Scrawler\Csrf;

class Hash
{
    private $hash;
    private $expire;

    /**
     * [__construct description].
     *
     */
    public function __construct($hashSize = 64)
    {
        // Generate hash
        $this->hash = $this->_generateHash($hashSize);
    }

    /**
     * The hash function to use.
     *
     * @param int $n Size in bytes
     *
     * @return string The generated hash
     */
    private function _generateHash(int $n): string
    {
        return bin2hex(openssl_random_pseudo_bytes($n / 2));
    }


    /**
     * Verify hash.
     */
    public function verify(string $hash): bool
    {
        if (hash_equals($hash, $this->hash)) {
            return true;
        }

        return false;
    }

    /**
     * Get hash.
     */
    public function get(): string
    {
        return $this->hash;
    }
}
