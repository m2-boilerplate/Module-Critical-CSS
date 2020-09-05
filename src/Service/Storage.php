<?php

namespace M2Boilerplate\CriticalCss\Service;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Asset\File;

class Storage
{

    const DIRECTORY = 'critical-css';

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $directory;

    /**
     * Storage constructor.
     *
     * @param \Magento\Framework\Filesystem $filesystem
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(\Magento\Framework\Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function clean()
    {
        $this->directory->delete(self::DIRECTORY);
    }

    /**
     * @param $identifier
     * @param $content
     *
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function saveCriticalCss(string $identifier, ?string $content): bool
    {
        $this->directory->create(self::DIRECTORY);
        $this->directory->writeFile(self::DIRECTORY.'/'.$identifier.'.css', $content);
        return true;
    }

    /**
     * @param $identifier
     *
     * @return null|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getCriticalCss($identifier): ?string
    {
        $file = self::DIRECTORY.'/'.$identifier.'.css';
        if (!$this->directory->isReadable($file)) {
            return null;
        }
        return $this->directory->readFile($file);
    }

    public function getFileSize($identifier): ?string
    {
        $file = self::DIRECTORY.'/'.$identifier.'.css';
        $stat = $this->directory->stat($file);
        if (!isset($stat['size'])) {
            return null;
        }

        return $stat['size'];
    }
}