<?php
class BwUserParams
{
	public $listId;
	public $userId;
	public $canView;
	public $canEdit;
	public $canMark;
	public $alertAddition;
	public $alertPurchase;

	public function __construct()
	{
		if(!empty($id)) {
			$this->id = (int)$id;
			$this->load($this->id);
		}
	}

	public function loadByUserIdListId($userId = null, $listId = null)
	{
		if(empty($userId) || empty($listId))
			return false;
		
		$db = BwDatabase::getInstance();
		$queryParams = array(
			'tableName' => 'list_user_params',
			'queryType' => 'SELECT',
			'queryFields' => '*',
			'queryCondition' => array(
				'gift_list_user_id = :gift_list_user_id',
				'gift_list_id = :gift_list_id'
			),
			'queryValues' => array(
				array(
					'parameter' => ':gift_list_user_id',
					'variable' => $userId,
					'data_type' => PDO::PARAM_INT
				),
				array(
					'parameter' => ':gift_list_id',
					'variable' => $listId,
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
			return true;
		}
		return false;
	}

	public function loadByUserId($userId = null)
	{
		if(empty($userId))
			return false;
		
		// Try to read from the cache
		$results = BwCache::read('user_param_' . $userId);
		if($results === false) {
			$db = BwDatabase::getInstance();
			$queryParams = array(
				'tableName' => 'list_user_params',
				'queryType' => 'SELECT',
				'queryFields' => '*',
				'queryCondition' => 'gift_list_user_id = :gift_list_user_id',
				'queryValues' => array(
					array(
						'parameter' => ':gift_list_user_id',
						'variable' => $userId,
						'data_type' => PDO::PARAM_INT
					)
				)
			);
			if($db->prepareQuery($queryParams)) {
				$results = $db->fetchAll();
				$db->closeQuery();
				if($results === false)
					return $results;
				
				if(empty($results)) {
					return false;
				}

				$userParams = array();
				foreach($results as $result) {
					$userParam = new self();
					$userParam->storeAttributes($result);
					$userParams[intval($result['gift_list_id'])] = $userParam;
				}

				// Store this in the cache
				BwCache::write('user_param_' . $userId, $results);
				return $userParams;
			} else {
				return false;
			}
		} else {
			// Use cache data
			foreach($results as $result) {
				$userParam = new self();
				$userParam->storeAttributes($result);
				$userParams[intval($result['gift_list_id'])] = $userParam;
			}
			return $userParams;
		}
	}

	/**
	 *
	 */
	public static function deleteByListId($listId = null, $userId = null) {
		$resultValue = 99;
		if(empty($listId) || empty($userId)) {
			return $resultValue;
		}

		$db = BwDatabase::getInstance();
		$queryParams = array(
			'tableName' => 'list_user_params',
			'queryType' => 'DELETE',
			'queryFields' => '',
			'queryCondition' => array(
				'gift_list_id = :gift_list_id',
			),
			'queryValues' => array(
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
				// Clear cache
				BwCache::delete('user_param_' . $userId);
				$resultValue = 0;
			} else {
				$resultValue = 1;
			}
		}
		return $resultValue;
	}

	/**
	 *
	 */
	public static function deleteByUserId($userId = null) {
		$resultValue = 99;
		if(empty($userId)) {
			return $resultValue;
		}

		$db = BwDatabase::getInstance();
		$queryParams = array(
			'tableName' => 'list_user_params',
			'queryType' => 'DELETE',
			'queryFields' => '',
			'queryCondition' => array(
				'gift_list_user_id = :gift_list_user_id',
			),
			'queryValues' => array(
				array(
					'parameter' => ':gift_list_user_id',
					'variable' => $userId,
					'data_type' => PDO::PARAM_INT
				)
			),
			
		);
		if($db->prepareQuery($queryParams)) {
			$result =  $db->exec();
			if($result) {
				// Clear cache
				BwCache::delete('user_param_' . $userId);
				$resultValue = 0;
			} else {
				$resultValue = 1;
			}
		}
		return $resultValue;
	}

	/**
	 *
	 */
	public static function addByListId($listId = null, $ownerId = null)
	{
		$resultValue = 99;
		if(empty($listId) || empty($ownerId)) {
			return $resultValue;
		}

		$allUsers = BwUser::getAll();
		foreach($allUsers as $anUser) {
			if($anUser->getId() == $ownerId) {
				$canEdit = 1;
				$canMark = 0;
			} else {
				$canEdit = 0;
				$canMark = 1;
			}
			$db = BwDatabase::getInstance();
			$queryParams = array(
				'tableName' => 'list_user_params',
				'queryType' => 'INSERT',
				'queryFields' => array(
					'gift_list_id' => ':gift_list_id',
					'gift_list_user_id' => ':gift_list_user_id',
					'can_view' => ':can_view',
					'can_edit' => ':can_edit',
					'can_mark' => ':can_mark',
					'alert_addition' => ':alert_addition',
					'alert_purchase' => ':alert_purchase',
				),
				'queryValues' => array(
					array(
						'parameter' => ':gift_list_id',
						'variable' => $listId,
						'data_type' => PDO::PARAM_INT
					),
					array(
						'parameter' => ':gift_list_user_id',
						'variable' => $anUser->getId(),
						'data_type' => PDO::PARAM_INT
					),
					array(
						'parameter' => ':can_view',
						'variable' => 1,
						'data_type' => PDO::PARAM_INT
					),
					array(
						'parameter' => ':can_edit',
						'variable' => $canEdit,
						'data_type' => PDO::PARAM_INT
					),
					array(
						'parameter' => ':can_mark',
						'variable' => $canMark,
						'data_type' => PDO::PARAM_INT
					),
					array(
						'parameter' => ':alert_addition',
						'variable' => 0,
						'data_type' => PDO::PARAM_INT
					),
					array(
						'parameter' => ':alert_purchase',
						'variable' => 0,
						'data_type' => PDO::PARAM_INT
					)
				)
			);
			if($db->prepareQuery($queryParams)) {
				$result =  $db->exec();
				if($result) {
					// All OK
					$resultValue = 0;
					BwCache::delete('user_param_' . $anUser->getId());
				} else {
					return $resultValue;
				}
			} else {
				return $resultValue;
			}
		}
		return $resultValue;
	}


	/**
	 *
	 */
	public static function addByUserId($userId = null)
	{
		$resultValue = 99;
		if(empty($userId)) {
			return $resultValue;
		}

		$allLists = BwList::getAll();
		if(!empty($allLists)) {
			foreach($allLists as $aList) {
				$db = BwDatabase::getInstance();
				$queryParams = array(
					'tableName' => 'list_user_params',
					'queryType' => 'INSERT',
					'queryFields' => array(
						'gift_list_id' => ':gift_list_id',
						'gift_list_user_id' => ':gift_list_user_id',
						'can_view' => ':can_view',
						'can_edit' => ':can_edit',
						'can_mark' => ':can_mark',
						'alert_addition' => ':alert_addition',
						'alert_purchase' => ':alert_purchase',
					),
					'queryValues' => array(
						array(
							'parameter' => ':gift_list_id',
							'variable' => $aList->getId(),
							'data_type' => PDO::PARAM_INT
						),
						array(
							'parameter' => ':gift_list_user_id',
							'variable' => $userId,
							'data_type' => PDO::PARAM_INT
						),
						array(
							'parameter' => ':can_view',
							'variable' => 1,
							'data_type' => PDO::PARAM_INT
						),
						array(
							'parameter' => ':can_edit',
							'variable' => 0,
							'data_type' => PDO::PARAM_INT
						),
						array(
							'parameter' => ':can_mark',
							'variable' => 0,
							'data_type' => PDO::PARAM_INT
						),
						array(
							'parameter' => ':alert_addition',
							'variable' => 0,
							'data_type' => PDO::PARAM_INT
						),
						array(
							'parameter' => ':alert_purchase',
							'variable' => 0,
							'data_type' => PDO::PARAM_INT
						)
					)
				);
				if($db->prepareQuery($queryParams)) {
					$result =  $db->exec();
					if($result) {
						// All OK
						$resultValue = 0;
						BwCache::delete('user_param_' . $userId);
					} else {
						return $resultValue;
					}
				} else {
					return $resultValue;
				}
			}
		} else {
			// All OK, but nothing to do
			$resultValue = 0;
		}
		return $resultValue;
	}

	/**
	 *
	 */
	public function updateRight($userId = null, $listId = null, $rightType = '', $enabled = false)
	{
		$resultCode = 99;
		if(empty($userId) || empty($listId) || empty($rightType)) {
			return $resultCode;
		}
		$rightValue = 0;
		if($enabled) {
			$rightValue = 1;
		}
		$paramFieldData = array(
			'variable' => $rightValue,
			'data_type' => PDO::PARAM_INT
		);
		switch($rightType) {
			case 'can_view':
			case 'can_edit':
			case 'can_mark':
			case 'alert_addition':
			case 'alert_purchase':
				$paramFieldData['parameter'] = ':' . $rightType;
			break;
			default:
				return $resultCode;
		}
		$db = BwDatabase::getInstance();
		$queryParams = array(
			'tableName' => 'list_user_params',
			'queryType' => 'UPDATE',
			'queryFields' => array(
				$rightType => ':' . $rightType
			),
			'queryCondition' => array(
				'gift_list_user_id = :gift_list_user_id',
				'gift_list_id = :gift_list_id',
			),
			'queryValues' => array(
				array(
					'parameter' => ':gift_list_user_id',
					'variable' => $userId,
					'data_type' => PDO::PARAM_INT
				),
				array(
					'parameter' => ':gift_list_id',
					'variable' => $listId,
					'data_type' => PDO::PARAM_INT
				),
				$paramFieldData
			),
			
		);
		if($db->prepareQuery($queryParams)) {
			$result =  $db->exec();
			if($result) {
				// Empty cache
				BwCache::delete('user_param_' . $userId);
				$resultCode = 0;
			} else {
				$resultCode = 1;
			}
		}
		return $resultCode;
	}

	private function storeAttributes($sqlResult)
	{
		$this->listId        = (int)$sqlResult['gift_list_id'];
		$this->userId        = (int)$sqlResult['gift_list_user_id'];
		$this->canView       = (bool)$sqlResult['can_view'];
		$this->canEdit       = (bool)$sqlResult['can_edit'];
		$this->canMark       = (bool)$sqlResult['can_mark'];
		$this->alertAddition = (bool)$sqlResult['alert_addition'];
		$this->alertPurchase = (bool)$sqlResult['alert_purchase'];
	}

	/**
	 *
	 */
	public static function getAllByUserId($userId = null)
	{
		$param = new self();
		return $param->loadByUserId($userId);
	}

	public static function updateUserRight($userId = null, $listId = null, $rightType = '', $enabled = false)
	{
		$param = new self();
		return $param->updateRight($userId, $listId, $rightType, $enabled);
	}
}