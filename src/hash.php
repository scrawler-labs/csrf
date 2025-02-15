<?php

namespace Scrawler\Csrf;

class Hash
{
    private $hash;
    private $expire;

    /**
     * [__construct description].
     *
     * @param int $time2Live Number of seconds before expiration
     */
    public function __construct($time2Live = 0, $hashSize = 64)
    {
        // Generate hash
        $this->hash = $this->_generateHash($hashSize);

        // Set expiration time
        if ($time2Live > 0) {
            $this->expire = time() + $time2Live;
        } else {
            $this->expire = 0;
        }
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
     * Check if hash has expired.
     */
    public function hasExpire(): bool
    {
        if (0 === $this->expire || $this->expire > time()) {
            return false;
        }

        return true;
    }

    /**
     * Verify hash.
     */
    public function verify(string $hash, string $context = ''): bool
    {
        if (!$this->hasExpire() && hash_equals($hash, $this->hash)) {
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
