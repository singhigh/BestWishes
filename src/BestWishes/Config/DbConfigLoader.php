<?php

namespace BestWishes\Config;

use Doctrine\DBAL\Connection;

class DbConfigLoader {

    private $dbConn;

    public function __construct(Connection $dbConn) {
        $this->dbConn = $dbConn;
    }
}