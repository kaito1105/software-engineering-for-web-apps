<?php
// Table-specific class - extends the data_operations class to apply to a specific database table.

class user extends data_operations {

  public function __construct() {

    $table = USERS_TABLE;              // Constant defined in init.php
    $id_field = 'user_id';               // Primary Key field
    $id_field_is_ai = true;             // Is Primary Key Auto Increment?
    $fields = array(                    // Array of all non-PK fields
      'user_name',
      'user_email',
      'user_password',
      'user_created_at',
      'user_ip_address',
      'user_api_token',
      'user_is_admin'
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
  public static function get_user($user_id='') {
    
    $where_clause = "TRUE";  // defaults to all people:  WHERE TRUE
    $placehold = [];
    
    if ($user_id !== '') {
      $where_clause = "user_id = :user_id";
      $placehold[':user_id'] = $user_id;
    }

    // LEFT JOIN - still get a person if there are no matches in states table
    $sql = "SELECT * FROM " . USERS_TABLE . " WHERE $where_clause ";

    $result = lib::db_query($sql, $placehold);

    // Filter out the redundency from the JOIN
    $user = [];
    while ( $row = $result->fetch() ) {
      $user[$row['user_id']]['user_name'] = $row['user_name'];
      $user[$row['user_id']]['user_email'] = $row['user_email'];
      $user[$row['user_id']]['user_password'] = $row['user_password'];
      $user[$row['user_id']]['user_created_at'] = $row['user_created_at'];
      $user[$row['user_id']]['user_ip_address'] = $row['user_ip_address'];
      $user[$row['user_id']]['user_api_token'] = $row['user_api_token'];
      $user[$row['user_id']]['user_is_admin'] = $row['user_is_admin'];
    }
    return $user;
  }

  public static function get_user_from_api_token($token) {
    if (empty($token)) return -1;

    $sql = "SELECT user_id FROM " . USERS_TABLE . " WHERE user_api_token = :token";
    $result = lib::db_query($sql, [':token' => $token]);
    $row = $result->fetch();

    return $row ? $row['user_id'] : -1;
  }

} //end class
