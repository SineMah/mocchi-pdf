<?php

namespace Mocchi\Pdf;

use Mocchi\Pdf\Common\Exceptions\ImageMagick\ExistsException;
use Mocchi\Pdf\Common\Exceptions\ImageMagick\ReturnModeException;
use Mocchi\Pdf\Common\Exceptions\MetaException;

class Im {

    protected string $file;

    protected int $width = 0;
    protected int $height = 0;
    protected int $resolution = 0;
    protected int $pages = 0;

    const EXEC_MODE_ARRAY = 0;
    const EXEC_MODE_STRING = 1;

    public function __construct(string $file)
    {
        if(!$this->exists()) {

            throw new ExistsException('ImageMagick not found');
        }

        $this->file = $file;

        $this->loadMeta();
    }

    public function exists(): bool
    {
        exec('convert -version', $out);

        return count($out) > 0;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getResolution(): int
    {
        return $this->resolution;
    }

    public function getPages(): int
    {
        return $this->pages;
    }

    protected function loadMeta() {
        $cmd = 'convert ' . $this->file . ' -format "%w:%h:%x:%y:%n" info:';
        $out = $this->cmd($cmd);

        $meta = explode(':', $out);

        if(count($meta) < 5) {

            throw new MetaException('Check command: ' . $cmd);
        }

        $this->pages        = (int) array_pop($meta);

        $this->width        = (int) $meta[0];
        $this->height       = (int) $meta[1];
        $this->resolution   = (int) $meta[2];
    }

    public function cmd(string $command, int $execMode = self::EXEC_MODE_STRING)
    {
        $cmd = escapeshellcmd($command);
        $out = null;

        switch ($execMode) {

            case self::EXEC_MODE_STRING:
                $out = shell_exec($cmd);
                break;
            case self::EXEC_MODE_ARRAY:
                exec($cmd, $out);
                break;
            default:
                throw new ReturnModeException($execMode);
        }

        return $out;
    }
}