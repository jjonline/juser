<?php
/**
 * 数据库操作模型
 * @authors Jea杨 (JJonline@JJonline.Cn)
 * @date    2015-07-23 21:29:50
 * @version $Id$
 */
if(!defined('EMLOG_ROOT')) {exit('Juser 运行在emlog博客框架下!');}
class JuserModel {
	// mysql链接句柄
	protected static $DbHandler =	null;
	// 主键名称
    protected $pk               =   'id';
	// 数据表名（不包含表前缀）
    protected $tableName        =   'juser_data';
	// 字段信息
    protected $fields           =   array();
    //处理好的sql字段
    protected $SQLfields        =   '';
	// 数据信息
    protected $data             =   array();
    // 查询表达式参数
    protected $options 			=	array();

    public function __construct() {
    	if(is_null(self::$DbHandler)) {  
    		if(class_exists('mysqli')) {
				self::$DbHandler=   MySqlii::getInstance();
			}else{
				self::$DbHandler= 	MySql::getInstance();
			}
    	}
    	$this->tableName 		=	DB_PREFIX.$this->tableName;
    	$this->fields 			=	$this->getDbFields();
        foreach ($this->fields as $key => $value) {
            $this->SQLfields   .=   $this->parseKey($value).',';
        }
        $this->SQLfields        =   rtrim($this->SQLfields,',');#查询的全字段预处理
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
     * 执行sql查询
     * @access public
     * @param mixed $data 要操作的sql
     * @return boolean
     */		
    public function query($sql) {
    	$result 		= 	array();
    	$queryID   		=	self::$DbHandler->query($sql,true);
        while($row      =   self::$DbHandler->fetch_array($queryID)) {
            $result[]   =   $row;
        }
        return $result;
    }
    
    /************************************************************************************************************/
    /************************************************************************************************************/
    /************************************************************************************************************/
    /*********************************************查询相关处理***************************************************/
    /************************************************************************************************************/
    /************************************************************************************************************/
    /************************************************************************************************************/
    /**
     * 指定查询字段 支持字段排除
     * @access public
     * @param mixed $field
     * @param boolean $except 是否排除
     * @return object
     */
    public function field($field,$except=false) {
        if(true === $field) {// 获取全部字段
            $fields     =  $this->fields;
            $field      =  $fields?:'*';
        }elseif($except) {// 字段排除
            if(is_string($field)) {
                $field  =  explode(',',$field);
            }
            $fields     =  $this->fields;
            $field      =  $fields?array_diff($fields,$field):$field;
        }
        $this->options['field']   =   $field;
        return $this;
    }

    /**
     * 指定查询条件（简单的按主键或索引键where，批量或范围查询不支持） eg. $where = ['id'=>1];
     * @access public
     * @param array $where 条件表达式
     * @param m$parse 预处理参数
     * @return object
     */
    public function where($where=array()){
        if(isset($this->options['where'])){
            $this->options['where'] =   array_merge($this->options['where'],$where);
        }else{
            $this->options['where'] =   $where;
        }
        #字段效验免了 emlog的Db驱动类已内置  
        return $this;
    }

    /**
     * 指定查询数量
     * @access public
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return object
     */
    public function limit($offset,$length=null){
        $this->options['limit'] =   is_null($length)?$offset:$offset.','.$length;
        return $this;
    }

    /**
     * 查询单条数据
     * @access public
     * @param mixed $options 表达式参数，参数为数组，指定条件，参数为数字或字符串，则是指定该值为主键进行查询
     * @return mixed
     */
    public function find($options=array()) {
        if(is_numeric($options) || is_string($options)) {
            $where[$this->pk]       =   $options;
            $options                =   array();
            $options['where']       =   $where;
        }
        // 总是查找一条记录
        $options['limit']           =   1;
        $resultSet                  =   $this->select($options);
        if(false === $resultSet) {
            return false;
        }
        if(empty($resultSet)) {// 查询结果为空
            return null;
        }
        return $resultSet[0];#find返回一维数组
    }

    /**
     * 查找记录
     * @access public
     * @param array $options 表达式
     * @return mixed
     */
    public function select($options=array()) {
        $sql            =   $this->buildSelectSql($options);
        $result         =   $this->query($sql);
        $this->options  =   array();#清空条件表达式避免影响下一次查询
        return $result;
    }

    /**
     * 将查询表达式转换为sql的select语句
     * @access public
     * @param array $options 表达式
     * @return string
     */
    protected function buildSelectSql($options=array()) {
        if(is_array($options)) {
            $options            =   array_merge($this->options,$options);
        }
        $sql                    =   'SELECT ';
        #字段
        if(isset($options['field'])) {
            foreach ($options['field'] as $key => $value) {
                $sql           .=   $this->parseKey($value).',';
            }
            $sql                =   rtrim($sql,',').' FROM '.$this->parseKey($this->tableName);
        }else {
            #并未显示指定查询那些字段就获取全部字段
            $sql                =   $this->SQLfields.' FROM '.$this->parseKey($this->tableName);
        }
        #条件
        if(isset($options['where'])) {
            foreach ($options['where'] as $key => $value) {
                $key            =   trim($key);
                $value          =   is_numeric($value)?(int)$value:"'".$value."'";
                $sql           .=   ' WHERE ('.$this->parseKey($key).'='.$value.')';
            }
        }
        #排序
        //ORDER BY `id` DESC,`time` DESC
        if(isset($options['order'])) {
            $sql               .=   ' ORDER BY ';
            foreach ($options['order'] as $key => $value) {
                $key            =   trim($key);
                $sql           .=   $this->parseKey($key).' '.strtoupper($value).',';
            }
            $sql                =   rtrim($sql,',');
        }
        #范围限制Limit
        //LIMIT 0,10
        //LIMIT 6
        if(isset($options['limit'])) {
            $sql               .=   ' LIMIT '.$options['limit'];
        }else {
            $sql               .=   ' LIMIT 20';#当调用方法并未限制时防止过量占用资源 默认限制返回20条数据
        }
        return $sql;
    }
    
    /************************************************************************************************************/
    /************************************************************************************************************/
    /************************************************************************************************************/
    /*********************************************写入相关处理***************************************************/
    /************************************************************************************************************/
    /************************************************************************************************************/
    /************************************************************************************************************/
    /**
     * 设置数据对象值或者不传参获取数据对象
     * @access public
     * @param mixed $data 数据
     * @return Model
     */
    public function data($data=''){
        if('' === $data && !empty($this->data)) {
            return $this->data;
        }
        if(is_object($data)){#对象类型提取成数组
            $data   =   get_object_vars($data);
        }elseif(is_string($data)){#get参数类型的数据传递
            parse_str($data,$data);
        }
        if(!is_array($data)) {return $this;}#非数组设置数据对象 不理会
        $this->data = $data;
        return $this;
    }

    /**
     * 设置数据对象的值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return void
     */
    public function __set($name,$value) {
        // 设置数据对象属性
        $this->data[$name]  =   $value;
    }

    /**
     * 获取数据对象的值
     * @access public
     * @param string $name 名称
     * @return mixed
     */
    public function __get($name) {
        return isset($this->data[$name])?$this->data[$name]:null;
    }

    /**
     * 检测数据对象的值
     * @access public
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name) {
        return isset($this->data[$name]);
    }

    /**
     * 销毁数据对象的值
     * @access public
     * @param string $name 名称
     * @return void
     */
    public function __unset($name) {
        unset($this->data[$name]);
    }
    
    /**
     * 新增(写入)数据
     * @access public
     * @param mixed $data 数据=>最佳实践：通过设置数据对象方式传递需要新增的数据较佳
     * @return mixed
     */   
    public function add($data = array()) {
        if(empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if(!empty($this->data)) {
                $data           =   $this->data;
                // 重置数据
                $this->data     = array();
            }else{
                #没有传递新增的数据
                return false;
            }
        }
        #效验新增数据合法性
        if(!isset($data['mail']) || !isset($data['password'])) {
            return false;
        }
        #time
        if(!isset($data['time'])) {
            $data['time']       =   time();
        }
        #sql语句和字段检测
        //INSERT INTO tbl_name (col1,col2) VALUES(col2*2,15)
        $sql                    =   'INSERT INTO '.$this->parseKey($this->tableName);
        $coums                  =   '';
        $coumVals               =   '';
        foreach ($data as $key  =>  $value) {
            if(!in_array($key,$this->fields)) {
                //直接过滤掉非法数据对象
                unset($data[$key]);
                //return false;
            }
            $coums             .=   $key.',';
            $coumVals          .=   "'".$value."',";
        }
        $coums                  =   '('.rtrim($coums,',').')';
        $coumVals               =   '('.rtrim($coumVals,',').')';
        $sql                   .=   ' '.$coums.' VALUES '.$coumVals;
        $result                 =   self::$DbHandler->query($sql,true);#emlog初始化好的数据操作对象直接执行sql的insert
        if(!$result) {
            return false;#新增数据失败
        }
        return self::$DbHandler->insert_id();#返回插入的主键
    }

    /************************************************************************************************************/
    /************************************************************************************************************/
    /************************************************************************************************************/
    /*********************************************修改相关处理***************************************************/
    /************************************************************************************************************/
    /************************************************************************************************************/
    /************************************************************************************************************/   
    
    /**
     * 保存（更新修改）数据
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return boolean
     */
    public function save($data='',$options=array()) {
        if(empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if(!empty($this->data)) {
                $data           =   $this->data;
                // 重置数据
                $this->data     =   array();
            }else{
                return false;
            }
        }
        if(empty($data)){
            // 没有数据则不执行
            return false;
        }
        // 主键
        $pk                         =   $this->pk;
        $where                      =   '';
        $dataStr                    =   '';
        $sql                        =   'UPDATE '.$this->parseKey($this->tableName).' SET ';
        //UPDATE table_name SET column_name = new_value WHERE column_name = some_value 
        // 分析更新条件
        if(!isset($options['where']) ) {
            // 如果存在主键数据 则自动作为更新条件
            if(isset($data[$pk])) {
                $where[$pk]         =   $data[$pk];
                $options['where']   =   $where;
                unset($data[$pk]);
            }else{
                return false;
            }
        }
        if(is_array($options['where']) && isset($options['where'][$pk])){
            $where                  =   $this->parseKey($pk).'='.$options['where'][$pk];
            unset($options['where']);#存在主键条件后清空其他条件（仅适用于juser）
        }else if(is_array($options['where'])){
            foreach ($options['where'] as $key => $value) {
                $where              =    $this->parseKey($key).'='."'".$value."'";
            }
        }
        $where                      =   ' WHERE '.$where;     
        #设置并检测更新的数据
        foreach ($data as $key => $value) {
            if(!in_array($key,$this->fields)) {
                //直接过滤掉非法数据对象
                unset($data[$key]);
                //return false;
            }
            $value                  =   is_numeric($value)?(int)$value:"'".$value."'";
            $dataStr               .=   $this->parseKey($key).'='.$value.',';
        }        
        $dataStr                    =   rtrim($dataStr,',');
        $sql                        =   $sql.$dataStr.$where;
        #执行更新
        $result                     =   self::$DbHandler->query($sql,true);
        if(!$result) {
            return false;
        }
        return self::$DbHandler->affected_rows();#返回更新影响的行数，可能会是0
    }
    //save方法的别名
    public function update($data='') {
        return $this->save($data);
    }

    /**
     * 对保存到数据库的数据进行效验处理--是否符合特定要求
     * @access public
     * @param mixed $data 要操作的数据
     * @return mixed $data
     */
	public function validData($data=array()) {
        if(!$data) {
            $data  = array_merge($this->data,$data);
        }
        #code...
		return ;
	}
	
	/**
     * 获取数据表字段信息
     * @access public
     * @return array
     */
    public function getDbFields(){
    	$result =   $this->query('SHOW COLUMNS FROM `'.$this->tableName.'`');
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                $info[] = $val['Field'];                
            }
        }
        return $info;
    }
}