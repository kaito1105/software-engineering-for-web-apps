<?php
/******************************************************
* Notes for Use
*
* 1. Create the object with the pg_list() Constructor Function - parameters below
*       Required Parameters
*       -------------------
*       $query - the database query to produce the data to list
*       $id_field - the primary key field in the data
*       $default_sort_by - The sort by field for the initial display
*       $default_sort_dir -  asc or desc
*
*       Optional Parameters (have default value if ommitted)
*       -------------------
*       $css_class_th - css class for the <th> header row tags
*       $css_class_td - css class for the <td> tags under the header row
*       $cellspacing - for the table
*       $cellpadding - for the table
*       $uses_paging - true or false -- whether all results are displayed vs using pagination
*       $page_length - if using pagination, how many results per page
*       $css_class_row - css class for odd rows
*       $css_class_even - css class for even rows - having odd and even allows shading every other row
*       $css_class_hilite - css class for mouseover row hover states
*
* 2. Add columns with add_column() method - parameters below
*       Required Parameters
*       -------------------
*       $column_name - the DB column whose data creates the listing column
*                      usually just the name of the column, unless an alias for it is used in the SQL query
*       $column_header - text to display in the header row of the listing column
*
*       Optional Parameters (get default value if ommitted)
*       -------------------
*       $format - specifies which formatting to use to transform the column value
*                 the $custom parameter below offers more flexible column formatting
*       $css_class - css class to use only for the tds in this column
*       $column_url - turns column value into a link with this url
*                   - use special notation to embed %%%column_name%%% data from column_name into the URL
*       $on_click - value for the onclick='value'
*       $sortable - true if this column is sortable
*       $blank_message - message for column in case it is empty
*       $custom -  custom formatting type for column - any data fields in the datebase record can be included into the column
*       $extra - extra data to pass in for column construction - useful for $custom column formatting
*
* 3. Initilize with init_list() method
*
* 4. Pour the html into your page with get_html() method - this will create the table

***********************************************************************************************************/

class pg_list {

   var $query;
   var $id_field;
   var $sort_url;
   var $default_sort_by;
   var $default_sort_dir;
   var $sort_by;
   var $sort_dir;
   var $css_class_th;
   var $css_class_td;
   var $css_class_row;
   var $css_class_even;
   var $css_class_hilite;
   var $cellspacing;
   var $cellpadding;
   var $no_results_message;  // set this after calling init_list(0 and before calling get_html() to provide a custom message
   var $columns;
   var $uses_paging;
   var $page_length;  // if uses_paging is set to false, set page_length to 0 to hide showing # of total results
   var $page;
   var $page_count;   // running page counter to coordinate pagination -- propagated by query string
   var $is_initialized;
   var $row_count;   // running row counter while building the table to coordinate pagination
   var $num_rows;    // total number of rows the query pulls from the db, basically a copy of num_rows from the mySQL result set.
   var $extra_data;  /* $extra_data can be assigned BEFORE call to get_html()
                        Sometimes when a column needs to contain an aggregate value (count, sum, etc) it's easier to do a separate SQL query
                        rather than constructing an wildly elaborate query (if even possible) to generate both the list of data rows AND the aggregates.
                        Doing such an extra query inside get_row() is generally not good because it results in an extra DB transaction for EVERY row.
                        Hence the utility of sending in some extra data from another query that all rows can use.
                        */

