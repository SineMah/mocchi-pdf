<?php

namespace Mocchi\Pdf;

use Mocchi\Pdf\Common\Exceptions\FileDeleteException;
use Mocchi\Pdf\Common\Exceptions\FileExistsException;
use Mocchi\Pdf\Common\Exceptions\FunctionMissingException;
use Mocchi\Pdf\Common\Exceptions\ImageMagick\NotSupportedTypeException;

/**
 * @method File getPages()
 * @method File getWidth()
 * @method File getHeight()
 * @method File getResolution()
 */
class File {

    protected string $file;
    protected Im $im;

    protected $types = [
        'jpg',
        'png'
    ];

    public function __construct(string $file)
    {
        $this->file = $file;

        if(!$this->exists()) {

            throw new FileExistsException($this->file);
        }

        $this->im = new Im($this->file);
    }

    public function exists(): bool
    {
        return file_exists($this->file);
    }

    public function convert(array $options=[]): bool
    {
        $info = pathinfo($this->file);
        $defaults = [
            'width'         => $this->getWidth(),
            'height'        => $this->getHeight(),
            'resolution'    => $this->getResolution(),
            'type'          => 'jpg',
            'quality'       => '80',
            'path'          => $info['dirname'] . '/',
            'file_name'     => $info['filename'],
            'page'          => ($this->getPages() > 0) ? 0 : $this->getPages(),
        ];
        $mergedOptions = array_merge($defaults, $options);

        if(!in_array($mergedOptions['type'], $this->types)) {

            throw new NotSupportedTypeException($mergedOptions['type']);
        }

        if(isset($options['height']) && !isset($options['width'])) {

            $this->reCalc($mergedOptions, $defaults, 'height');
        }

        if(isset($options['width']) && !isset($options['height'])) {

            $this->reCalc($mergedOptions, $defaults, 'width');
        }

        return $this->convertFile($mergedOptions);
    }

    public function __call($name, $args)
    {
        if(method_exists($this->im, $name)) {

            return $this->im->$name();
        }

        throw new FunctionMissingException($name);
    }

    protected function reCalc(array &$options, array $defaults, string $base): void
    {
        switch ($base) {
            case 'width':
                $width = $options[$base];
                $options['height'] = (int) (($defaults['height']*$width)/$defaults['width']);
                break;
            case 'height':
                $height = $options[$base];
                $options['width'] = (int) (($defaults['width']*$height)/$defaults['height']);
                break;
        }
    }

    protected function convertFile($options): bool
    {
        $target = $options['path'] . $options['file_name'] . '.' . $options['type'];

        if(file_exists($target)) {

            $deleted = unlink($target);

            if(!$deleted) {

                throw new FileDeleteException($target);
            }
        }

        $cmd = sprintf(
            'convert %s[%d] -resize %dx%d -density %d -quality %d %s',
            $this->file,
            $options['page'],
            $options['width'],
            $options['height'],
            $options['resolution'],
            $options['quality'],
            $target
        );

        $this->im->cmd($cmd);

        return file_exists($target);
    }
}