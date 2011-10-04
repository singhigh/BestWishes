<?php
class BwClassAutoloader
{
	protected static $instance;

	public function __construct()
	{
		spl_autoload_register(array($this, 'loader'));
	}

	private function loader($className) {
		global $bwLibDir;

		$fileToLoad = $bwLibDir . DS . $className . '.class.php';
		if(!is_file($fileToLoad)) {
			exit('<span style="color: #ff0000">Could not load file ' . $fileToLoad . '</span>');
		}
		require($fileToLoad);
	}

	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new BwClassAutoloader();
		}
		return self::$instance;
  }
}