<?php
/**
 * Interaction with the database on a custom Level
 * 
 * @since 12.5.13
 * 
 * @author Mat Lipe <mat@matlipe.com>
 * 
 * @uses extend as an abstract or use as is
 * @uses new MvcDatabase(%table%);
 * 
 */
class MvcDatabase {
    /**
     * The current table name
     *
     * @var boolean
     */
    private $table_name = false;

    /**
     * Constructor for the database class to inject the table name
     *
     * @param String $table_name - The current table name
     */
    public function __construct($table_name) {
        $this->table_name = $table_name;
    }
    
    
    /**
     * Get a count if items in this table with optional conditions
     *
     * @param  Array [$conditionValue] - A key value pair of the conditions you want to search on
     * @param  String $condition - A string value for the condition of the query default to equals
     *
     * @return int
     * 
     * @since 12.2.13
     */
    public function getCount(array $conditionValue = array(), $condition = '=' ) {
       global $wpdb;
       
        if( is_array( $fields ) ){
            $fields = implode(',', $fields);
        }
        
        $sql = 'SELECT COUNT(*) FROM `' . $this->table_name . '`';

        if( !empty( $conditionValue ) ){
            $sql .= ' WHERE ';
        }

        foreach ($conditionValue as $field => $value) {
            switch(strtolower($condition)) {
                case 'in' :
                    if (!is_array($value)) {
                        throw new Exception("Values for IN query must be an array.", 1);
                    }

                    $sql .= $wpdb->prepare('`%s` IN (%s)', $field, implode(',', $value));
                    break;

                default :
                    $wheres[] = $wpdb->prepare('`' . $field . '` ' . $condition . ' %s', $value);
                    break;
            }
        }
        
        if( !empty( $wheres ) ){
            $sql .= implode( ' AND ', $wheres );   
        }
        $result = $wpdb->get_var($sql);

        return $result;
    }

