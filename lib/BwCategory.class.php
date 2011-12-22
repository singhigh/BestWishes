<?php
class BwCategory
{
	public $id;
	public $name;
	public $isVisible;
	public $giftsCount = 0;
	private $giftListId;
	
	public $gifts;
	
	public function __construct($id = null)
	{
		if(!empty($id))
			$this->id = (int)$id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getGifts()
	{
		return $this->gifts;
	}

	public function load($id = null)
	{
		if(!empty($id))
			$this->id = (int)$id;
		if(empty($this->id))
			return false;

		// Try to read from the cache
		$result = BwCache::read('category_' . $this->id);
		if($result === false) {
			// Nothing in the cache
			$db = BwDatabase::getInstance();
			$queryParams = array(
				'tableName' => 'category',
				'queryType' => 'SELECT',
				'queryFields' => '*',
				'queryCondition' => 'id = :id',
				'queryValues' => array(
					array(
						'parameter' => ':id',
						'variable' => $this->id,
						'data_type' => PDO::PARAM_INT
					)
				)
			);
			if($db->prepareQuery($queryParams)) {
				$result = $db->fetch();
				$db->closeQuery();
				if($result === false)
					return $result;

				if(empty($result)) {
					return false;
				}

				$this->storeAttributes($result);
				// Store this in the cache
				BwCache::write('category_' . $this->id, $result);
				return true;
			} else {
				return false;
			}
		} else {
			// Use cache data
			$this->storeAttributes($result);
			return true;
		}
	}

	private function storeAttributes($sqlResult)
	{
		$this->id         = (int)$sqlResult['id'];
		$this->name       = $sqlResult['name'];
		$this->giftListId = (int)$sqlResult['gift_list_id'];
		$this->isVisible  = (bool)$sqlResult['is_visible'];

		$this->giftsCount = 0;
		$this->gifts = BwGift::getAllByCategoryId((int)$sqlResult['id']);
		if(!empty($this->gifts)) {
			$this->giftsCount = count($this->gifts);
		}
	}

	/**
	 *
	 */
	private function loadAllByListId($listId = null)
	{
		if(empty($listId))
			return false;

		// Try to read from the cache
		$results = BwCache::read('category_all_list_' . $listId);
		if($results === false) {
			// Nothing in the cache
			$db = BwDatabase::getInstance();
			$queryParams = array(
				'tableName' => 'category',
				'queryType' => 'SELECT',
				'queryFields' => '*',
				'queryCondition' => 'gift_list_id = :gift_list_id',
				'queryValues' => array(
					array(
						'parameter' => ':gift_list_id',
						'variable' => $listId,
						'data_type' => PDO::PARAM_INT
					)
				),
				'queryOrderBy' => 'name ASC'
			);
			if($db->prepareQuery($queryParams)) {
				$results = $db->fetchAll();
				$db->closeQuery();
				if($results === false)
					return $results;

				if(empty($results)) {
					return false;
				}
				
				$allCategories = array();
				foreach($results as $result) {
					$category = new self($result['id']);
					$category->storeAttributes($result);
					$allCategories[] = $category;
				}

				// Store this in the cache
				BwCache::write('category_all_list_' . $listId, $results);
				return $allCategories;
			} else {
				return false;
			}
		} else {
			// Use cache data
			$allCategories = array();
			foreach($results as $result) {
				$category = new self($result['id']);
				$category->storeAttributes($result);
				$allCategories[] = $category;
			}
			return $allCategories;
		}
	}

	public static function add($listId = null, $name = '') {

		if(empty($listId) || empty($name)) {
			return false;
		}

		if(self::checkExisting($listId, $name)) {
			return false;
		}
		
		$db = BwDatabase::getInstance();
		$queryParams = array(
			'tableName' => 'category',
			'queryType' => 'INSERT',
			'queryFields' => array(
				'gift_list_id' => ':gift_list_id',
				'name' => ':name',
			),
			'queryValues' => array(
				array(
					'parameter' => ':gift_list_id',
					'variable' => $listId,
					'data_type' => PDO::PARAM_INT
				),
				array(
					'parameter' => ':name',
					'variable' => $name,
					'data_type' => PDO::PARAM_INT
				)
			)
		);
		if($db->prepareQuery($queryParams)) {
			$result =  $db->exec();
			if($result) {
				// Empty cache
				BwCache::delete('category_all_list_' . $listId);
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function delete($listId = null, $catId = null) {

		if(empty($listId) || empty($catId)) {
			return false;
		}

		$category = new self($catId);
		if(!$category->load()) {
			return false;
		}

		if(!$category->giftListId == $listId) {
			return false;
		}

		$db = BwDatabase::getInstance();
		$queryParams = array(
			'tableName' => 'category',
			'queryType' => 'DELETE',
			'queryFields' => '',
			'queryCondition' => array(
				'gift_list_id = :gift_list_id',
				'id = :id'
			),
			'queryValues' => array(
				array(
					'parameter' => ':id',
					'variable' => $catId,
					'data_type' => PDO::PARAM_INT
				),
				array(
					'parameter' => ':gift_list_id',
					'variable' => $listId,
					'data_type' => PDO::PARAM_INT
				)
			),
			
		);
		if($db->prepareQuery($queryParams)) {
			$result =  $db->exec();
			if($result) {
				// Empty cache
				BwCache::delete('category_all_list_' . $listId);
				// TODO: delete the gifts too
				BwCache::delete('gift_all_list_' . $listId);
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function checkExisting($listId = null, $name = '') {

		if(empty($listId) || empty($name))
			return false;

		$queryParams = array(
			'tableName' => 'category',
			'queryType' => 'SELECT',
			'queryFields' => 'COUNT(id) as count_existing',
			'queryCondition' => array(
				'gift_list_id = :gift_list_id',
				'name = :name'
			),
			'queryValues' => array(
				array(
					'parameter' => ':gift_list_id',
					'variable' => $listId,
					'data_type' => PDO::PARAM_INT
				),
				array(
					'parameter' => ':name',
					'variable' => $name,
					'data_type' => PDO::PARAM_STR
				)
			),
			'queryLimit' => 1
		);
		$db = BwDatabase::getInstance();
		if($db->prepareQuery($queryParams)) {
			$result = $db->fetch();
			$db->closeQuery();
			if($result === false)
				return $result;

			if(empty($result)) {
				return false;
			}
			return (intval($result['count_existing']) != 0);
		} else {
			return false;
		}
	}

	/**
	 *
	 */
	public static function getAllByListId($listId = null)
	{
		if(empty($listId))
			return false;

		$category = new self();
		return $category->loadAllByListId((int)$listId);
	}
}