    //Constructor Function
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function pg_list($query, $id_field, $default_sort_by, $default_sort_dir, $css_class_th = '', $css_class_td = '',
                     $cellspacing = 1 , $cellpadding = 4, $uses_paging = false, $page_length = 50,
                     $css_class_row = '', $css_class_even = '', $css_class_hilite = ''){

        $this->query = $query;
        $this->id_field = $id_field;
        $this->default_sort_by = $default_sort_by;
        $this->default_sort_dir = $default_sort_dir;
        $this->css_class_th = $css_class_th;
        $this->css_class_td = $css_class_td;
        $this->cellspacing = $cellspacing;
        $this->cellpadding = $cellpadding;
        $this->uses_paging = $uses_paging;
        $this->page_length = $page_length;
        $this->css_class_row = $css_class_row;
        $this->css_class_even = $css_class_even;
        $this->css_class_hilite = $css_class_hilite;

   } //end function pg_list


   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   function init_list(){

      /*
        First find out how many rows this list will have. This needs to be known up front since pagination calculations
        such as total number of pages need to know this in advance of the query that actually pulls the data.
      */

      $this->set_total_num_rows();  // sets $this->num_rows

      /*
        The rest of the init checks submitted data regarding the pagination logistics.
        Basically, if pagination directives come in from SESSION or the query string, then those are used.
        Otherwise, the defaults are used.

        One would be tempted to always propagate pagination state from session, but it is often desirable to forget the last pagination state
        when moving to a different listing for example.
        Other times it is desirable to remember pagination state when surfing away from a listing, but then returning to the same listing.
        In that case, set propagate_pageable_list_in_session=yes in a link that returns to a listing page that returns to the list.
      */

      $_GETPOST = array_merge($_GET, $_POST);

      if ( $_GETPOST['propagate_pageable_list_in_session'] && ($_SESSION['current_list'] == $_SERVER['PHP_SELF']) ) {
          $this->page = $_SESSION['current_page'];
          $this->page_count = $_SESSION['current_page_count'];
          $this->page_length = $_SESSION['current_page_length'];
          $this->sort_by = $_SESSION['current_sort_by'];
          $this->sort_dir = $_SESSION['current_sort_dir'];
      }
      // incoming paramaters will override the session ones

      if ($_GETPOST['page']){
         $this->page = $_GETPOST['page'];
      }
      if ($_GETPOST['page_count']){
         $this->page_count = $_GETPOST['page_count'];
      }
      if ($_GETPOST['page_length']){
         $this->page_length = $_GETPOST['page_length'];
      }

      if ($this->uses_paging && !$this->page_length){
         $this->page_length = 50;
      }
      if ($this->uses_paging && !$this->page){
         $this->page = 1;
      }

      //Figure out the sort by and sort order;
      if ($_GETPOST['sort_by']){
         $this->sort_by = $_GETPOST['sort_by'];
         $this->sort_dir = $_GETPOST['sort_dir'];
      }

      //We checked the request supervariable too. Now we give up.
      if (!$this->sort_by){
         $this->sort_by = $this->default_sort_by;
         $this->sort_dir = $this->default_sort_dir;
      }

      $this->sort_url = $_SERVER['PHP_SELF'];

      // This is so in the propagate_pageable_list_in_session clause above, we can avoid potentially using session info from a different list
      $_SESSION['current_list'] = $_SERVER['PHP_SELF'];
      // Save the paging data
      $_SESSION['current_page'] = $this->page;
      $_SESSION['current_page_count'] = $this->page_count;
      $_SESSION['current_page_length'] = $this->page_length;
      $_SESSION['current_sort_by'] = $this->sort_by;
      $_SESSION['current_sort_dir'] = $this->sort_dir;

      $this->is_initialized = true;
   }

   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   function set_total_num_rows(){
      // Wraps the original query as a subquery — MySQL treats it as an in-memory table during the database hit,
      // counts its rows, and returns the count all in a single DB hit.
      $query = "SELECT COUNT(*) as count FROM (" . $this->query . ") AS pg_count_subquery";
      $result = lib::db_query($query);
      $row = $result->fetch();
      $this->num_rows = $row['count'];
   }

   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   function get_html(){
      if (!$this->is_initialized){
         die ("You must initialize the list by calling init_list() before calling get_html().");
      }
      if (!isset($r)) {
         $r = "";
      }

      // used in sortable column headers and the pagination buttons
      $qs_to_propagate = $this->propagate_extra_get_items();

      // Do the hilite script -- could replace this at some point with the even/odd child css selectors
      $r .= "\n<script>\n"
            . "function hilite_row (row, which, css_class) {\n"
            . " if (which) {"
            . " row.className = '" . $this->css_class_hilite . "';"
            . " } else {"
            . " row.className = css_class;"
            . " }\n"
            . "}\n"
            . "</script>\n";

      $r.= "<table class='listing_table' cellpadding='" . $this->cellpadding . "' cellspacing='" . $this->cellspacing . "'>\n";

      $sort_by = $this->sort_by;
      $sort_dir = $this->sort_dir;

      //if we have a sort_url, it's because we need to pass arguments.
      if ($this->sort_url){
         if (strpos ($this->sort_url, '?') === false) {
            $url_delimiter = '?';
         } else {
            $url_delimiter = '&';
         }
      }
      else {
         $this->sort_url = $_SERVER['PHP_SELF'];
         $url_delimiter = '?';
      }//end if

      // Special sort_by values -- more than one column involved
      switch ($sort_by){
          case 'field1_then_field2':
              $sort_by_save = $sort_by;
              $sort_dir_save = $sort_dir;
              $sort_by = ' field1 '.$sort_dir.',field2 '.$sort_dir;  // could hardcode mixtures of ASC / DESC
              $sort_dir = '';
              break;
      }//end switch

      //Now, parse the rows
      $query = $this->query;

      // Whitelist sort_by and sort_dir to prevent SQL injection --
      // column names cannot be parameterized in prepared statements
      $allowed_sort_dirs = ['ASC', 'DESC'];
      $sort_dir_safe = in_array(strtoupper($sort_dir), $allowed_sort_dirs) ? strtoupper($sort_dir) : 'ASC';
      $allowed_columns = array_map(fn($col) => $col->column_name, $this->columns);
      $sort_by_safe = in_array($sort_by, $allowed_columns) ? $sort_by : $this->default_sort_by;

      $query .= " ORDER BY $sort_by_safe $sort_dir_safe ";

      if ( $sort_dir_save ) {
        // put back in place so next sort dir can be determined
        $sort_dir = $sort_dir_save;
      }
      if ( $sort_by_save ) {
        // put back in place so special sorts above don't foul things up
        $sort_by = $sort_by_save;
      }


      $pb = ''; // pagination buttons
      if ($this->uses_paging) {

          $page_offset = ($this->page - 1) * $this->page_length;
          $display_start = $page_offset;
          $display_stop =  $page_offset +  $this->page_length;

          // Augment the query to pull only what the pagination state needs
          $query = $query . " LIMIT ".$this->page_length." OFFSET $display_start ";

          if (($this->num_rows > $this->page_length) && ($this->page_length > 0)){
            $this->page_count = ceil($this->num_rows / $this->page_length);
          }
          else {
            $this->page_count = 1;
          }

          if ($this->num_rows <= $this->page_length) {
            $all_results_display ='&nbsp;<span style="font-size:.75em;font-weight:bold;">showing all '.$this->num_rows.' results</span>';
            $pb .= "<tr><td colspan='" . sizeof ($this->columns) . "'>".$all_results_display."</td></tr>";
          }
          else {
            // More rows than page length
            $page_count_display = '&nbsp;<span style="font-size:.75em;font-weight:bold;">'.$this->num_rows.' results - showing page '.$this->page.' of '.$this->page_count.'</span>';

            $pb .= "<tr><td colspan='" . sizeof ($this->columns) . "'>".$page_count_display."</td></tr>";

            //draw the first page and previous buttons, but disabled if this IS the first page.
            $pb .= "<tr><td colspan='" . sizeof ($this->columns) . "'><table><tr>";
            if ($this->page == 1) {
               $pb .= "<td class='tiny'><input class='disabled' type='button' disabled value=' << '></td>";
               $pb .="<td class='tiny'><input class='disabled' type='button' disabled value=' < '></td>";
            }
            else {
               $pb .= "<td class='tiny'><input type='button' name='page_move' title='Go Back to Page 1' value = ' << ' onclick='location.href=(\""
                   . $this->get_page_url('first').$qs_to_propagate . "\");'></td>";
               $pb .= "<td class='tiny'><input type='button' name='page_move' title='Go Back One Page' value = ' < ' onclick='location.href=(\""
                   . $this->get_page_url('previous').$qs_to_propagate . "\");'></td>";
            } //END if

            //draw the last page and next buttons, but disabled if this IS the last page.
            if ($this->page == $this->page_count) {
               $pb .= "<td class='tiny'><input class='disabled' type='button' disabled value=' > '></td>";
               $pb .= "<td class='tiny'><input class='disabled' type='button' disabled value=' >> '></td>";
            }
            else {
               $pb .= "<td class='tiny'><input type='button' name='page_move' title='Advance One Page' value = ' > ' onclick='location.href=(\"". $this->get_page_url('next').$qs_to_propagate . "\");'></td>";
               $pb .= "<td class='tiny'><input type='button' name='page_move' title='Advance to the Last Page' value = ' >> ' onclick='location.href=(\"". $this->get_page_url('last').$qs_to_propagate . "\");'></td>";
            } //END if
            $pb .= "</tr></table></td></tr>";
          }
      }
      else {
        // doesn't use paging

        if ($this->page_length) {
          $all_results_display ='&nbsp;<span style="font-size:.75em;font-weight:bold;">showing all '.$this->num_rows.' results</span>';
          $pb .= "<tr><td colspan='" . sizeof ($this->columns) . "'>".$all_results_display."</td></tr>";
        }
      }

      $r .= $pb; // paging buttons before data

      if ( $this->num_rows > 0 ) {
          //Do a header row
          $r.= "  <tr class='" . $this->css_class_tr . "'>\n";
          foreach ($this->columns as $col){

             //Can we sort on this field?
             if ($col->sortable){
                //Yes. Draw the clickable header
                if ($col->column_name == $sort_by){
                   $currentSortDir = ($sort_dir == 'DESC' ? 'ASC' : 'DESC');
                }

                // comment out to remove
                // Have to play with this to get looking right, depending on table CSS formatting
                //$up_down_arrows = " <sup style='font-size:90%;vertical-align:baseline;position:relative;top: -0.15em;'>&varr;</sup>";

                $r.= "    <th class='$this->css_class_th'><a href='" . $this->sort_url . $url_delimiter . "sort_by=" . $col->column_name . "&sort_dir=$currentSortDir" . "&page=" . $this->page . "&page_count=" . $this->page_count . "&page_length=" . $this->page_length . $qs_to_propagate. "'>" . $col->column_header . "$up_down_arrows</a></th>\n";
             }
             else {
                //No. Just draw the name.
                $r.= "    <th class='$this->css_class_th'>" . $col->column_header . "</th>\n";
             }//end if
          }//end foreach
          $r.= "  </tr>\n";
      }

      // Now start pumping out the rows
      $result = lib::db_query($query);   // should alter the query and just pull a count(*) here
      $this->row_count = 0;  // running counter for pagination, incremented in get_row() function called in the loop below

      if ( $this->num_rows > 0 ){
        while ($row = $result->fetch()) {
          $r .= $this->get_row($row);
        }
      }
      else {
         $r.= $this->get_no_results_row();
      }

     	$r .= $pb;  // paging buttons after data

      $r.= "</table>";

      $this->sort_by = "";

      return $r;
   } //end function get_html()


   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   function get_row($row){
      //Begin the row

      if (($this->row_count % 2) != 0) {
         $r = "  <tr class='" . $this->css_class_row . "'"
                                       . " onMouseOver='hilite_row(this, true, \"". $this->css_class_row . "\");'"
                                       . " onMouseOut='hilite_row(this, false, \"" . $this->css_class_row . "\");'>";
      } else { // if even row
          $r = "  <tr class='" . $this->css_class_even . "'"
                                       . " onMouseOver='hilite_row(this, true, \"" . $this->css_class_even . "\");'"
                                       . " onMouseOut='hilite_row(this, false, \"" . $this->css_class_even . "\");'>";
      } // end else

      $this->row_count++;

      foreach ($this->columns as $col){
         //if the url has an %%%id%%% token, then replace with the id
         $url = $col->column_url;
         $on_click = $col->on_click;

         //Check for special %%% tokens
         $pattern = "/%%%[A-Za-z0-9_]+%%%/";
         $regs = array();

         //Check in the URL
         while (preg_match($pattern, $url, $regs)){
            $token = $regs[0];
            $token = str_replace('%%%', '', $token);
            $url = str_replace($regs[0], $row[$token], $url);
         }

         //Check in the onClick
         while (preg_match($pattern, $on_click, $regs)){
            $token = $regs[0];
            $token = str_replace('%%%', '', $token);
            $on_click = str_replace($regs[0], $row[$token], $on_click);
         }

         $value = ''; // reset after each row
         switch ($col->custom) {
            case 'custom_column_type':
                // Determine the display value for a custom_column_type
                // Any data in the $row[] fetched from the database is available

                $value = "This is just an example case";
                break;
            case 'column_action_links':
                // provide edit and delete links

                // $col->extra (last parameter passed into create_column) contains the filename for the form

                $value  = '<a href='.$col->extra.'?task=edit&'.$this->id_field.'='.$row[$this->id_field].'>edit</a>';
                $value .= '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;';
                $value .= '<a href="#null" onclick="confirm_delete('.$row[$this->id_field].',\''.$row[$this->default_sort_by].'\')">delete</a>';

                break;
            default:
                // if no custom column type, then run the value through the format_value method

                $value = $col->format_value($row[$col->column_name]);

         } //end $col->custom switch


         if ($value === '') {
            $value = $col->blank_message;
         }

         $css_class = $col->css_class;
         //Begin the table cell
         $r.= "    <td class='" . $css_class . "'>";

         //was a url or an onClick passed?
         if ($url || $on_click) {
            //draw it with a linky-loo
            $r.= "<a href='$url' onClick='$on_click'>$value</a>";
         } else {
            //just draw the value
            $r.= $value;
         }//end if

         //End the table cell
         $r.= "</td>\n";
       }//end foreach

      $r.= "  </tr>";

      return $r;
   } //end function get_row();


   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   function get_no_results_row(){
        if ($this->no_results_message) {
          $message = $this->no_results_message;
        }
        else {
          $message = 'There are currently no records in the database.';
        }
        $colspan = count($this->columns);
        $r = "    <tr class='row'><td colspan='$colspan' class='$this->css_class_td'><i>$message</i></td></tr>\n";
        return $r;
   }  //end function get_no_results_row();


   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   function add_column($column_name, $column_header, $format='', $css_class='', $column_url = '', $on_click = '', $sortable = true, $blank_message = '', $custom = '', $extra = ''){
        $c = new list_column($column_name, $column_header, $sortable, $column_url, $on_click, $format, $css_class, $blank_message, $custom, $extra);
        $this->columns[] = $c;
   }  //end function add_column();


   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   function get_page_url($page){
        if (!$this->is_initialized){
            die ("You must initialize the list by calling init_list() before calling get_page_url().");
        }
        $sort_by = $this->sort_by;
        $sort_dir = $this->sort_dir;
        $bad = false;
        //if we have a sort_url, it's because we need to pass arguments.
        // we may have a sort_url, or may not
        // if we do, it may or may not have already used the '?' char to set parameters
        if ($this->sort_url){
            if (strpos ($this->sort_url, '?') === false) {
               $url_delimiter = '?';
            }
            else {
               $url_delimiter = '&';
            }
        }
        else {
            $this->sort_url = $_SERVER['PHP_SELF'];
            $url_delimiter = '?';
        }

        if ($page == 'unspecified'){
            $r = $this->sort_url . $url_delimiter . "sort_by=" . $sort_by . "&sort_dir=" . $sort_dir . "&page_count=" . $this->page_count . "&page_length=" . $this->page_length;
            return $r;
        }

        if ($page == 'first'){
            $page = 1;
            if ($this->page == 1){
                $bad = true;
            }
        }

        if ($page == 'last') {
            $page = $this->page_count;
            if ($this->page == $this->page_count){
                $bad = true;
            }
        }

        if ($page == 'previous'){
            $page = $this->page - 1;
            if ($page < 1){
                $bad = true;
            }
        }

        if ($page == 'next'){
            $page = $this->page + 1;
            if ($page > $this->page_count){
                $bad = true;
            }
        }

        if ($bad == false){
            $r = $this->sort_url . $url_delimiter . "sort_by=" . $sort_by . "&sort_dir=" . $sort_dir . "&page=" . $page . "&page_count=" . $this->page_count . "&page_length=" . $this->page_length;
            return $r;
        }
        else {
            return false;
        }

    } //end function get_page_url()

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function propagate_extra_get_items() {
        // propagates extra query string items other than what this class uses
        $get_preserve_items = array();
        foreach ( $_GET as $key=>$get_value ) {
          if ( $key != 'sort_by' && $key != 'sort_dir' && $key != 'page' && $key != 'page_count'  && $key != 'page_length' ) {
            // then its something other than what this pageable list tool is using
            if (!is_array($get_value)) {
              $get_preserve_items[] = $key.'='.urlencode(stripslashes($get_value));
            }
          }
        }
        if (  count($get_preserve_items) ) {
          return '&'.implode('&',$get_preserve_items);
        }
    } // end function propagate_extra_get_items()

} //end class pg_list


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
class list_column {
    var $column_name;
    var $column_header;
    var $sortable;
    var $column_url;
    var $on_click;
    var $format;
    var $css_class;
    var $blank_message;
    var $custom;
    var $extra;

