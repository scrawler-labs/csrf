<?php

namespace Scrawler\Csrf;

class CSRF
{
    private $name;
    private $hashes;
    private $hashSize;
    private $inputName;
    private $maxHashes;

    /**
     * Initialize a CSRF instance.
     */
    public function __construct()
    {
        $this->name = 'scrawler-csrf-token';
        $this->inputName = 'csrf';
        $this->hashSize = 64;
        $this->maxHashes = 10;
        $this->_load();
    }

    /**
     * Generate a CSRF_Hash.
     *
     */
    private function generateHash(): string
    {
        // Generate new hash
        $hash = bin2hex(openssl_random_pseudo_bytes($this->hashSize / 2));
        // Save it
        array_unshift($this->hashes, $hash);
        if (0 === $this->clearHashes()) {
            $this->_save();
        }

        // Return hash info
        return $hash;
    }

    /**
     * Get the hashes of a context.
     *
     *
     * @return array array of hashes as strings
     */
    public function getHashes(): array
    {
        $len = count($this->hashes);
        $hashes = [];
        // Check in the hash list
        for ($i = $len - 1; $i >= 0 && $len > 0; --$i) {
            array_push($hashes, $this->hashes[$i]->get());
            --$len;
        }

        return $hashes;
    }

    /**
     * Clear the hashes of a context.
     *
     * @return int number of deleted hashes
     */
    public function clearHashes(): int
    {
        $deleted = 0;
        // Check in the hash list
        for ($i = count($this->hashes) - 1; $i >= $this->maxHashes; --$i) {
            array_splice($this->hashes, $i, 1);
            ++$deleted;
        }
        if ($deleted > 0) {
            $this->_save();
        }

        return $deleted;
    }

    /**
     * Generate an input html element.
     *     
     * @return string html input element code as a string
     */
    public function input(): string
    {
        // Generate hash
        $hash = $this->generateHash();

        // Generate html input string
        return '<input type="hidden" name="' . htmlspecialchars($this->inputName) . '" id="' . htmlspecialchars($this->inputName) . '" value="' . htmlspecialchars($hash) . '"/>';
    }

    /**
     * Generate a string hash.
     *
     * @return string hash as a string
     */
    public function string(): string
    {
        // Generate hash
        $hash = $this->generateHash();

        // Generate html input string
        return $hash;
    }

    /**
     * Validate by context.
     *
     * @return bool Valid or not
     */
    public function validate(): bool
    {
        // If hash was not given, find hash
        if (request()->has($this->inputName)) {
            $hash = request()->get($this->inputName);
        } else {
            return false;
        }

        // Check in the hash list
        for ($i = count($this->hashes) - 1; $i >= 0; --$i) {
            if (hash_equals($hash, $this->hashes[$i])) {
                array_splice($this->hashes, $i, 1);
                return true;
            }
        }
        return false;

    }

    /**
     * Load hash list.
     */
    private function _load()
    {
        $this->hashes = [];
        // If there are hashes on the session
        if (session()->has($this->name)) {
            // Load session hashes
            $this->hashes = unserialize(session()->get($this->name));
           
        }
    }

    /**
     * Save hash list.
     */
    private function _save()
    {
        session()->set($this->name, serialize($this->hashes));
    }
}
