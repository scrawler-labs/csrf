<?php

namespace Scrawler\Csrf;

class CSRF
{
    private $name;
    private $hashes;
    private $hashTime2Live;
    private $hashSize;
    private $inputName;

    /**
     * Initialize a CSRF instance.
     */
    public function __construct()
    {
        // Session mods
        $this->name = '__scrawler-csrf';
        // Form input name
        $this->inputName = 'csrf';
        // Default time before expire for hashes
        $this->hashTime2Live = (int) \Safe\ini_get('session.gc_maxlifetime');
        // Default hash size
        $this->hashSize = 64;
        // Load hash list
        $this->_load();
    }

    /**
     * Generate a CSRF_Hash.
     *
     * @param int $time2Live  Seconds before expiration
     * @param int $max_hashes Clear old context hashes if more than this number
     */
    private function generateHash($time2Live = -1, $max_hashes = 10): Hash
    {
        // If no time2live (or invalid) use default
        if ($time2Live < 0) {
            $time2Live = $this->hashTime2Live;
        }
        // Generate new hash
        $hash = new Hash($time2Live, $this->hashSize);
        // Save it
        array_unshift($this->hashes, $hash);
        if (0 === $this->clearHashes($max_hashes)) {
            $this->_save();
        }

        // Return hash info
        return $hash;
    }

    /**
     * Get the hashes of a context.
     *
     * @param int $max_hashes max hashes to get
     *
     * @return array array of hashes as strings
     */
    public function getHashes($max_hashes = -1): array
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
     * @param int $max_hashes ignore first x hashes
     *
     * @return int number of deleted hashes
     */
    public function clearHashes($max_hashes = 0): int
    {
        $deleted = 0;
        // Check in the hash list
        for ($i = count($this->hashes) - 1; $i >= $max_hashes; --$i) {
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
     * @param int $time2Live  Seconds before expire
     * @param int $max_hashes Clear old context hashes if more than this number
     *
     * @return string html input element code as a string
     */
    public function input($time2Live = -1, $max_hashes = 5): string
    {
        // Generate hash
        $hash = $this->generateHash($time2Live, $max_hashes);

        // Generate html input string
        return '<input type="hidden" name="'.htmlspecialchars($this->inputName).'" id="'.htmlspecialchars($this->inputName).'" value="'.htmlspecialchars($hash->get()).'"/>';
    }

    /**
     * Generate a string hash.
     *
     * @param int $time2Live  Seconds before expire
     * @param int $max_hashes Clear old context hashes if more than this number
     *
     * @return string hash as a string
     */
    public function string($time2Live = -1, $max_hashes = 5): string
    {
        // Generate hash
        $hash = $this->generateHash($time2Live, $max_hashes);

        // Generate html input string
        return $hash->get();
    }

    /**
     * Validate by context.
     *
     * @return bool Valid or not
     */
    public function validate($hash = null): bool
    {
        // If hash was not given, find hash
        $input_name = $this->inputName;
        if (is_null($hash)) {
            if (request()->has($input_name)) {
                $hash = request()->get($input_name);
            } else {
                return false;
            }
        }

        // Check in the hash list
        for ($i = count($this->hashes) - 1; $i >= 0; --$i) {
            if ($this->hashes[$i]->verify($hash)) {
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
            $session_hashes = unserialize(session()->get($this->name));
            // Ignore expired
            for ($i = count($session_hashes) - 1; $i >= 0; --$i) {
                // If an expired found, the rest will be expired
                if ($session_hashes[$i]->hasExpire()) {
                    break;
                }
                array_unshift($this->hashes, $session_hashes[$i]);
            }
            if (count($this->hashes) != count($session_hashes)) {
                $this->_save();
            }
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
