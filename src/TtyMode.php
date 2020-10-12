<?php

namespace gipfl\Cli;

use function escapeshellarg;
use function register_shutdown_function;
use function rtrim;
use function shell_exec;

class TtyMode
{
    protected $originalMode;

    public function preserve($force = false)
    {
        if ($force || $this->originalMode === null) {
            $this->originalMode = $this->getCurrentMode();
            register_shutdown_function([$this, 'restore']);
        }

        return $this;
    }

    public function setPreferredMode()
    {
        $this->preserve();
        $this->disableFeature('icanon', 'echo');

        return $this;
    }

    public function enableFeature(...$feature)
    {
        $this->preserve();
        $cmd = 'stty ';
        foreach ($feature as $f) {
            $cmd .= escapeshellarg($f);
        }

        shell_exec($cmd);
    }

    public function disableFeature(...$feature)
    {
        $this->preserve();
        $cmd = 'stty';
        foreach ($feature as $f) {
            $cmd .= ' -' . escapeshellarg($f);
        }

        shell_exec($cmd);
    }

    protected function getCurrentMode()
    {
        return rtrim(shell_exec('stty -g'), PHP_EOL);
    }

    /**
     * @internal
     */
    public function restore()
    {
        if ($this->originalMode) {
            shell_exec('stty ' . escapeshellarg($this->originalMode));
            $this->originalMode = null;
        }
    }
}