    // Constructor
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function list_column($column_name, $column_header, $sortable, $column_url, $on_click, $format, $css_class, $blank_message, $custom, $extra){
        $this->column_name = $column_name;
        $this->column_header = $column_header;
        $this->sortable = $sortable;
        $this->column_url = $column_url;
        $this->on_click = $on_click;
        $this->format = $format;
        $this->css_class = $css_class;
        $this->blank_message = $blank_message;
        $this->custom = $custom;  // refers to custom column formatting
        $this->extra = $extra;
    } // end Constructor function

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function format_value($value){
        switch ($this->format){
            case '':
                return $value;
                break;
            case 'mysql_timestamp':
            	  return date("Y-m-d H:i:s", $value); // 2006-01-01 13:01:01
                break;
            case 'mysql_timestamp_no_seconds':
                // works for raw timestamp or remove seconds from mysql_timestamp
                if (strpos($value,'-') !== false) {
                  $value = strtotime($value);
                }
            	  return date("Y-m-d H:i", $value); // 2006-01-01 13:01
                break;
            case 'sortable_date':
                return date("Y-m-d", $value);
                break;
            case 'convert_to_sortable_date':
                // same as above but converts from human readable format
                return date('Y-m-d',strtotime($value));
                break;
            case 'date_time':
            	  return date("Y-m-d h:i a", $value); // 1/1/06 01:59:59 pm
                break;
            case 'convert_to_date_time':
            	  return date("Y-m-d h:i a", strtotime($value)); // 1/1/06 01:59:59 pm
                break;
                case 'date':
                return date('m/d/Y',$value);
                break;
            case 'convert_to_date':
                // converts from sortable database format to standard human readable
                return date('m/d/Y',strtotime($value));
                break;
            case 'GMT_to_local':
                $timestamp = strtotime($value .  ' GMT');
                return date('Y-m-d h:i:s a',$timestamp);
                break;
            case 'yesno':
            	 // turn a 0/1 field into Yes/No
                if ($value){
                    return 'Yes';
                }
                else {
                    return 'No';
                }
                break;
            case 'abbreviated':
                if(strlen($value) > 10){
                    $value = substr($value, 0, 9) . "...";
                }// end if
                return $value;
                break;
            case 'json_array_to_comma_delimited':
                return implode(', ',json_decode($value));
                break;
            case 'young_or_old':
                if( $value > 60 ){
                	$value = 'Old';
                }
                else {
                	$value = 'Young';
                }
                return $value;
                break;
            default:
                die ("There is no format called '$this->format' for columns.");
        } //end switch
    } // end function format_value($value)

}//end class list_column

?>