    /**
     * Insert data into the current data
     *
     * @param  array  $data - Data to enter into the database table
     *
     * @return InsertQuery Object
     */
    public function insert(array $data) {
        global $wpdb;

        if (empty($data)) {
            return false;
        }

        $wpdb->insert($this->table_name, $data);

        return $wpdb->insert_id;
    }
    
    
    /**
     * Insert a row with some encrypted fields
     * 
     * @since 11.27.13
     * 
     * @param array $data - key => value pairs
     * @param string $salt - The salt to use to encrypt
     * @param array $fieldsToEncrypt - The fields to apply the encryption to - should match data keys
     * 
     * @return Object
     */
    public function insertEncrypt( array $data, $salt, $fieldsToEncrypt = array() ){
        global $wpdb;
        $fields = array_keys( $data );
        
        foreach( $data as $k => $value ){
            if( in_array( $k, $fieldsToEncrypt ) ){
                $formatted_fields[] = 'AES_ENCRYPT("'.$value.'", "'.$salt.'")';  
            } else {
                $formatted_fields[] = '%s';
                $values[] = $value;  
            } 
        }
        
        
        $sql = $wpdb->prepare( "INSERT INTO `$this->table_name` (`" . implode( '`,`', $fields ) . "`) VALUES (" . implode( ",", $formatted_fields ) . ")", $values );

        return $wpdb->query($sql);
        
    }
    
    
    /**
     * Rerieve all from the selected table
     * 
     * @param array $encryptedFields - the fields which are encrypted in the database
     * @param string $salt - the salt that was used to encrypt
     * @param string [$orderBy] - field to orderby (defaults to false)
     * @param  String|Array [$fields] - fields to include in results
     * @param string [$limit] - ability to set a LIMIT in the query
     * 
     * @since 12.5.13
     */
    public function getEncryptedResults(array $encryptedFields, $salt, $orderBy = false, $fields = '*', $limit = false ) {
        global $wpdb;
        
        
        foreach( $encryptedFields as $field ){
            if( $fields != '*' ){
                if( !is_array( $fields ) ){
                    $fields = explode(',', $fields );
                }
                if( !in_array( $field, $fields ) ) continue;   
            }
            $en_fields[] = 'AES_DECRYPT('.$field.',"'.$salt.'") as '.$field;
        }

        if( is_array( $fields ) ){
            $fields = implode(',', $fields);
        }
        
        $sql = 'SELECT '.$fields.','.implode(',',$en_fields).' FROM `' . $this->table_name . '`';

        if (!empty($orderBy)) {
            $sql .= ' ORDER BY ' . $orderBy;
        }
        
        if( $limit ){
            $sql .= ' LIMIT '.$limit;   
        }

        $all = $wpdb->get_results($sql);

        return $all;
    } 
    
    

    
    /**
     * Get all from the selected table
     *
     * @param  String $orderBy - Order by column name
     * @param  String|Array [$fields] - fields to include in results
     * @param  String [$limit] - either number or rows or start,end
     * 
     * @return Table result
     * 
     * @since 12.2.13
     */
    public function getResults($orderBy = NULL, $fields = '*', $limit = false ) {
        global $wpdb;
        
        if( is_array( $fields ) ){
            $fields = implode(',', $fields);
        }
        
        $sql = 'SELECT '.$fields.' FROM `' . $this->table_name . '`';

        if (!empty($orderBy)) {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        if( $limit ){
            $sql .= ' LIMIT '.$limit;   
        }

        $all = $wpdb->get_results($sql);

        return $all;
    }
    
    
        /**
     * Get Encrypted fields by encrypted conditions
     * 
     * @uses will encrypt specified fields using in conditionals and decrypt fields coming out
     *
     * @param  Array $conditionValue - A key value pair of the conditions you want to search on
     * @param  Array $encryptedFields - the fields which are encrypted
     * @param  sting $salt - the salt the fields where encrypted with
     * @param  String [$condition] - A string value for the condition of the query default to equals
     * @param  String|Array [$fields] - fields to include in results
     * @param  String [$limit] - optional LIMIT clause in query
     *
     * @since 12.5.13 
     *
     * @return Table result
     * 
     * 
     */
    public function getEncryptedBy(array $conditionValue, array $encryptedFields, $salt, $condition = '=', $fields = '*', $limit = false ) {
        global $wpdb;

        foreach( $encryptedFields as $field ){
            if( $fields != '*' ){
                if( !is_array( $fields ) ){
                    $fields = explode(',', $fields );
                }
                if( !in_array( $field, $fields ) ) continue;   
            }
            $en_fields[] = 'AES_DECRYPT('.$field.',"'.$salt.'") as '.$field;
        }
       
        if( is_array( $fields ) ){
            $fields = implode(',', $fields);
        }
        
        $sql = 'SELECT '.$fields.','.implode(',',$en_fields).' FROM `' . $this->table_name . '` WHERE ';

        foreach ($conditionValue as $field => $value) {
            if( in_array( $field, $encryptedFields ) ){
                 $wheres[] = '`' . $field . '`' . $condition . ' AES_ENCRYPT("'.$value.'","'.$salt.'")'; 
            } else {
                 $wheres[] = $wpdb->prepare('`' . $field . '` ' . $condition . ' %s', $value); 
            }

        }
        
        if( !empty( $wheres ) ){
            $sql .= implode( ' AND ', $wheres );   
        }
          
        
        if( $limit ){
            $sql .= ' LIMIT '.$limit;   
        }
 

        $result = $wpdb->get_results($sql);

        return $result;
    }
    
    

    /**
     * Get a value by a condition
     *
     * @param  Array $conditionValue - A key value pair of the conditions you want to search on
     * @param  String $condition - A string value for the condition of the query default to equals
     * @param  String|Array [$fields] - fields to include in results
     * @param  String [$limit] - optional LIMIT clause
     * 
     * @since 12.5.13
     *
     * @return Table result
     */
    public function getBy(array $conditionValue, $condition = '=', $fields = '*', $limit = false ) {
       global $wpdb;
       
        if( is_array( $fields ) ){
            $fields = implode(',', $fields);
        }
        
        $sql = 'SELECT '.$fields.' FROM `' . $this->table_name . '` WHERE ';


        foreach ($conditionValue as $field => $value) {
            switch(strtolower($condition)) {
                case 'in' :
                    if (!is_array($value)) {
                        throw new Exception("Values for IN query must be an array.", 1);
                    }

                    $sql .= $wpdb->prepare('`%s` IN (%s)', $field, implode(',', $value));
                    break;

                default :
                    $wheres[] = $wpdb->prepare('`' . $field . '` ' . $condition . ' %s', $value);
                    break;
            }
        }
        
        if( !empty( $wheres ) ){
            $sql .= implode( ' AND ', $wheres );   
        }

        if( $limit ){
            $sql .= ' LIMIT '.$limit;   
        }

        $result = $wpdb->get_results($sql);

        return $result;
    }
    
    
    /**
     * Get results by a custom WHERE statement
     * 
     * @since 11.27.13
     * 
     * @param string $where - custom WHERE statement
     * @param  String|Array [$fields] - fields to include in results
     * 
     */
    public function getWhere( $where, $fields = '*' ) {
         global $wpdb;
         
         if( is_array( $fields ) ){
            $fields = implode(',', $fields);
         }
        
         $sql = 'SELECT '.$fields.' FROM `' . $this->table_name . '` WHERE '.$where;

         $result = $wpdb->get_results($sql);
         return $result;
    }
    
    
    

    /**
     * Update a table record in the database
     *
     * @param  array  $data           - Array of data to be updated
     * @param  array  $conditionValue - Key value pair for the where clause of the query
     *
     * @return Updated object
     */
    public function update(array $data, array $conditionValue) {
        global $wpdb;

        if (empty($data)) {
            return false;
        }

        $updated = $wpdb->update($this->table_name, $data, $conditionValue);

        return $updated;
    }
    
    
    /**
     * Update a an encryptedtable record in the database
     *
     * @param  array  $data           - Array of data to be updated
     * @param  array  $conditionValue - Key value pair for the where clause of the query
     * @param  array  $encryptedFields - the fields to encrypt
     * @param  string $salt - the salt to use
     * @param  array  [$conditionFormats] - the formats to use with conditions (keys must match conditionValue keys) defaults to =
     *
     * @return Updated object
     */
    public function updateEncrypted(array $data, array $conditionValue, array $encryptedFields, $salt, $formats = array() ) {
        global $wpdb;
        
        foreach( $data as $field => $value ){
             if( !in_array( $field, $encryptedFields ) ){
                $set[] = "`$field` = '$value'";   
             } else {
                $set[] = "`$field` = AES_ENCRYPT('$value','$salt')";
             }
        }
        
        foreach( $conditionValue as $field => $value ){
            if( array_key_exists($field, $formats) ){
                $format = $formats[$field];
            } else {
                $format = '=';
            }
            if( in_array( $field, $encryptedFields ) ){
                $wheres[] = "`$field` $format AES_ENCRYPT('$value', '$salt')";   
            } else {
                $wheres[] = "`$field` $format '$value'";
            }
        }

        $sql = "UPDATE `$this->table_name` SET " . implode( ', ', $set ) . ' WHERE ' . implode( ' AND ', $wheres );

        return $wpdb->query( $sql );
    }
    
    

    /**
     * Delete row on the database table
     *
     * @param  array  $conditionValue - Key value pair for the where clause of the query
     *
     * @return Int - Num rows deleted
     */
    public function delete(array $conditionValue) {
        global $wpdb;

        $deleted = $wpdb->delete($this->table_name, $conditionValue);

        return $deleted;
    }

}