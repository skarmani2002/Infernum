<?php
/**
 * Infernum
 * Copyright (C) 2011 IceFlame.net
 *
 * Permission to use, copy, modify, and/or distribute this software for
 * any purpose with or without fee is hereby granted, provided that the
 * above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE
 * FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY
 * DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER
 * IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING
 * OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 *
 * @package  FlameCore\Infernum
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

namespace FlameCore\Infernum;

/**
 * Class for reading and storing cache instances
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Cache
{
    /**
     * The path to the cache directory
     *
     * @var string
     */
    private $path;

    /**
     * Creates a Cache object.
     *
     * @param string $path The path to the cache directory
     */
    public function __construct($path)
    {
        if (!is_writable($path))
            throw new \LogicException('The cache directory is not writable.');

        $this->path = $path;
    }

    /**
     * Reads data from the cache.
     *
     * @param string $name The name of the cache file
     * @return mixed
     */
    public function get($name)
    {
        if (!$this->validateName($name))
            throw new \InvalidArgumentException(sprintf('Given cache name "%s" is invalid.', $name));

        $rawdata = '';
        $expire = 0;
        $filename = $this->getFilename($name);

        if (!is_file($filename)) {
            return false;
        }

        $resource = fopen($filename, "r");

        if (($line = fgets($resource)) !== false) {
            $expire = (int) $line;
        }

        if ($expire != 0 && $expire < time()) {
            fclose($resource);
            return false;
        }

        while (($line = fgets($resource)) !== false) {
            $rawdata .= $line;
        }

        fclose($resource);

        return unserialize($rawdata);
    }

    /**
     * Writes data to the cache.
     *
     * @param string $name The name of the cache file
     * @param mixed $data The data to write
     * @param int $lifetime The lifetime of the cache file in seconds (0 = infinite)
     * @return bool
     */
    public function set($name, $data, $lifetime)
    {
        if (!$this->validateName($name))
            throw new \InvalidArgumentException(sprintf('Given cache name "%s" is invalid.', $name));

        $rawdata = serialize($data);
        $expire = time() + (int) $lifetime;
        $filename = $this->getFilename($name);

        if (strpos($name, '/') !== false) {
            $directory = dirname($filename);

            if (!is_dir($directory)) {
                if (false === @mkdir($directory, 0777, true) && !is_dir($directory)) {
                    return false;
                }
            } elseif (!is_writable($directory)) {
                return false;
            }
        }

        $tmpfile = tempnam($directory, $name);

        if ((file_put_contents($tmpfile, $expire.PHP_EOL.$rawdata) !== false) && @rename($tmpfile, $filename)) {
            @chmod($filename, 0666 & ~umask());

            return true;
        }

        return false;
    }

    /**
     * Returns whether the cache contains the file with given name.
     *
     * @param string $name The name of the cache file
     * @return bool
     */
    public function contains($name)
    {
        $expire = 0;
        $filename = $this->getFilename($name);

        if (!is_file($filename)) {
            return false;
        }

        $resource = fopen($filename, "r");

        if (($line = fgets($resource)) !== false) {
            $expire = (int) $line;
        }

        fclose($resource);

        return $expire == 0 || $expire >= time();
    }

    /**
     * Deletes the cache file with given name.
     *
     * @param string $name The name of the cache file
     * @return bool
     */
    public function delete($name)
    {
        return @unlink($this->getFilename($name));
    }

    /**
     * Clears the cache.
     *
     * @return void
     */
    public function clear()
    {
        $iterator = new RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $filename => $file) {
            if ($file->isDir()) {
                $this->clear($filename);
                rmdir($filename);
            } else {
                unlink($filename);
            }
        }
    }

    private function getFilename($name)
    {
        return $this->path.'/'.$name.'.dat';
    }

    private function validateName($name)
    {
        return preg_match('#^[\w-+@\./]+$#', $name) && $name[0] != '/';
    }
}
