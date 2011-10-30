<?php
/**
 * File-based cache mamagement class, borrowed from CakePHP 2.0 ;)
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 */

class BwFileCache extends BwAbstractCache {

/**
 * Instance of SplFileObject class
 *
 * @var File
 */
	protected $_File = null;

/**
 * Settings
 *
 * - path = absolute path to cache directory, default => CACHE
 * - prefix = string prefix for filename, default => cake_
 * - lock = enable file locking on write, default => false
 * - serialize = serialize the data, default => true
 *
 * @var array
 * @see CacheEngine::__defaults
 */
	public $settings = array();

/**
 * True unless FileEngine::__active(); fails
 *
 * @var boolean
 */
	protected $_init = true;

/**
 * Initialize the Cache Engine
 *
 * Called automatically by the cache frontend
 * To reinitialize the settings call Cache::engine('EngineName', [optional] settings = array());
 *
 * @param array $settings array of setting for the engine
 * @return boolean True if the engine has been successfully initialized, false if not
 */
	public function init($settings = array()) {
		global $bwCacheDir;
		
		parent::init(array_merge(
			array(
				'engine' => 'File', 'path' => $bwCacheDir, 'prefix'=> 'cake_', 'lock'=> true,
				'serialize'=> true, 'isWindows' => false, 'mask' => 0664
			),
			$settings
		));

		if (DS === '\\') {
			$this->settings['isWindows'] = true;
		}
		if (substr($this->settings['path'], -1) !== DS) {
			$this->settings['path'] .= DS;
		}
		return $this->_active();
	}

/**
 * Garbage collection. Permanently remove all expired and deleted data
 *
 * @return boolean True if garbage collection was succesful, false on failure
 */
	public function gc() {
		return $this->clear(true);
	}

/**
 * Write data for key into cache
 *
 * @param string $key Identifier for the data
 * @param mixed $data Data to be cached
 * @param mixed $duration How long to cache the data, in seconds
 * @return boolean True if the data was successfully cached, false on failure
 */
	public function write($key, $data, $duration) {
		if ($data === '' || !$this->_init) {
			return false;
		}

		if ($this->_setKey($key, true) === false) {
			return false;
		}

		$lineBreak = "\n";

		if ($this->settings['isWindows']) {
			$lineBreak = "\r\n";
		}

		if (!empty($this->settings['serialize'])) {
			if ($this->settings['isWindows']) {
				$data = str_replace('\\', '\\\\\\\\', serialize($data));
			} else {
				$data = serialize($data);
			}
		}

		$expires = time() + $duration;
		$contents = $expires . $lineBreak . $data . $lineBreak;

		if ($this->settings['lock']) {
		    $this->_File->flock(LOCK_EX);
		}

		$this->_File->rewind();
		$success = $this->_File->ftruncate(0) && $this->_File->fwrite($contents) && $this->_File->fflush();

		if ($this->settings['lock']) {
		    $this->_File->flock(LOCK_UN);
		}

		return $success;
	}

/**
 * Read a key from the cache
 *
 * @param string $key Identifier for the data
 * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
 */
	public function read($key) {
		if (!$this->_init || $this->_setKey($key) === false) {
			return false;
		}

		if ($this->settings['lock']) {
			$this->_File->flock(LOCK_SH);
		}

		$this->_File->rewind();
		$time = time();
		$cachetime = intval($this->_File->current());

		if ($cachetime !== false && ($cachetime < $time || ($time + $this->settings['duration']) < $cachetime)) {
			if ($this->settings['lock']) {
				$this->_File->flock(LOCK_UN);
			}
			return false;
		}

		$data = '';
		$this->_File->next();
		while ($this->_File->valid()) {
			$data .= $this->_File->current();
			$this->_File->next();
		}

		if ($this->settings['lock']) {
			$this->_File->flock(LOCK_UN);
		}

		$data = trim($data);

		if ($data !== '' && !empty($this->settings['serialize'])) {
			if ($this->settings['isWindows']) {
				$data = str_replace('\\\\\\\\', '\\', $data);
			}
			$data = unserialize((string)$data);
		}
		return $data;
	}

/**
 * Delete a key from the cache
 *
 * @param string $key Identifier for the data
 * @return boolean True if the value was successfully deleted, false if it didn't exist or couldn't be removed
 */
	public function delete($key) {
		if ($this->_setKey($key) === false || !$this->_init) {
			return false;
		}
		$path = $this->_File->getRealPath();
		$this->_File = null;
		return unlink($path);
	}

/**
 * Delete all values from the cache
 *
 * @param boolean $check Optional - only delete expired cache items
 * @return boolean True if the cache was successfully cleared, false otherwise
 */
	public function clear($check) {
		if (!$this->_init) {
			return false;
		}
		$dir = dir($this->settings['path']);
		if ($check) {
			$now = time();
			$threshold = $now - $this->settings['duration'];
		}
		$prefixLength = strlen($this->settings['prefix']);
		while (($entry = $dir->read()) !== false) {
			if (substr($entry, 0, $prefixLength) !== $this->settings['prefix']) {
				continue;
			}
			if ($this->_setKey($entry) === false) {
				continue;
			}
			if ($check) {
				$mtime = $this->_File->getMTime();

				if ($mtime > $threshold) {
					continue;
				}

				$expires = (int)$this->_File->current();

				if ($expires > $now) {
					continue;
				}
			}
			$path = $this->_File->getRealPath();
			$this->_File = null;
			if (file_exists($path)) {
				unlink($path);
			}
		}
		$dir->close();
		return true;
	}

/**
 * Sets the current cache key this class is managing, and creates a writable SplFileObject
 * for the cache file the key is refering to.
 *
 * @param string $key The key
 * @param boolean $createKey Whether the key should be created if it doesn't exists, or not
 * @return boolean true if the cache key could be set, false otherwise
 */
	protected function _setKey($key, $createKey = false) {
		$path = new SplFileInfo($this->settings['path'] . $key);

		if (!$createKey && !$path->isFile()) {
			return false;
		}
		if (empty($this->_File) || $this->_File->getBaseName() !== $key) {
			$exists = file_exists($path->getPathname());
			try {
				$this->_File = $path->openFile('c+');
			} catch (Exception $e) {
				trigger_error($e->getMessage(), E_USER_WARNING);
				return false;
			}
			unset($path);

			if (!$exists && !chmod($this->_File->getPathname(), (int) $this->settings['mask'])) {
				trigger_error(sprintf(_(
					'Could not apply permission mask "%s" on cache file "%s"'),
					array($this->_File->getPathname(), $this->settings['mask'])), E_USER_WARNING);
			}
		}
		return true;
	}

/**
 * Determine is cache directory is writable
 *
 * @return boolean
 */
	protected function _active() {
		$dir = new SplFileInfo($this->settings['path']);
		if ($this->_init && !($dir->isDir() && $dir->isWritable())) {
			$this->_init = false;
			trigger_error(sprintf(_('%s is not writable'), $this->settings['path']), E_USER_WARNING);
			return false;
		}
		return true;
	}
}