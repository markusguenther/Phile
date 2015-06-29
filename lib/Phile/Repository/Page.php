<?php
/**
 * the page repository class
 */
namespace Phile\Repository;

use Phile\Core\Registry;
use Phile\Core\ServiceLocator;
use Phile\Core\Utility;

/**
 * the Repository class for pages
 *
 * @author  Frank Nägler
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Repository
 */
class Page {
	/**
	 * @var array the settings array
	 */
	protected $settings;

	/**
	 * @var array object storage for initialized objects, to prevent multiple loading of objects.
	 */
	protected $storage = array();

	/**
	 * @var \Phile\ServiceLocator\CacheInterface the cache implementation
	 */
	protected $cache = null;

	/**
	 * the constructor
	 */
	public function __construct($settings = null) {
		if ($settings === null) {
			$settings = Registry::get('Phile_Settings');
		}
		$this->settings = $settings;
		if (ServiceLocator::hasService('Phile_Cache')) {
			$this->cache = ServiceLocator::getService('Phile_Cache');
		}
	}

	/**
	 * find a page by path
	 *
	 * @param string $pageId
	 * @param string $folder
	 *
	 * @return null|\Phile\Model\Page
	 */
	public function findByPath($pageId, $folder = CONTENT_DIR) {
		// be merciful to lazy third-party-usage and accept a leading slash
		$pageId = ltrim($pageId, '/');
		// 'sub/' should serve page 'sub/index'
		if ($pageId === '' || substr($pageId, -1) === '/') {
			$pageId .= 'index';
		}

		$file = $folder . $pageId . CONTENT_EXT;
		if (!file_exists($file)) {
			if (substr($pageId, -6) === '/index') {
				// try to resolve sub-directory 'sub/' to page 'sub'
				$pageId = substr($pageId, 0, strlen($pageId) - 6);
			} else {
				// try to resolve page 'sub' to sub-directory 'sub/'
				$pageId .= '/index';
			}
			$file = $folder . $pageId . CONTENT_EXT;
		}
		if (!file_exists($file)) {
			return null;
		}
		return $this->getPage($file, $folder);
	}

	/**
	 * find all pages (*.md) files and returns an array of Page models
	 *
	 * @param array  $options
	 * @param string $folder
	 *
	 * @return PageCollection of \Phile\Model\Page objects
	 */
	public function findAll(array $options = array(), $folder = CONTENT_DIR) {
		$options += $this->settings;
        return new PageCollection($options, $folder, $this);
	}

	/**
	 * return page at offset from $page in applied search order
	 *
	 * @param \Phile\Model\Page $page
	 * @param int $offset
	 * @return null|\Phile\Model\Page
	 */
	public function getPageOffset(\Phile\Model\Page $page, $offset = 0) {
		$pages = $this->findAll();
		$order = array();
		foreach ($pages as $p) {
			$order[] = $p->getFilePath();
		}
		$key = array_search($page->getFilePath(), $order) + $offset;
		if (!isset($order[$key])) {
			return null;
		}
		return $this->getPage($order[$key]);
	}

	/**
	 * get page from cache or filepath
	 *
	 * @param        $filePath
	 * @param string $folder
	 *
	 * @return mixed|\Phile\Model\Page
	 */
	protected function getPage($filePath, $folder = CONTENT_DIR) {
		$key = 'Phile_Model_Page_' . md5($filePath);
		if (isset($this->storage[$key])) {
			return $this->storage[$key];
		}

		if ($this->cache !== null) {
			if ($this->cache->has($key)) {
				$page = $this->cache->get($key);
			} else {
				$page = new \Phile\Model\Page($filePath, $folder);
				$this->cache->set($key, $page);
			}
		} else {
			$page = new \Phile\Model\Page($filePath, $folder);
		}
		$this->storage[$key] = $page;

		return $page;
	}

}
