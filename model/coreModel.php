<?php
	
if( !class_exists('DB') ) {
	require dirname(dirname(__FILE__)). '/utils/database.php';
}

class CoreModel
{
    const YT_POST_TABLE = 'yt_video';
    const FB_POST_TABLE = 'fb_post';
    const FB_POST_MENTION_TABLE = 'fb_post_mention';
    const FB_POST_TAG_TABLE = 'fb_post_tag';
    const IG_POST_TABLE = 'ig_post';
    const INSIGHT_TABLE = 'insight';
    const INSIGHT_PRESET_HASHTAGS_TABLE = 'insight_preset_hashtags';
    const INSIGHT_TRACKING_POST_TABLE = 'insight_tracking_post';
    const INSIGHT_TRACKING_POST_HISTORY_TABLE = 'insight_tracking_post_history';
    const INSIGHT_TRACKING_POST_LATEST_COMMENTS_TABLE = 'insight_tracking_post_latest_comments';
    const INSIGHT_TRACKING_POST_LATEST_LIKES_TABLE = 'insight_tracking_post_latest_likes';
    const JOBS_CAMPAIGN_TABLE = 'jobs_campaign';
    const JOBS_CAMPAIGN_HASHTAGS_TABLE = 'jobs_campaign_hashtags';
    const JOBS_INFLUENCER_TABLE = 'jobs_influencer';
    const MANUAL_IG_USER_TABLE = 'manual_ig_user';
    const MANUAL_INFLUENCER_TABLE = 'manual_influencer';
    const MANUAL_JOBS_INFLUENCER_TABLE = 'manual_jobs_influencer';

	public $db = null;
    public $table = null;

    public function __construct($table=null) {
		$this->db = new DB();
		$this->dba = new DB("AUTOMATION");
		$this->wdb = new DB("WEB");
        $this->table = $table;
    }

	public function resetDBConnection()
	{
		$this->db->resetDBConnection();
	}

	private function initDB(){
		//echo "init db";
	}
	private function closeDB(){
		//echo "close db";
	}
	public function __call($method,$arguments) {
		//echo "call";
        if(method_exists($this, $method)) {
            $this->initDB();
            call_user_func_array(array($this,$method),$arguments);
            $this->closeDB();
            return;
        }
    }

    public function insertSingle($item, $dbType=null) {
        return $this->insertMulti([$item], $dbType);
    }

    public function insertMulti($items, $dbType=null) {
        if (count($items) <= 0) {
            return false;
        }
        list($queryProperties, $queryValues, $args) = $this->prepareSQLQueryParams($items);
        return $this->runQuery('INSERT INTO '.$this->table.' '.$queryProperties.' VALUES '.$queryValues, $args, $dbType);
    }

    private function prepareSQLQueryParams($items) {
        $queryProperties = [];
        $queryValues = [];
        $args = [];

        foreach ($items[0] as $key => $val) {
            array_push($queryProperties, $key);
        }

        foreach ($items as $itemKey => $item) {
            $queryValue = [];
            foreach ($item as $key => $val) {
                $queryKey = ':'.$key.$itemKey;
                array_push($queryValue, $queryKey);
                $args[$queryKey] = $val;
            }
            array_push($queryValues, $this->roundBrackets(join(',', $queryValue)));
        }

        return [$this->roundBrackets(join(',', $queryProperties)), join(',', $queryValues), $args];
    }

    private function roundBrackets($string) {
        return '('.$string.')';
    }

    // Unsupported if any duplicated key in $set and $where
    public function updateMulti($set=[], $where=[], $dbType=null) {
        if (empty($set)) {
            return false;
        }

        list($where, $whereArgs) = $this->prepareSQLWhereClause($where);
        list($set, $setArgs) = $this->prepareSQLSet($set);

        return $this->updateQuery('UPDATE '.$this->table.$set.$where, array_merge($setArgs, $whereArgs), $dbType);
    }

    private function prepareSQLSet($array) {
        return $this->prepareSQLStringWithArgs('SET', ',', $array);
    }

    public function getSingle($where=[], $column=['*'], $orderBy=[], $dbType=null) {
        $result = $this->getMulti($where, $column, $orderBy, $dbType);
        return empty($result) ? false : $result[0];
    }

    public function getMulti($where=[], $column=['*'], $orderBy=[], $dbType=null) {
        list($where, $args) = $this->prepareSQLWhereClause($where);
        return $this->query('SELECT '.join(',', $column).' FROM '.$this->table.$where.$this->prepareSQLOrderBy($orderBy), $args, $dbType);
    }

    private function prepareSQLOrderBy($array) {
        if (count($array) <= 0) {
            return '';
        }

        $strings = [];
        foreach ($array as $item) {
            $string = $item['column'];
            if (!empty($item['order'])) {
                $string .= ' '.$item['order'];
            }
            array_push($strings, $string);
        }
        return ' ORDER BY '.join(',', $strings);
    }

    public function deleteMultiByWhereClause($whereClause, $dbType=null) {
        return $this->query('DELETE FROM '.$this->table.' WHERE '.$whereClause, null, $dbType);
    }

    public function deleteMulti($where=[], $dbType=null) {
        list($where, $args) = $this->prepareSQLWhereClause($where);
        return $this->query('DELETE FROM '.$this->table.$where, $args, $dbType);
    }

    private function prepareSQLWhereClause($array) {
        return $this->prepareSQLStringWithArgs('WHERE', 'AND', $array);
    }

    private function prepareSQLStringWithArgs($prefix, $separator, $array) {
        if (empty($array)) {
            return ['', []];
        }

        $strings = [];
        foreach ($array as $key => $val) {
            array_push($strings, $key.'=:'.$key);
        }
        $args = $this->prepareArgs($array);
        return [' '.$prefix.' '.join(' '.$separator.' ', $strings), $args];
    }

    private function prepareArgs($array=[]) {
        $args = [];
        foreach ($array as $key => $val) {
            $args[':'.$key] = $val;
        }
        return $args;
    }

    protected function runQuery($sql, $args=null, $dbType=null) {
        return $this->getDb($dbType)->runQuery($sql, $args);
    }

    protected function updateQuery($sql, $args=null, $dbType=null) {
        return $this->getDb($dbType)->updateQuery($sql, $args);
    }

    protected function query($sql, $args=null, $dbType=null) {
        return $this->getDb($dbType)->query($sql, $args);
    }

    private function getDb($dbType) {
        switch ($dbType) {
            case 'WEB':
                return $this->wdb;
            case 'AUTOMATION':
                return $this->dba;
            default:
                return $this->db;
        }
    }
}
?>