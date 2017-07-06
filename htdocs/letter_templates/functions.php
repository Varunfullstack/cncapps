<?php
/*
  Copyright(c) 2006 DataLink UK Ltd
  sage sync
  version 0.02
*/

function CDT($thetext)
{
  return '<![CDATA[' . $thetext . ']]>';
}

function CDT64E($thetext)
{
  return '<![CDATA[' . base64_encode($thetext) . ']]>';
//  return '<![CDATA[' . $thetext . ']]>';
}

function B64E($thetext)
{
  return base64_encode($thetext);
}

function B64D($thetext)
{
  return base64_decode($thetext);
}

function save_to_file($file_name, $type, $content, $permission = 0644)
{
  $fp = fopen($file_name, $type);
  fputs($fp, $content);
  fclose($fp);
  chmod($file_name, $permission);
}


function get_maufacturers_id($manufacturers_name){
   global $lng;

   if ($manufacturers_name<>''){

      $man_query = tep_db_query("select manufacturers_id   from " . TABLE_MANUFACTURERS . " where upper(manufacturers_name) = '" . tep_db_input(strtoupper($manufacturers_name)) . "'");
      if (tep_db_num_rows($man_query)>0){
        $man = tep_db_fetch_array($man_query);
        return  $man['manufacturers_id'];   
      } else{
        tep_db_query("insert into " . TABLE_MANUFACTURERS . " (manufacturers_name) values ('" . tep_db_input($manufacturers_name) . "')");

        return  tep_db_insert_id();
      }
    } else {
        return 0;
    }

/*      $man_query = tep_db_query("select manufacturers_id   from " . TABLE_MANUFACTURERS_INFO . " where upper(manufacturers_name) = '" . strtoupper(tep_db_input($manufacturers_name)) . "'");
      if (tep_db_num_rows($man_query)>0){
        $man = tep_db_fetch_array($man_query);
        return  $man['manufacturers_id'];   
      } else{
        tep_db_query("insert into " . TABLE_MANUFACTURERS . " (manufacturers_image) values ('')");

        $maufacturers_id = tep_db_insert_id();

        foreach ($lng->catalog_languages as $lang_code => $lang)
        {
          $languages_id = $lang['id'];
          tep_db_query("insert into " . TABLE_MANUFACTURERS_INFO . " (manufacturers_id, languages_id, manufacturers_name) values ('" . (int)$maufacturers_id . "', '" . (int)$languages_id . "', '" . tep_db_input($manufacturers_name) . "')");
        }

        return $maufacturers_id;
      }
    } else { 
        return 0;
    }
*/
}


function fill_from_log_file($type_of_array, $file_name){
  global $all_products_array,$all_inventory_array,$all_attributes_array;
  if(filesize ($file_name)>0) {
    $fPointer = fopen($file_name, "r");
    $ell_array = array();
    while ($data = fgetcsv ($fPointer, 10240, ";")){
      $ell_array[] = $data;
    }
    fclose ($fPointer);
    foreach($ell_array as $array_rec){
      $array_id             = trim($array_rec[0]);                         
      switch ($type_of_array) {
          case 0:
              $all_products_array[] = $array_id;
              break;
          case 1:
              $all_attributes_array[] = $array_id;
              break;
          case 2:
              $all_inventory_array[] = $array_id;
              break;
      }
    }
  }
}

function get_array_csv($ell_array){
  $result = '';

  foreach($ell_array as $array_rec){
    $result .= $array_rec . ';' . "\r\n";
  }
  return $result;
}

function tep_save_text_for_csv($thetext)
{
  $thetext = str_replace("\r", ' ', $thetext);
//  $thetext = str_replace("\n", "\t", $thetext);
//	$thetext = str_replace("\t", ' ', $thetext);
  $thetext = str_replace('\"', '"', $thetext);
  $thetext = str_replace('"', '""', $thetext);
  return $thetext;
}

///////ATTRIBUTE INVENTORY POLITICS
function get_options_id($name){
  global $current_language_id;

  $options_query = tep_db_query("select products_options_id from " . TABLE_PRODUCTS_OPTIONS . " where upper(products_options_name)  = '" . strtoupper(tep_db_input($name)) . "'");
  if (tep_db_num_rows($options_query)>0){
//      print 'update o'.'<br>';
    $options = tep_db_fetch_array ($options_query);
    return $options['products_options_id'];
  } else {
//      print 'insert o'.'<br>';
    $products_options_id = 1;

    $options_query =  tep_db_query("select max(products_options_id) as max_id  from " . TABLE_PRODUCTS_OPTIONS ); 
    if ($options = tep_db_fetch_array($options_query) ){
      $products_options_id = $options['max_id'] ;
      $products_options_id ++;
    }

    tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, language_id, products_options_name) values ('" . (int)$products_options_id . "', '" . (int)$current_language_id . "', '" . tep_db_input($name) . "')");

    return $products_options_id;
  }
}
function get_options_value_id($options_id,$value){

  global $current_language_id;
    // Insert
  $options_values_query = tep_db_query("select products_options_values_id from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where upper(products_options_values_name)  = '" . tep_db_input($value) . "'");

  if (tep_db_num_rows($options_values_query)>0){
//      print 'update ov'.'<br>';
    $options_values = tep_db_fetch_array ($options_values_query);
    return $options_values['products_options_values_id'];
  } else {
//      print 'insert ov'.'<br>';
    $products_options_values_id = 1;
    $options_value_query =  tep_db_query("select max(products_options_values_id) as max_id  from " . TABLE_PRODUCTS_OPTIONS_VALUES ); 
    if ($options_value = tep_db_fetch_array($options_value_query) ){
      $products_options_values_id = $options_value['max_id'] ;
      $products_options_values_id ++;
    }

    tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) values ('" . (int)$products_options_values_id . "', '" . (int)$current_language_id . "', '" . tep_db_input($value) . "')");

    tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_id, products_options_values_id) values ('" . (int)$options_id . "', '" . (int)$products_options_values_id . "')");
    return $products_options_values_id; 
  }
}
//
function get_attributes_id($options_id,$options_values_id,$products_id,$attributes_price){

    $sql_data_array = array(
                            'products_id' => $products_id,
                            'options_id' => $options_id,
                            'options_values_id' => $options_values_id,
                            'price_prefix' => ($attributes_price < 0 ? '-' : '+'),
                            'options_values_price' => abs($attributes_price),
                       

//                            'products_attributes_weight_prefix' => ($attributes_weight < 0 ? '-' : '+'),
//                            'products_attributes_weight' => abs($attributes_weight),
                             );

  $attr_query = tep_db_query("select    products_attributes_id   from " . TABLE_PRODUCTS_ATTRIBUTES . " where    products_id  = '" . (int)$products_id . "' and  options_id = '" .(int)$options_id  . "' and options_values_id = '" . (int)$options_values_id  ."'" );
  
  if (tep_db_num_rows($attr_query)>0){
    $attribute = tep_db_fetch_array ($attr_query);

    tep_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $sql_data_array, 'update', "products_attributes_id = '" . (int)$attribute['products_attributes_id'] . "'");
    return $attribute['products_attributes_id'];

  } else {

    tep_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $sql_data_array);
    return   tep_db_insert_id();
  }

}

?>
