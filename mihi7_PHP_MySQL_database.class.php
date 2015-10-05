<?php
/*
 * Created By Mihitha Rajith Kankanamge
 * GNU GENERAL PUBLIC LICENSE
 * Created 29 May 2015 (Version 1)
 * Update 21 Sep 2015 (Version 2)
 * All rights received.
 */

class DATABASE_VER2 {

    //Constructor
    public function __construct() {
        //Load Database connection to an object
        $this->database_connection = $this->Connect_MySQLi_DB();
        $this->tanent_id = get_tanent_id();
    }

    /**
     * Declaring Private MYSQLi connection function
     * @return \mysqli
     */
    private function Connect_MySQLi_DB() {
        //global $sql_link;
        $connection = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DBASE);
        // check connection
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit;
        } else {
            mysqli_set_charset($connection, "utf8");
            return $connection;
        }
    }

    /**
     * Constructor for MySql Connection
     * Determining the Type of Each Variable
     * @param type $item
     * @return string
     */
    private function myvartype($item) {
        switch (gettype($item)) {
            case 'NULL':
            case 'string':
                return 's';
                break;
            case 'boolean':
            case 'integer':
                return 'i';
                break;
            case 'blob':
                return 'b';
                break;
            case 'double':
                return 'd';
                break;
        }
        return '';
    }

    /**
     * Builiding necessary elements for the query
     * @param type $array Data Array
     * @return array - Columns List, Values List n DataTypes, Question marks list
     */
    private function build_query_lists($array) {
        //Variable Diclration
        $columns1 = $columns = $values1 = $values = $types = $qmrks = $columnsNqmarks = $columnsAND = $columnsOR = '';
        foreach ($array as $columns => $values) {
            $columns1 .= $columns . ',';
            // Value should URL encode to allow (,) in the string
            $values1 .= urlencode($values) . ',';
            $types .= $this->myvartype($values);
            $qmrks .='?,';
            //Columns and Question marks together for update function
            $columnsNqmarks .= $columns . ' = ?,';
            //Columns and Question marks with AND for select function
            $columnsAND .= $columns . ' = ? AND ';
            //Columns and Question marks with OR for select function
            $columnsOR .= $columns . ' = ? OR ';
        }
        $columns1 = substr($columns1, 0, -1);
        $values1 = substr($values1, 0, -1);
        $qmrks = substr($qmrks, 0, -1);
        $columnsNqmarks = substr($columnsNqmarks, 0, -1);
        $columnsAND = substr($columnsAND, 0, -5);
        $columnsOR = substr($columnsOR, 0, -4);
        return array('columns' => $columns1, 'typesNvalues' => ($types . ',' . $values1), 'qmrks' => $qmrks, 'columnsNqmarks' => $columnsNqmarks, 'columnsAND' => $columnsAND, 'columnsOR' => $columnsOR);
    }

    /**
     * Inser Data to Database
     * @param string $table - Table Name
     * @param array $column_value_array - Array of data 'column_name'=>'value','column_name2'=>'value2'
     * @return last insert ID
     * @return boolean false if fails
     * @author Mihtha
     */
    public function insert_me_in($table, $column_value_array) {
        $mysqli = $this->database_connection;

        $query_lists = $this->build_query_lists($column_value_array);
        //Create SQL statement
        $sql_query = 'INSERT INTO ' . $table . ' (' . $query_lists['columns'] . ') VALUES (' . $query_lists['qmrks'] . ')';
        //Data array prefixed with data types
        $refArr0 = explode(',', $query_lists['typesNvalues']);
        //Decode URL Encode
        foreach ($refArr0 as $key4 => &$value4) {
            $refArr[] = urldecode($value4);
        }

        //Prepare the statements
        if (!($stmt = $mysqli->prepare($sql_query))) {
            echo 'Database Insert Error (Database action class, insert_me_in) : ' . $mysqli->errno . ' - ' . $mysqli->error;
            exit;
        } else {
            //Data Inserting
            $ref = new ReflectionClass('mysqli_stmt');
            $method = $ref->getMethod("bind_param");
            $method->invokeArgs($stmt, $refArr);
            if ($stmt->execute()) {
                return $mysqli->insert_id;
            } else {
                if (($mysqli->error) != '') {
                    echo $mysqli->error;
                    exit;
                }
                return false;
            }
        }
    }

    /**
     * Delete Data Set from Database
     * @param String $table Table Name
     * @param array $where_columns Where culumn list as key value array. Eg - array('column_name'=>'value','column_name2'=>'value2')
     * @param String $and_or Select type in between where columns. Either AND or OR. Cannot use mixed. 
     * @return boolean True or False
     * @author Mihitha R K <mihitha@gmail.com>
     */
    public function delete_me_now($table, $where_columns, $and_or) {

        $mysqli = $this->database_connection;
        if ($where_columns != '') {
            $where_columns1 = $this->build_query_lists($where_columns);
            //Change Query Tipe to AND or OR
            if (trim(strtolower($and_or)) == 'and') {
                $where_columns_type = $where_columns1['columnsAND'];
            } elseif (trim(strtolower($and_or)) == 'or') {
                $where_columns_type = $where_columns1['columnsOR'];
            } else {
                echo 'Fatal Error Database Class : wrong AND, OR selection';
                exit;
            }
        }
        //Create SQL statement
        $sql_query = 'DELETE FROM ' . $table;
        if ($where_columns != '') {
            $sql_query .= ' WHERE (' . $where_columns_type . ')';
        }
        $sql_query .= $select_tanent;

        if ($where_columns != '') {
            //Data array prefixed with data types
            $refArr0 = explode(',', $where_columns1['typesNvalues']);
            //Decode URL Encode
            foreach ($refArr0 as $key4 => &$value4) {
                $refArr[] = urldecode($value4);
            }
        }
        //Prepare the statements
        if (!($stmt = $mysqli->prepare($sql_query))) {
            echo 'Database Select Error (Database action class, TAKE ME OUT) : ' . $mysqli->errno . ' - ' . $mysqli->error;
            exit;
        } else {
            //Data Selecting
            if ($where_columns != '') {
                $ref = new ReflectionClass('mysqli_stmt');
                $method = $ref->getMethod("bind_param");
                $method->invokeArgs($stmt, $refArr);
            }
            if ($stmt->execute()) {
                return true;
            } else {
                if (($mysqli->error) != '') {
                    echo $mysqli->error;
                    exit;
                }
                return false;
            }
        }
    }

    /**
     * Update Data in the Database
     * @param string $table - Table Name
     * @param string $which_column - Search Column for Update
     * @param string $search_value - Search Value
     * @param array $column_value_array - Array of data 'column_name'=>'value','column_name2'=>'value2'
     * @return boolean
     * @author Mihtha
     */
    public function update_me_now($table, $which_column, $search_value, $column_value_array) {
        $mysqli = $this->database_connection;
        $query_lists = $this->build_query_lists($column_value_array);
        //Create SQL statement
        $sql_query = "update $table set " . $query_lists['columnsNqmarks'] . " where $which_column = '" . $search_value . "' $select_tanent";
        //Data array prefixed with data types
        $refArr0 = explode(',', $query_lists['typesNvalues']);
        //Decode URL Encode
        foreach ($refArr0 as $key4 => &$value4) {
            $refArr[] = urldecode($value4);
        }
        //Prepare the statements
        if (!($stmt = $mysqli->prepare($sql_query))) {
            echo 'Database Update Error (Database action class, update_me_now) : ' . $mysqli->errno . ' - ' . $mysqli->error;
            exit;
        }
        //Data Inserting
        $ref = new ReflectionClass('mysqli_stmt');
        $method = $ref->getMethod("bind_param");
        $method->invokeArgs($stmt, $refArr);
        if ($stmt->execute()) {
            return true;
        } else {
            if (($mysqli->error) != '') {
                echo 'Database action class. Error on Update ' . $mysqli->error;
            }
            return false;
        }
    }

    /**
     * Select Data Set from Database
     * @param String $table Table Name
     * @param String $prefix SQL Statement Prefix. Eg - DISTINCT
     * @param array $selected_columns Slected columns as a simple array. Eg - array(id,name)
     * @param array $where_columns Where culumn list as key value array. Eg - array('column_name'=>'value','column_name2'=>'value2')
     * @param String $and_or Select type in between where columns. Either AND or OR. Cannot use mixed. 
     * @param String $suffix SQL Statement Suffix. Eg - LIMIT 1
     * @return Array Multi Dimention Array
     * @author Mihitha R K <mihitha@gmail.com>
     */
    public function take_me_out($table, $prefix, $selected_columns, $where_columns, $and_or, $suffix) {
        $mysqli = $this->database_connection;
        $selected_columns1 = implode(',', $selected_columns);
        if ($where_columns != '') {
            $where_columns1 = $this->build_query_lists($where_columns);
            //Change Query Tipe to AND or OR
            if (trim(strtolower($and_or)) == 'and') {
                $where_columns_type = $where_columns1['columnsAND'];
            } elseif (trim(strtolower($and_or)) == 'or') {
                $where_columns_type = $where_columns1['columnsOR'];
            } else {
                echo 'Fatal Error Database Class : wrong AND, OR selection';
                exit;
            }
        }
        //Create SQL statement
        $sql_query = 'SELECT ' . $prefix . ' ' . $selected_columns1 . ' FROM ' . $table;
        if ($where_columns != '') {
            $sql_query .= ' WHERE (' . $where_columns_type . ')';
        }
        $sql_query .= $select_tanent . ' ' . $suffix;

        if ($where_columns != '') {
            //Data array prefixed with data types
            $refArr0 = explode(',', $where_columns1['typesNvalues']);
            //Decode URL Encode
            foreach ($refArr0 as $key4 => &$value4) {
                $refArr[] = urldecode($value4);
            }
        }
        //Prepare the statements
        if (!($stmt = $mysqli->prepare($sql_query))) {
            echo 'Database Select Error (Database action class, TAKE ME OUT) : ' . $mysqli->errno . ' - ' . $mysqli->error;
            exit;
        } else {
            //Data Selecting
            if ($where_columns != '') {
                $ref = new ReflectionClass('mysqli_stmt');
                $method = $ref->getMethod("bind_param");
                $method->invokeArgs($stmt, $refArr);
            }
            if ($stmt->execute()) {
                $meta = $stmt->result_metadata();
                while ($field = $meta->fetch_field()) {
                    $params[] = &$row[$field->name];
                }
                call_user_func_array(array($stmt, 'bind_result'), $params);
                $dataset = array();
                while ($stmt->fetch()) {
                    foreach ($row as $key => $val) {
                        $c[$key] = $val;
                    }
                    $dataset[] = $c;
                }
                //Return Dataset
                return $dataset;
            } else {
                if (($mysqli->error) != '') {
                    echo $mysqli->error;
                    exit;
                }
                return false;
            }
        }
    }

}
