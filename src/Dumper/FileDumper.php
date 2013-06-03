<?php

namespace Dumper;

/**
 * FileDumper
 */
class FileDumper
{
    private $outputPath;

    /** @var \ZipArchive */
    private $zip;

    public function __construct($outputPath)
    {
        if (!extension_loaded('zip')) {
            throw new \RuntimeException('The "zip" php extension must be loaded.' . "\n");
        }

        if (is_file($outputPath)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" already exists.', $outputPath));
        }

        $outputDir = dirname($outputPath);

        if (!is_dir($outputDir) || !is_writable($outputDir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $outputDir));
        }

        $this->outputPath = $outputPath;
    }

    public function dump(array $stuff)
    {
        $this->zip = new \ZipArchive();

        if (true !== $this->zip->open($this->outputPath, \ZIPARCHIVE::CREATE)) {
            throw new \RuntimeException(sprintf('Failed to initialize a zip file as "%s".', $this->outputPath));
        }

        $this->log(sprintf('Initialied a zip into "%s".', $this->outputPath));

        foreach ($stuff as $fromPath) {
            if (is_array($fromPath)) {
                list($saveName) = array_values($fromPath);
                list($fromPath) = array_keys($fromPath);
            } else {
                $saveName = basename($fromPath);
            }
            $this->pack($fromPath, $saveName);
        }

        $this->zip->close();

        $this->log('Mission Complete!');
    }

    /**
     * Packs a file to zip.
     *
     * @param string $fromPath
     * @param string $saveName
     */
    private function pack($fromPath, $saveName)
    {
        if (is_dir($fromPath)) {
            if (true === $this->zip->addEmptyDir($saveName)) {
                $this->logSection('>> dir+ ', $saveName);
                foreach (glob($fromPath . '/*') as $path) {
                    $this->pack($path, $saveName . '/' . basename($path));
                }
            } else {
                $this->logSection('>> skip ', sprintf('Failed to add a directory "%s"', $saveName));
            }
        } else {
            if (true === $this->zip->addFile($fromPath, $saveName)) {
                $this->logSection('>> file+', $saveName);
            } else {
                $this->logSection('>> skip ', sprintf('Failed to add a file "%s"', $saveName));
            }
        }
    }

    protected function log($message)
    {
        echo $message, "\n";
    }

    protected function logSection($section, $message)
    {
        $this->log(sprintf("%s\t%s", $section, $message));
    }
}
