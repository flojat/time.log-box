<?php

function date_EN_to_CH($date){
        if(!checkdate(date("m",strtotime($date)),date("d",strtotime($date)),date("Y",strtotime($date)))) {
                        return FALSE;
        }
  if($date=="0000-00-00"){
                return NULL;
        } else if(strpos($date,"-")==FALSE) {
                return $date;
        } else {
    return date("d.m.Y",strtotime($date));
        }
}
function date_CH_to_EN($date){
        if(strpos($date,".")==FALSE) {
                return $date;
        } else {
                $date= preg_replace("|\b(\d+).(\d+).(\d+)\b|", "\\3-\\2-\\1", $date);
                if(checkdate(date("m",strtotime($date)),date("d",strtotime($date)),date("Y",strtotime($date)))) {
                        return $date;
                } else {
                        return FALSE;
                }
        }
}




function display_db_query($query_string, $header_bool, $table_params) {
	// perform the database query
	$result_id = mysql_query($query_string)
	or die("display_db_query:" . mysql_error());

  // find out the number of columns in result
	$column_count = mysql_num_fields($result_id)
	or die("display_db_query:" . mysql_error());

  // Here the table attributes from the $table_params variable are added
	print("<table $table_params >\n");
	// optionally print a bold header at top of table


  if($header_bool) {
		print("<tr>");
		for($column_num = 0; $column_num < $column_count; $column_num++) {
			$field_name = mysql_field_name($result_id, $column_num);
			print("<th>$field_name</th>");
		}
		print("</tr>\n");
	}


	// print the body of the table
	while($row = mysql_fetch_row($result_id)) {
		print("<tr>");
		for($column_num = 0; $column_num < $column_count; $column_num++) {
			print("<td>$row[$column_num]</td>\n");
		}
		print("</tr>\n");
	}
	print("</table>\n");
}


function getProjectList($tag_id_name, $selected_item=0, $text_null="please choose"){

  $query = mysql_query("  SELECT projects.id, projects.name, clients.company, CONCAT(clients.firstname,' ',clients.name)
                          FROM `projects`,`clients`
                          WHERE clients.Id = projects.client_id
                      ");

  $select="<label for=\"$tag_id_name\">". str_replace('_', ' ', $tag_id_name)."</label> <select id=\"$tag_id_name\" name=\"$tag_id_name\">
              <option value=0>$text_null</option>\n";

              while(list($id,$projectname,$company,$clientname)=@mysql_fetch_row($query)){

                    if($id == $selected_item){
                      $select.="<option value=$id selected=selected >($id) $projectname, $company, $clientname </option>\n";
                    }else{
                      $select.="<option value=$id>($id) $projectname, $company, $clientname  </option>\n";
                    }
              }
  $select.="</select>\n";
  return $select;
}


function getUserList($tag_id_name, $selected_item=0, $text_null="please choose"){

  $query = mysql_query("  Select id, CONCAT(firstname, ' ', name) FROM user WHERE 1");

  $select="<label for=\"$tag_id_name\">". str_replace('_', ' ', $tag_id_name)."</label> <select placeholder='$text_null' id=\"$tag_id_name\" name=\"$tag_id_name\">

              <option value='0'>$text_null</option>\n";

              while(list($id,$username)=@mysql_fetch_row($query)){

                    if($id == $selected_item){
                      $select.="<option value=$id selected=selected >($id) $username</option>\n";
                    }else{
                      $select.="<option value=$id>($id) $username</option>\n";
                    }
              }
  $select.="</select>\n";
  return $select;
}


function getClientList($tag_id_name, $selected_item=0, $tag_width=300, $text_null="please choose"){

  $query = mysql_query("  Select id, CONCAT(company,' ',firstname, ' ', name) FROM clients WHERE 1");

  $select="<label for=\"$tag_id_name\">". str_replace('_', ' ', $tag_id_name)."</label> <select placeholder='$text_null' id=\"$tag_id_name\" name=\"$tag_id_name\">

              <option value='0'>$text_null</option>\n";

              while(list($id,$username)=@mysql_fetch_row($query)){

                    if($id == $selected_item){
                      $select.="<option value=$id selected=selected >($id) $username</option>\n";
                    }else{
                      $select.="<option value=$id>($id) $username</option>\n";
                    }
              }
  $select.="</select>\n";
  return $select;
}


?>
