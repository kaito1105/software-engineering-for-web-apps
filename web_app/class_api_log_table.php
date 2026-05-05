<?php
// Table-specific class - extends the data_operations class to apply to a specific database table.

class api_log extends data_operations {

  public function __construct() {

    $table = API_LOG_TABLE;              // Constant defined in init.php
    $id_field = 'api_log_id';               // Primary Key field
    $id_field_is_ai = true;             // Is Primary Key Auto Increment?
    $fields = array(                    // Array of all non-PK fields
      'api_log_user_id',
      'api_log_form_id',
      'api_log_timestamp',
      'api_log_method',
      'api_log_http_code',
      'api_log_token'
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
  public static function get_api_log($api_log_id='') {
    
    $where_clause = "TRUE";  // defaults to all people:  WHERE TRUE
    $placehold = [];
    
    if ($api_log_id !== '') {
      $where_clause = "api_log_id = :api_log_id";
      $placehold[':api_log_id'] = $api_log_id;
      $order = 'api_log_timestamp';
    }

    // LEFT JOIN - still get a person if there are no matches in states table
    $sql = "SELECT * FROM " . API_LOG_TABLE . " WHERE $where_clause ORDER BY $order ASC";

    $result = lib::db_query($sql, $placehold);

    // Filter out the redundency from the JOIN
    $api_log = [];
    while ( $row = $result->fetch() ) {
      $api_log[$row['api_log_id']]['api_log_user_id'] = $row['api_log_user_id'];
      $api_log[$row['api_log_id']]['api_log_form_id'] = $row['api_log_form_id'];
      $api_log[$row['api_log_id']]['api_log_timestamp'] = $row['api_log_timestamp'];
      $api_log[$row['api_log_id']]['api_log_method'] = $row['api_log_method'];
      $api_log[$row['api_log_id']]['api_log_http_code'] = $row['api_log_http_code'];
      $api_log[$row['api_log_id']]['api_log_token'] = $row['api_log_token'];
    }
    return $api_log;
  }

} //end class
