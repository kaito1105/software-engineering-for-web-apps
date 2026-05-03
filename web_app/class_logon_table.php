<?php
// Table-specific class - extends the data_operations class to apply to a specific database table.

class logon extends data_operations {

  public function __construct() {

    $table = LOGON_TABLE;              // Constant defined in init.php
    $id_field = 'logon_token';               // Primary Key field
    $id_field_is_ai = false;             // Is Primary Key Auto Increment?
    $fields = array(                    // Array of all non-PK fields
      'logon_id',
      'logon_created_at',
      'logon_last_transaction',
      'logon_ip_address'
    );

    // Parent class Constructor: Sending table-specific information to the abstract class_data_operations
    // enables usage of the abstract methods such as load() and save() but applied specifically to this table.  
    parent::__construct($table, $id_field, $id_field_is_ai, $fields);
  }

  /*
    At this point, this ORM table class has access to all abstract methods from class_data_operations.
    That functionality is sufficient for operations on 1 Active Record.
    
    More complex data operations involving JOINS, etc are usually still required. 
    One such method is defined below. 
    
    Collecting such data-related methods in the ORM table classes keeps SQL out of the 
    application logic in other PHP files helping to enforce the MVC separation of labor model. 
    
    It it quite common for table classes to have several (or many) custom methods such as below. 
  */
  
  //////////////////////////////////////////////////////////////////////////////////////////////
  // Custom Methods for Data Operations
  //////////////////////////////////////////////////////////////////////////////////////////////

  /*
  Gets a person, together with an array of states visited.
  Returns all people if called with no primary key value: get_people_with_states()
  */
  //////////////////////////////////////////////////////////////////////////////////////////////
  public static function get_logon($logon_id='') {
    
    $where_clause = "TRUE";  // defaults to all people:  WHERE TRUE
    $placehold = [];
    
    if ($logon_id !== '') {
      $where_clause = "logon_id = :logon_id";
      $placehold[':logon_id'] = $logon_id;
      $order = 'logon_created_at';
    }

    // LEFT JOIN - still get a person if there are no matches in states table
    $sql = "SELECT * FROM " . LOGON_TABLE . " WHERE $where_clause ORDER BY $order ASC";

    $result = lib::db_query($sql, $placehold);

    // Filter out the redundency from the JOIN
    $logon = [];
    while ( $row = $result->fetch() ) {
      $logon[$row['logon_token']]['logon_id'] = $row['logon_id'];
      $logon[$row['logon_token']]['logon_created_at'] = $row['logon_created_at'];
      $logon[$row['logon_token']]['logon_last_transaction'] = $row['logon_last_transaction'];
      $logon[$row['logon_token']]['logon_ip_address'] = $row['logon_ip_address'];
    }
    return $logon;
  }

} //end class
