<?php

namespace Janfish\Upload;

/**
 * Class File
 * @package Janfish\Upload
 */
class File
{
    /**
     * @var int
     */
    private $_chunkQuantity = 0;

    /**
     * @var int
     */
    private $_currentChunk = 0;

    /**
     * @var
     */
    private $_sessionKey;

    /**
     * File constructor.
     * @param int $chunkQuantity
     */
    public function __construct(int $chunkQuantity)
    {
        $this->_chunkQuantity = $chunkQuantity;
    }

    /**
     * @param string $key
     */
    public function setSessionKey(string $key)
    {
        $this->_sessionKey = $key;
    }

    /**
     * @param int $chunkNo
     * @param string $tempPath
     * @param string $savePath
     * @return bool
     */
    public function append(int $chunkNo, string $tempPath, string $savePath)
    {
        $this->_currentChunk = $chunkNo;
        $dir = dirname($savePath);

        if (!@move_uploaded_file($tempPath, $dir . '/_' . $this->_currentChunk . '_' . $this->_sessionKey)) {

            return false;
        }

        if ($this->isFinished()) {
            return $this->mergeFile($dir, $savePath);
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->_currentChunk === $this->_chunkQuantity;
    }

    /**
     * @param string $chunkDir
     * @param string $savePath
     * @return bool
     */
    private function mergeFile(string $chunkDir, string $savePath): bool
    {
        $handle = fopen($savePath, 'w+');
        for ($i = 1; $i <= $this->_chunkQuantity; $i++) {
            if (@fwrite($handle, file_get_contents($chunkDir . '/_' . $i . '_' . $this->_sessionKey)) === false) {
                return false;
            }
        }
        fclose($handle);
        $this->removeChunk($chunkDir);
        return true;
    }

    /**
     * @param $chunkDir
     * @return bool
     */
    private function removeChunk($chunkDir): bool
    {
        for ($i = 1; $i <= $this->_chunkQuantity; $i++) {
            @unlink($chunkDir . '/_' . $i . '_' . $this->_sessionKey);
        }
        return true;
    }


}


