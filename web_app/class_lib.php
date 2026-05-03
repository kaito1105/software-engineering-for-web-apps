<?php
/*
This class is just a wrapper for some useful php utility functions.

All the methods are Static - you don't have to instantiate a lib object to call them.

Simply do

lib::function_name()

*/

class lib {

    /******************************************************************************************
    Function for database queries with built in SQL error reporting
    ******************************************************************************************/

    /*
    Makes a PDO query using a pre-existing $pdo DB connection.
    Input:  Valid SQL query string, optional array of $placeholders (named or ?)
            If no $placeholders are supplied, uses query() instead of prepare()/execute()
    Returns: Result of Query or triggers Error
    */
    ////////////////////////////////////////////////////////////////////////////////////////////
    static function db_query($query, $placeholders = []) {
        global $pdo;

        try {
            if (empty($placeholders)) {
                return $pdo->query($query);
            }
            $stmt = $pdo->prepare($query);
            $stmt->execute($placeholders);
            return $stmt;
        } catch (PDOException $e) {
            trigger_error("Query Failed! SQL: $query - Error: " . $e->getMessage(), E_USER_ERROR);
        }
    }

    /******************************************************************************************
    Functions to help with HTML forms and other utility functions
    ******************************************************************************************/

    /*
    Converts text line breaks into HTML line breaks for textarea form elements
    Input: String of text
    Returns: String of text with line breaks converted into <br>
    */
    ////////////////////////////////////////////////////////////////////////////////////////////
    static function textarea_to_html($text) {
        $r = str_replace("\r\n", '<br>', $text);
        $r = str_replace("\n", '<br>', $r);
        return $r;
    }

    /*
    Builds an HTML select menu
    Input: Menu name, Assoc array for the menu data, various optional features for menu
      The array keys become the menu's hidden values, and array values become the visible text on the menu.
    Returns: String containing the menu
    */
    ////////////////////////////////////////////////////////////////////////////////////////////
    function menu_from_assoc_array($name, &$array, $dummy_option='', $selected='', $multiple='', $event_handler='', $extra_attributes='') {

      // For Convenience, the ID is set to be the same as the name.
      // If the name has [] (for multiple selection), it's removed for the ID value.
      $id = str_replace(array('[',']'),'',$name);

      $menu = "<select name=\"$name\" id=\"$id\" $event_handler $multiple $extra_attributes >\n";

      if ($dummy_option) {
          // Option such as "Choose Item" for first item of single selection menu
          $menu .= "<option value=\"-1\" ";
          if ($default == '' ) {
              $menu .= "selected='yes'";
          }
          $menu .= ">$dummy_option</option>\n";
      }

       foreach ($array as $key=>$value) {
            $menu .= "<option value=\"$key\" ";
            if ( (!is_array($selected) && $key==$selected) || (is_array($selected) && in_array($key,$selected)) ) {
                $menu .= "selected='yes'";
            }
            $menu .= ">$value</option>\n";
       }
       $menu .= "</select>\n";
       return $menu;
    }



    /******************************************************************************************
    Function for Date formatting
    ******************************************************************************************/

    /*
    Computes Human Readable dates from raw timestamps
    Input: Raw Timestamp and PHP date format
    Returns: Date string with input format
    */
    ////////////////////////////////////////////////////////////////////////////////////////////
    static function nice_date ($timestamp, $format) {
        if (!$timestamp) {return 'timestamp error';}

        if ($timestamp == 'now') {
            $timestamp = time();
        }

        switch ($format) {
            case 'date' :
                // 01/01/2006
                $form = 'm/d/Y';
                break;
            case 'datetime' :
                // 1/1/06 01:59:59 PM
                $form = 'm/d/Y h:i:s A';
                break;
            case 'military_datetime' :
                // 01/01/2006 13:59:59
                $form = 'm/d/Y H:i:s';
                break;
            case 'descriptive_date' :
                // Monday, August 8, 2018
                $form = 'l, F j, Y';
                break;
            case 'mysql_timestamp' :
                // sortable date format
                // 2006-01-01 13:01:01
                $form = 'Y-m-d H:i:s';
                break;
            case 'mysql_datestamp' :
                // sortable date format
                // 2006-01-01
                $form = 'Y-m-d';
                break;
            case 'minimal_time' :
                // sortable date format
                // 9:00 whether am or pm
                $form = 'g:i';
                break;
        }
        return date($form,$timestamp);
    } // end nice_date

} //end class lib
