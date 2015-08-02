<?php
/**
 * Juser评论管理功能实现
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-08-02 15:55:04
 * @version $Id$
 */

class JuserCommnet {
    private $Db;
    private $tableName;
    private $tableField;
    private $Options  = array();

    function __construct(){
    	$this->tableName       =	DB_PREFIX.'comment';
    	$Juser  	  		   =	Juser::getInstance();
        $this->Db      		   =	$Juser->getDbInstance();
        $Filed  	  		   =	$this->getDbFields();
        foreach ($Filed as $key => $value) {
        	$this->tableField .=	$this->parseKey($value).',';
        }
        $this->tableField      =	rtrim($this->tableField,',');
    }

	/**
     * 获取数据表字段信息
     * @access public
     * @return array
     */
    protected function getDbFields(){
    	$result =   $this->Db->query('SHOW COLUMNS FROM `'.$this->tableName.'`');
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                $info[] = $val['Field'];                
            }
        }
        return $info;
    }

    /**
     * 字段和表名处理添加`
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey($key) {
        $key   =  trim($key);
        if(!is_numeric($key) && !preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
           $key = '`'.$key.'`';
        }
        return $key;
    }

	/**
	 * 单用户评论过多 实现分页功能
	 * @param int $pageNum
	 * @return object
	 */
    public function page($pageNum=1) {
    	$this->Options['page']        =	$pageNum;
    	if(!ctype_digit((string)$pageNum)) {
			$this->Options['page'] 	  = 1;
		}
		return $this;
    }

	/**
	 * 查询出指定分页的指定邮箱的20条评论
	 * @param string $mail
	 * @return object
	 */
	public function select($mail) {
		if(!Juser_is_mail($mail)) { return false; }
		if(empty($this->Options['page'])) {
			$this->Options['page']   = 1;
		}
		$lb                          = ($this->Options['page']-1)*20;
		$selectSql					 = "SELECT {$this->tableField} FROM `{$this->tableName}` WHERE `mail`='{$mail}' ORDER BY `cid` DESC LIMIT {$lb},20";
		// var_dump();
		// echo $selectSql;
		$result =   $this->query($selectSql);
		return $result;
	}
	#按邮箱查询获得分页按钮情况
	public function getPageString($mail) {
		if(!Juser_is_mail($mail)) { return ''; }
		$rowsCount   =  $this->query("SELECT count(*) AS J_COUNT FROM {$this->tableName} WHERE `mail`='{$mail}'");
		$rowsCount   =  $rowsCount[0]['J_COUNT'];
		$TotolRows   =  ceil($rowsCount/20);
	}

	/**
     * 执行sql查询
     * @access public
     * @param mixed $data 要操作的sql
     * @return boolean
     */		
    public function query($sql) {
    	$result 		= 	array();
    	$queryID   		=	$this->Db->query($sql,true);
        while($row      =   $this->Db->fetch_array($queryID)) {
            $result[]   =   $row;
        }
        return $result;
    }
}