<?php

/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.03.16
 * Time: 17:41
 * @method getUserId()
 * @method getValue()
 * @method getCmd()
 * @method setOffset()
 * @method getOffset()
 * @method getUpdated()
 * @method setUpdated()
 * @method getDiffArray()
 * @method setDiffArray()
 * @method getOffsetDiff()
 * @method setOffsetDiff()
 */
class App
{


    private $_data = array();
    private $_db = null;
    private $_lastUpdate = null;

    /**
     * App constructor.
     * simple routing
     */
    public function __construct()
    {
        $this->_initGet();
        // command top render top.phtml
        if ($this->getCmd() == 'top') {

            include 'template/top.phtml';

            //this is top last week
        } elseif ($this->getCmd() == 'topLast') {

            $this->setOffset(0);
            $this->setOffsetDiff(7);
            include 'template/top.phtml';

            //calculation and indexing table user rating index
        } elseif ($this->getCmd() == 'calc') {

            $this->calcAction();
            include 'template/top.phtml';

            //adding score to user
        } elseif ($this->getCmd() == 'score') {

            $this->scoreAction();
            include 'template/top.phtml';

        } else {
            throw new Exception('Command not found!');
        }
    }

    /**
     * get array configuration
     * @return PDO
     */
    public function getConnection()
    {
        if (!is_null($this->_db)) {
            return $this->_db;
        }
        //loading configuration
        $config = include 'config/Config.php';

        //connect to database
        $this->_db = new PDO(
            'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'],
            $config['db_user'],
            $config['db_password'],
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            )
        );
        return $this->_db;
    }


    /**
     * @param int $dayOffset
     * @return array
     */
    public function getTop()
    {
        $lastUpdate = $this->_getLastUpdateAt();

        if (!is_null($this->getOffset())) {
            // todo calc intermedia value for score
            $sql = '
              SELECT DISTINCT `raiting_user_index`.`user`, `raiting_user_index`.*, (@place := @place + 1) as place
              FROM `raiting_user_index`
              WHERE
                `update_date` =  DATE_SUB(CURRENT_DATE, INTERVAL ' . $this->getOffset() . ' DAY)
                GROUP BY `user`
                ORDER BY `place` ASC ';
        } else {
            $sql = '
              SELECT `raiting_user_index`.*, (@place := @place + 1) as place
              FROM `raiting_user_index`
              WHERE
                `update_at` = "' . $lastUpdate . '"
                ORDER BY `score` DESC  ';
        }

        $this->getConnection()->exec('SET @place:=0;');
        $statement = $this->getConnection()->query($sql);
        return $statement->fetchAll();
    }

    /**
     * @param int $dayOffset
     * @return mixed
     */
    public function getDiff()
    {
        if (!is_null($this->getDiffArray())) {
            return $this->getDiffArray();
        }


        if (!is_null($this->getOffset())) {

            $sql = '
          SELECT `raiting_user_index`.`user`, (@place := @place + 1) as place
          FROM `raiting_user_index`
          WHERE
            `update_date` =  DATE_SUB(CURRENT_DATE, INTERVAL '
                .($this->getOffset()+$this->getOffsetDiff()).' DAY)
            ORDER BY `score` DESC  ';

        } else {

            $sql = '
          SELECT `raiting_user_index`.`user`, (@place := @place + 1) as place
          FROM `raiting_user_index`
          WHERE
            `update_at` = "' . $this->_getLastUpdate() . '"
            ORDER BY `score` DESC  ';

        }

        $this->getConnection()->exec('SET @place:=0;');
        $statement = $this->getConnection()->query($sql);

        $arrayBackOffset = array();

        foreach ($statement->fetchAll() as $place) {
            $arrayBackOffset[$place['user']] = $place['place'];
        }

        $this->setDiffArray($arrayBackOffset);
        return $this->getDiffArray();
    }

    /**
     * calc action
     * @return int
     */
    public function calcAction()
    {
        $lastUpdate = $this->_getLastUpdate();

        if (is_null($lastUpdate)) {
            $sql = '
           INSERT INTO `users_raiting`.`raiting_user_index` (`user`,`score`,`update_at`,`last_update`,`update_date`)
           SELECT `user`, `score`, NOW(),NOW(), CURRENT_DATE FROM `raiting_user`
          ';
        } else {
            $sql = '
           INSERT INTO `users_raiting`.`raiting_user_index` (`user`,`score`,`update_at`,`last_update`,`update_date`)
           (SELECT `user`, `score`, NOW(),"' . $lastUpdate . '", CURRENT_DATE FROM `raiting_user`)
          ';
        }

        $res = $this->getConnection()->exec($sql);
        return $res;
    }

    /**
     * get last update
     * @return date | null
     */
    protected function _getLastUpdate()
    {
        if (!is_null($this->_lastUpdate)) {
            return $this->_lastUpdate;
        }
        // select last date for update
        $lastDateQuery = '
            SELECT `raiting_user_index`.`last_update`
            FROM `raiting_user_index`
            ORDER BY `last_update` DESC
            LIMIT 1
        ';

        $lastUpdate = $this
            ->getConnection()
            ->query($lastDateQuery)
            ->fetch();
        $this->_lastUpdate = $lastUpdate['last_update'];
        return $this->_lastUpdate;
    }

    /**
     * get last updated at
     * @return null
     */
    protected function _getLastUpdateAt()
    {
        if (!is_null($this->getUpdated())) {
            return $this->_lastUpdate;
        }
        // select last date for update
        $lastDateQuery = '
            SELECT `raiting_user_index`.`update_at`
            FROM `raiting_user_index`
            ORDER BY `update_at` DESC
            LIMIT 1
        ';

        $lastUpdate = $this
            ->getConnection()
            ->query($lastDateQuery)
            ->fetch();
        $this->setUpdated($lastUpdate['update_at']);
        return $this->getUpdated();
    }

    /**
     * add score for user
     * @return bool
     * @throws Exception
     */
    public function scoreAction()
    {
        // check get parameters user id
        if (is_null($this->getUserId())) {
            throw new Exception('User not specified');
        }

        // check get `value`
        if (is_null($this->getValue())) {
            throw new Exception('Value params not specified');
        }

        // check exist user in database
        if ($obj = $this->_checkExistUser()) {
            $result = $this->_updateScore($obj);
        } else {
            $result = $this->_insertScore();
        }

        return $result;
    }

    /**
     * update score
     * @param $obj
     * @return bool
     */
    protected function _updateScore($obj)
    {
        $score = $this->getValue() + $obj->score;

        $sql = 'UPDATE `raiting_user` SET `raiting_user`.`score` = :score
                  WHERE `raiting_user`.`user` = :userId';

        $statement = $this->getConnection()->prepare($sql);

        $result = $statement->execute(
            array(
                'userId' => $this->getUserId(),
                'score' => (int)$score
            )
        );

        return $result;
    }

    /**
     * insert score to database
     * @return bool
     */
    protected function _insertScore()
    {
        $sql = 'INSERT INTO `raiting_user` (`user`,`score`) VALUES (:userId,:score)';

        $statement = $this->getConnection()->prepare($sql);

        $result = $statement->execute(
            array(
                'userId' => $this->getUserId(),
                'score' => $this->getValue()
            )
        );
        return $result;
    }

    /**
     * check exist user in database
     * @return bool | integer
     */
    protected function _checkExistUser()
    {
        $sql = 'SELECT `raiting_user`.`user`,`raiting_user`.`score`
                  FROM `raiting_user`
                  WHERE `raiting_user`.`user` = :userId';

        $statement = $this->getConnection()->prepare($sql);

        $statement->execute(
            array(
                'userId' => $this->getUserId()
            )
        );

        if ($obj = $statement->fetchObject()) {
            return $obj;
        } else {
            return false;
        }
    }

    /**
     * init automatically getters init
     */
    private function _initGet()
    {
        foreach ($_GET as $key => $value) {
            $key = str_replace('_', '', $key);
            $this->setData($key, $value);
        }
    }

    /**
     * set data
     * @param $key
     * @param $value
     * @return $this
     */
    public function setData($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    /**
     * automatically setter and getter
     * @param $name
     * @param $arguments
     * @return $this|null
     */
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        $method = substr($name, 0, 3);
        $key = strtolower(substr($name, 3, strlen($name)));
        if ($method == 'set') {
            $this->_data[$key] = $arguments[0];
            return $this;
        } elseif ($method == 'get') {
            if (isset($this->_data[$key])) {
                return $this->_data[$key];
            } else {
                return null;
            }
        }
    }

}