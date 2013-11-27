<?php
/**
 * Interaction with the database on a custom Level
 * 
 * @since 11.27.13
 * 
 * @author Mat Lipe <mat@matlipe.com>
 * 
 * @uses extend as an abstract or use as is
 * 
 * @uses new MvcDatabase(%table%);
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
        $this->$table_name = $table_name;
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

        $wpdb->insert($this->$table_name, $data);

        return $wpdb->insert_id;
    }
    
    

    /**
     * Get all from the selected table
     *
     * @param  String $orderBy - Order by column name
     *
     * @return Table result
     */
    public function getResults($orderBy = NULL) {
        global $wpdb;

        $sql = 'SELECT * FROM `' . $this->$table_name . '`';

        if (!empty($orderBy)) {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        $all = $wpdb->get_results($sql);

        return $all;
    }
    
    

    /**
     * Get a value by a condition
     *
     * @param  Array $conditionValue - A key value pair of the conditions you want to search on
     * @param  String $condition - A string value for the condition of the query default to equals
     *
     * @return Table result
     */
    public function getWhere(array $conditionValue, $condition = '=') {
        global $wpdb;

        $sql = 'SELECT * FROM `' . $this->$table_name . '` WHERE ';

        foreach ($conditionValue as $field => $value) {
            switch(strtolower($condition)) {
                case 'in' :
                    if (!is_array($value)) {
                        throw new Exception("Values for IN query must be an array.", 1);
                    }

                    $sql .= $wpdb->prepare('`%s` IN (%s)', $field, implode(',', $value));
                    break;

                default :
                    $sql .= $wpdb->prepare('`' . $field . '` ' . $condition . ' %s', $value);
                    break;
            }
        }

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

        $updated = $wpdb->update($this->$table_name, $data, $conditionValue);

        return $updated;
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

        $deleted = $wpdb->delete($this->$table_name, $conditionValue);

        return $deleted;
    }

}
?>