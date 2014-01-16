<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_LockFile
{
    protected $id = null;
    protected $modelName = null;
    private $isLocked = null;
    private $lockFile = null;

    public function __construct()
    {
        $this->modelName = 'M2ePro/LockFile';
    }

    public function __destruct()
    {
        if (is_resource($this->lockFile)) {
            fclose($this->lockFile);
        }
    }

    public function setId($id)
    {
        $this->id = (string)$id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function isEnabled()
    {
        return true;
    }

    public function lock()
    {
        if (!$this->getId()) {
            return $this;
        }

        $this->isLocked = true;
        flock($this->getLockFile(), LOCK_EX | LOCK_NB);
        return $this;
    }

    public function lockAndBlock()
    {
        if (!$this->getId()) {
            return $this;
        }

        $this->isLocked = true;
        flock($this->getLockFile(), LOCK_EX);
        return $this;
    }

    public function unlock()
    {
        if (!$this->getId()) {
            return $this;
        }

        $this->isLocked = false;
        flock($this->getLockFile(), LOCK_UN);
        return $this;
    }

    public function isLocked()
    {
        if ($this->isLocked !== null) {
            return $this->isLocked;
        } else {
            $fp = $this->getLockFile();
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                flock($fp, LOCK_UN);
                return false;
            }
            return true;
        }
    }

    private function getLockFile()
    {
        if ($this->lockFile === null) {
            $varDirectory = Mage::getConfig()->getVarDir('locks');

            if (!is_dir($varDirectory) || !is_writable($varDirectory)) {
                throw new Exception(sprintf('Permission denied for write to "%s".', $varDirectory));
            }

            $file = $varDirectory . DS . 'm2epro_'.$this->getId().'.lock';
            $this->lockFile = fopen($file, 'w');
            fwrite($this->lockFile, date('r'));
        }
        return $this->lockFile;
    }

    public function registerShutdownFunction()
    {
        $code = '
$lockFile = Mage::getSingleton(\''.$this->modelName.'\');
if ($lockFile->getId()) {
    $lockFile->unlock();
}
';

        $shutdownDeleteFunction = create_function('', $code);
        register_shutdown_function($shutdownDeleteFunction);
    }
}