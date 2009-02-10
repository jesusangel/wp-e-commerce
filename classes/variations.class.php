<?php
class nzshpcrt_variations {  
  function nzshpcrt_variations() { 
    global $wpdb;
	}
	
	
	
  function list_variations($product_id = null) {
    // create a list of checkboxes to associate variations with products
    // if a product ID is supplied, displays variations associated with that product.
  	global $wpdb;
    $options = "";
    $variations = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}product_variations` ORDER BY `id` ASC",ARRAY_A);
    //$options .= "<option  $selected value='0'>".TXT_WPSC_PLEASECHOOSE."</option>\r\n";
    /*
    if($product_id > 0) {
      $if_adding = '';
    } else {
      $if_adding = 'add_';
    }*/
    $options .= "<div class='variation_checkboxes'>\n\r";
    foreach((array)$variations as $variation) {
      $variation_values = array();    
      $checked = "";
      
			$values_box_state = "style='display: none;' ";
      if($product_id > 0) {
        // if the product ID is greater than 0, check to see if the variation is associated.
        $check_variation = $wpdb->get_var("SELECT `id` FROM `{$wpdb->prefix}variation_associations` WHERE `type` IN ('product') AND `associated_id` IN ('{$product_id}') AND `variation_id` IN ('{$variation['id']}') LIMIT 1");
        if($check_variation > 0) {
          $checked = "checked='true'";
					$values_box_state = "";
        }
      }
    
      $options .= "  <div class='variation_box'>\n\r";
      $options .= "    <label class='variation_checkbox{$product_id}'><input type='checkbox' $checked onchange='{$if_adding}variation_value_list(\"{$product_id}\", jQuery(this).parents(\"div.variation_box\"));' value='1' name='variations[{$variation['id']}]' class='variation_checkbox' >{$variation['name']}</label>\n\r";
      /**
      *  get the list of variation values
			*  need different input names for editing and adding due to using different keys
			*/
      if(($product_id > 0) && ($check_variation > 0)) {
				$variation_values = $wpdb->get_results("SELECT `a`.`visible`, `b`.*  FROM `{$wpdb->prefix}variation_values_associations` AS `a` JOIN `{$wpdb->prefix}variation_values` AS `b` ON `a`.`value_id` = `b`.`id`  WHERE `a`.`product_id` IN('{$product_id}') AND `a`.`variation_id` IN('{$variation['id']}')",ARRAY_A);
			}
			if(count($variation_values) < 1) {
				$variation_values = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}variation_values` WHERE `variation_id` IN('{$variation['id']}')",ARRAY_A);
			}
			
				
      
				// display the list of variation values
				$options .= "    <div class='variation_values_box' $values_box_state>\n\r";
				foreach($variation_values as $variation_value) {
					$checked = '';
					$variation_value['name'] = stripslashes($variation_value['name']);
					if($variation_value['visible'] > 0) {
						$checked = "checked='true'";
					}
					$options .= "     <label class='variation_checkbox{$product_id}'><input type='hidden' value='0' name='edit_variation_values[{$variation_value['id']}]' /><input type='checkbox' $checked value='1' onchange='{$if_adding}variation_value_list(\"{$product_id}\", jQuery(this).parents(\"div.variation_box\"));' name='edit_variation_values[{$variation_value['id']}]' />{$variation_value['name']}</label>\n\r";
				}
				$options .= "    </div>\n\r";
				$options .= "  </div>\n\r";
      }
    $options .= "</div>\n\r";
    return $options;
  }
    
  function display_variation_values($prefix,$variation_id) {
    global $wpdb;
    if(is_numeric($variation_id)) {
      $variation_values = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}variation_values` WHERE `variation_id` = '{$variation_id}' ORDER BY `id` ASC",ARRAY_A);
      if($variation_values != null) {
			}
		}
    return $output;
	}
    
  
  function falsepost_variation_values($variation_id) {
    global $wpdb;
    if(is_numeric($variation_id)) {
      $variation_values = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}variation_values` WHERE `variation_id` = '{$variation_id}' ORDER BY `id` ASC",ARRAY_A);
      if($variation_values != null) {
        foreach($variation_values as $variation_value) {
          if(isset($_POST['edit_add_variation_values'])) {
            if($_POST['edit_variation_values'][$variation_value['id']] == 1) {
              $output_variation_values[$variation_id][$variation_value['id']]['active'] = 1;
            } else {
							$output_variation_values[$variation_id][$variation_value['id']]['active'] = 0;
            }
          } else {
						$output_variation_values[$variation_id][$variation_value['id']]['active'] = 1;
				  }
          $output_variation_values[$variation_id][$variation_value['id']]['blank'] = 'null';
				}
			}
		}
    return $output_variation_values;
	}
    
    
  function add_to_existing_product($product_id,$variation_list) {
     global $wpdb;
    if(is_numeric($product_id)) {
      foreach($variation_list as $variation_id => $variation_values) {
        if(is_numeric($variation_id)) {
          $num = 0;
          $variation_assoc_sql = "INSERT INTO `{$wpdb->prefix}variation_associations` ( `type` , `name` , `associated_id` , `variation_id` ) VALUES ( 'product', '', '{$product_id}', '{$variation_id}');";

          $product_assoc_sql = "INSERT INTO `{$wpdb->prefix}variation_values_associations` ( `product_id` , `value_id` , `quantity` , `price` , `visible` , `variation_id` ) VALUES";
          foreach($variation_values as $variation_value_id => $variation_value_properties) {
            if(is_numeric($variation_value_id)) {
              switch($num) {
                case 0:
                $comma = '';
                break;
                
                default:
                $comma = ', ';
                break;
							}
                
              if(is_numeric($variation_value_properties['price']) && ($variation_value_properties['price'] > 0))
                {
                $price = $variation_value_properties['price'];
							} else {
								$price = '';
							}
                
              if($variation_value_properties['active'] == 1) {
                $active = 1;
							} else {
								$active = 0;
							}
              
              if(is_numeric($variation_value_properties['stock']) && ($variation_value_properties['stock'] > 0)) {
                $quantity = $variation_value_properties['stock'];
							} else {
								$quantity = 0;
							}
              
              $product_assoc_sql .= "$comma ( '$product_id', '$variation_value_id', '$quantity', '".$price."', '$active', '$variation_id')";
              $num++;
						}
					}
          $product_assoc_sql .= ";";
          $wpdb->query($product_assoc_sql);
          $wpdb->query($variation_assoc_sql);
				}
			}
		}
    return $output;
	}
  
  function variations_grid_view($product_id, $variation_values = null) {
    global $wpdb;
    $product_id = (int)$product_id;
		$product_data = $wpdb->get_row("SELECT `price`, `quantity_limited` FROM `{$wpdb->prefix}product_list` WHERE `id` IN ('{$product_id}') LIMIT 1", ARRAY_A);
    $product_price = $product_data['price'];
    $stock_column_state = '';
    if($product_data['quantity_limited'] == 0) {
      $stock_column_state = " style='display: none;'";
    }
    
    
    $associated_variations = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}variation_associations` WHERE `type` IN ('product') AND `associated_id` = '{$product_id}' ORDER BY `id` ASC",ARRAY_A);
    $variation_count = count($associated_variations);
//  $grid_header = str_replace(Array(":variation1:", ":variation2:"),Array($variation_name1, $variation_name2) ,TXT_WPSC_VARIATION_GRID_CONTROL_PAIR);
    
    
    
    if($variation_count > 0) {
      $output .= "<table class='product_variation_grid'>\n\r";   
      $output .= "  <tr>\n\r";
      $output .= "    <th class='variations titles'>".TXT_WPSC_VARIATION."</th>\n\r";
      $output .= "    <th class='titles stock' $stock_column_state >".TXT_WPSC_STOCK."</th>\n\r";
      $output .= "    <th class='titles price'>".TXT_WPSC_PRICE."</th>\n\r";
      //$output .= "    <th class='titles weight'>".TXT_WPSC_WEIGHT."</th>\n\r";
      $output .= "    <th class='titles'>".TXT_WPSC_MORE."&nbsp;</th>\n\r";
      $output .= "  </tr>\n\r";
      
      foreach((array)$associated_variations as $key => $associated_variation) {
        $variation_id = (int)$associated_variation['variation_id'];
        $excluded_values = $wpdb->get_col("SELECT `value_id` FROM `{$wpdb->prefix}variation_values_associations` WHERE `product_id` IN('{$associated_variation['associated_id']}') AND `variation_id` IN ('{$variation_id}') AND `visible` IN ('1')");
        
				$included_value_sql = "AND `b{$variation_id}`.`value_id`  IN('".implode("','", $excluded_values)."')";
      
        // generate all the various bits of SQL to bind the tables together
        $join_selected_cols[] = "`b{$variation_id}`.`value_id` AS `value_id{$variation_id}`";
        $join_tables[] = "`".$wpdb->prefix."wpsc_variation_combinations` AS `b{$variation_id}`";
        $join_on[] = "`a`.`id` = `b{$variation_id}`.`priceandstock_id`";
        $join_conditions[] = "`b{$variation_id}`.`variation_id` = '{$variation_id}' AND `b{$variation_id}`.`all_variation_ids` IN (':all_variation_ids:') $included_value_sql";
        $join_order[] = "`value_id{$variation_id}` ASC";
        
        // also store the columns in which the value ID's are, because we need them later
        $table_columns[] = "value_id{$variation_id}";
        
        $selected_variations[] = $variation_id;
        
        $get_variation_names = $wpdb->get_results("SELECT `id`, `name` FROM `{$wpdb->prefix}variation_values` WHERE `variation_id` = '{$variation_id}'", ARRAY_A);
        
        foreach((array)$get_variation_names as $get_variation_name) {
          $variation_names[$get_variation_name['id']] = $get_variation_name['name'];
        }
      }
      
      // implode the SQL statment segments into bigger segments
      $join_selected_cols = implode(", ", $join_selected_cols);
      $join_tables = implode(" JOIN ", $join_tables);
      $join_on = implode(" AND ", $join_on);
      $join_conditions = implode(" AND ", $join_conditions);
      $join_order = implode(", ", $join_order);
      
      
      
      asort($selected_variations);      
      $all_variation_ids = implode(",", $selected_variations);
      
      $join_conditions = str_replace(":all_variation_ids:",$all_variation_ids, $join_conditions );
      //exit("SELECT `a`.*, {$join_selected_cols} FROM  `{$wpdb->prefix}variation_priceandstock` AS `a` JOIN {$join_tables} ON {$join_on} WHERE `a`.`product_id` = '$product_id' AND {$join_conditions} ORDER BY {$join_order}");
      // Assemble and execute the SQL query
      $associated_variation_values = $wpdb->get_results("SELECT `a`.*, {$join_selected_cols} FROM  `{$wpdb->prefix}variation_priceandstock` AS `a` JOIN {$join_tables} ON {$join_on} WHERE `a`.`product_id` = '$product_id' AND {$join_conditions} ORDER BY {$join_order}", ARRAY_A);
      if(count($associated_variation_values) < 1) {
        $price = $wpdb->get_var("SELECT `price` FROM `{$wpdb->prefix}product_list` WHERE `id` ='{$product_id}' LIMIT 1");
        return $this->variations_add_grid_view((array)$selected_variations, $variation_values, $price);
      }
      foreach((array)$associated_variation_values as $key => $associated_variation_row) {
        // generate the variation name and ID arrays
        $associated_variation_names = array();
        $associated_variation_ids = array();
        foreach((array)$table_columns as $table_column) {
          $associated_variation_ids[] =  $associated_variation_row[$table_column];
          $associated_variation_names[] =  $variation_names[$associated_variation_row[$table_column]];
        }
        $group_defining_class = '';
        
        if($associated_variation_ids[0] != $associated_variation_values[$key+1]["value_id{$selected_variations[0]}"]) {
          $group_defining_class = "group_boundary";
        }
        $previous_row_id = $associated_variation_ids[0];
        
        // Implode them into a comma seperated string
        $associated_variation_names =  stripslashes(implode(", ",(array)$associated_variation_names));
        $associated_variation_ids = implode(",",(array)$associated_variation_ids);
        
        $variation_settings_uniqueid = $product_id."_".str_replace(",","_",$associated_variation_ids);
      
        // Format the price nicely
        if(is_numeric($associated_variation_row['price'])) {
          $product_price = number_format($associated_variation_row['price'],2,'.', '');
        }
        $file_checked = '';
        if((int)$associated_variation_row['file'] == 1) {
          $file_checked = "checked='true'";
        }
        
        $output .= "  <tr class='variation_row'>\n\r";
        $output .= "    <td class='variations'>{$associated_variation_names}</td>\n\r";
        $output .= "    <td class='stock' $stock_column_state ><input type='text' name='variation_priceandstock[{$associated_variation_ids}][stock]' value='".$associated_variation_row['stock']."' size='3' /></td>\n\r";
        $output .= "    <td class='price'><input type='text' name='variation_priceandstock[{$associated_variation_ids}][price]' value='{$product_price}' size='6' /></td>\n\r";
        //$output .= "    <td class='weight'><input type='text' name='variation_priceandstock[{$associated_variation_ids}][weight]' value='{$associated_variation_row['weight']}' size='3' /></td>\n\r";
        
        $output .= "    <td>\n\r";
        $output .= "      <a href='#' class='variation_edit_button' onclick='return open_variation_settings(\"variation_settings_$variation_settings_uniqueid\")' ><img src='".WPSC_URL."/images/gear__plus.png' alt='".TXT_WPSC_EDIT."' title='".TXT_WPSC_EDIT."'></a>\n\r";
        $output .= "    </td>\n\r";
        $output .= "  </tr>\n\r";
        
        $output .= "  <tr class='settings_row {$group_defining_class}' id='variation_settings_$variation_settings_uniqueid'>\n\r";
        $output .= "    <td colspan='5'>\n\r";
        $output .= "      <div class='variation_settings'>\n\r";
				$output .= "        <div class='variation_weight'>\n\r";
        $output .= "          <strong>".TXT_WPSC_WEIGHT_SETTINGS."</strong><br />";
        $output .= "          <input type='text' name='variation_priceandstock[{$associated_variation_ids}][weight]' value='{$associated_variation_row['weight']}' size='3' />";

        $output .= "          <select name='variation_priceandstock[{$associated_variation_ids}][weight_unit]'>\n\r";
				$output .= "            <option value='pound' ". (($associated_variation_row['weight_unit'] == 'pound') ? 'selected' : '') .">Pounds</option>\n\r";
				$output .= "            <option value='once' ". (($associated_variation_row['weight_unit'] == 'once') ? 'selected' : '') .">Ounces</option>\n\r";
				$output .= "            <option value='gram' ". (($associated_variation_row['weight_unit'] == 'gram') ? 'selected' : '') .">Grams</option>\n\r";
				$output .= "            <option value='kilogram' ". (($associated_variation_row['weight_unit'] == 'kilogram') ? 'selected' : '') .">Kilograms</option>\n\r";
				$output .= "          </select>\n\r";

        $output .= "        </div>\n\r";
        $output .= wpsc_select_variation_file($associated_variation_ids, $associated_variation_row['id']);
        $output .= "      </div>\n\r";
        
        
        $output .= "    </td>\n\r";
        $output .= "  </tr>\n\r";
      }
      $output .= "</table>\n\r";    
    }
    return $output;
	}
  
  function display_attached_variations($product_id) {
    global $wpdb;
      // no longer used, calls to this need to be removed before this can. Look at function 'variations_grid_view' instead.
    return $output;
  }
    
  function edit_product_values($product_id,$variation_value_list, $price = 0) {
		global $wpdb;
		$variation_id_list = array();
		$modified_values = array();
		$modified_value_variations = array();
		// Edit or update the variation values association table
    foreach($variation_value_list as $variation_value_id => $variation_visibility) {
			$variation_value_id = (int)$variation_value_id; 
			$visible_state = (int)(bool)$variation_visibility;
			// this is inefficient, it would probably be better to get the list of values associated with a variation into an array, and do an array search in PHP. Probably need some SQLfu to do this nicely.
			$variation_id = $wpdb->get_var("SELECT `variation_id` FROM `{$wpdb->prefix}variation_values` WHERE `id` IN('{$variation_value_id}') LIMIT 1");
			if($wpdb->get_var("SELECT * FROM `{$wpdb->prefix}variation_associations` WHERE `type` IN ('product') AND `associated_id` IN ('$product_id') AND `variation_id` IN ('$variation_id')") > 0) {
				$variation_id_list[] = $variation_id;
				if($wpdb->get_var("SELECT * FROM `{$wpdb->prefix}variation_values_associations` WHERE `product_id` = '$product_id' AND `value_id` = '".(int)$variation_value_id."'") > 0) {
					// if is present, update it
					$rows_changed = $wpdb->query("UPDATE `".$wpdb->prefix."variation_values_associations` SET `visible` = '".$visible_state."' WHERE `product_id` = '$product_id' AND `value_id` = '".(int)$variation_value_id."' LIMIT 1 ;");
					if($rows_changed == 1 ) {
						$modified_values[] = $variation_value_id;
						$modified_value_variations[$variation_value_id] = $variation_id;
					}
				} else {
			    // otherwise, add it
					$wpdb->query("INSERT INTO `{$wpdb->prefix}variation_values_associations` ( `product_id` , `value_id` , `quantity` , `price` , `visible` , `variation_id` ) VALUES ( '$product_id', '$variation_value_id', 0, 0, '$visible_state', '$variation_id')");
					$modified_values[] = $variation_value_id;
					$modified_value_variations[$variation_value_id] = $variation_id;
				}
			}
		}
		
		
		
		
		$variation_id_list = array_unique($variation_id_list);
		asort($variation_id_list);
		$all_variation_ids = implode(",", $variation_id_list);
		//get the count of variation combinations, if not zero, then go to the next step
		if($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `product_id` IN ('$product_id') AND `all_variation_ids` IN ('{$all_variation_ids}')") > 0 ) {
		  
		  // get the variation values with no combination data for them.
			$imploded_modified_values = implode("','", (array)$modified_values);
			$combination_data = $wpdb->get_col("SELECT DISTINCT `value_id` FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `product_id` IN ('$product_id') AND `all_variation_ids` IN ('{$all_variation_ids}') AND `value_id` IN('{$imploded_modified_values}')");
			$new_values = array_diff($modified_values, $combination_data);
			
			if(count($new_values) > 0) {
				echo "/*\n";				
			  foreach($new_values as $new_value) {
					// Need to join the wp_variation_values variation_values`table to itself multiple times with no condition for joining, resulting in every combination of values being extracted
					foreach((array)$variation_id_list as $variation) {
						$variation = (int)$variation;
						
						$excluded_value_sql = '';
						if($modified_value_variations[$new_value] == $variation) {
							$excluded_value_sql = "AND `a{$variation}`.`id` IN('{$new_value}')";
						}
						
						// generate all the various bits of SQL to bind the tables together
						$join_selected_cols[] = "`a{$variation}`.`id` AS `id_{$variation}`";
						$join_tables[] = "`{$wpdb->prefix}variation_values` AS `a{$variation}`";
						$join_conditions[] = "`a{$variation}`.`variation_id` = '{$variation}' $excluded_value_sql";
					}
					
					// implode the SQL statment segments into bigger segments
					$join_selected_cols = implode(", ", $join_selected_cols);
					$join_tables = implode(" JOIN ", $join_tables);
					$join_conditions = implode(" AND ", $join_conditions);
					$new_variation_combinations = $wpdb->get_results("SELECT {$join_selected_cols} FROM {$join_tables} WHERE {$join_conditions}", ARRAY_A);
					
					foreach($new_variation_combinations as $new_variation_combination) {
						echo print_r($new_variation_combination,true)."\n";
						
						$wpdb->query("INSERT INTO `{$wpdb->prefix}variation_priceandstock` ( `product_id` , `stock`, `price`, `weight`, `file` ) VALUES ('{$product_id}', '0', '{$price}', '0', '');");
						$variation_priceandstock_id = $wpdb->get_var("SELECT LAST_INSERT_ID() FROM `{$wpdb->prefix}variation_priceandstock` LIMIT 1");
						foreach($new_variation_combination as $new_variation_value) {
							if($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `priceandstock_id` = '{$variation_priceandstock_id}' AND `value_id` = '$new_variation_value'") < 1) {
								$variation_id = $wpdb->get_var("SELECT `{$wpdb->prefix}variation_values`.`variation_id` FROM `{$wpdb->prefix}variation_values` WHERE `id` = '{$new_variation_value}'");
								$wpdb->query("INSERT INTO `{$wpdb->prefix}wpsc_variation_combinations` ( `product_id` , `priceandstock_id` , `value_id`, `variation_id`, `all_variation_ids` ) VALUES ( '$product_id', '{$variation_priceandstock_id}', '$new_variation_value', '$variation_id', '$all_variation_ids' );");
							}
						}
					//$selected_price
					
					
					}
				}
				// Assemble and execute the SQL query
				//$associated_variation_values = $wpdb->get_results("SELECT {$join_selected_cols} FROM {$join_tables} WHERE {$join_conditions}", ARRAY_A);
				
				echo "*/\n";
				
				
				
				
			}
			
			
		}
    return $output;
	}
    
   function edit_add_product_values($product_id,$variation_value_list) {
    global $wpdb;
    // this does nothing any more, but is left to avoid fatal errors, for now...
    return $output;
	}
  
  function display_product_variations($product_id,$no_label = false, $no_div = false, $update_price = false ) {
    global $wpdb;
    $product_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='".$product_id."' LIMIT 1",ARRAY_A);
    
    $variation_assoc_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_associations` WHERE `type` IN ('product') AND `associated_id` IN ('$product_id')",ARRAY_A);
    $saved_variation_price = 0;
    
    if($variation_assoc_data != null) {
      $first_entries = Array();
			foreach($variation_assoc_data as $variation_association) {
				$variation_ids[] = $variation_association['variation_id'];
			}
			$j=0;
      foreach((array)$variation_assoc_data as $variation_association) {
        $i = 0;
        if($no_div !== true) {			
				}
				if($j==0) {
					$default_topping='checked="checked"';
				} else {
					$default_topping='';
				}
				$j++;
        $variation_id = $variation_association['variation_id'];
        $value_assoc_data = $wpdb->get_results("SELECT `a`.*, `v`.`name` FROM `{$wpdb->prefix}variation_values_associations` AS `a` JOIN `{$wpdb->prefix}variation_values` AS `v` ON `a`.`value_id` = `v`.`id` WHERE `a`.`product_id` IN ('$product_id') AND `a`.`variation_id` IN ('$variation_id') AND `a`.`visible` IN ('1')",ARRAY_A);
        
        
        $variation_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."product_variations`  WHERE `id` IN ('$variation_id') LIMIT 1",ARRAY_A);
        
        
        if($no_label !== true) {
          $output .= "<label for='variation_select_".$product_id."_".$variation_data['id']."'>". $variation_data['name'] . ":</label> ";
				}
        
        
        if(($update_price === true) && (count($variation_ids) >= 1)) {
          $special = 'false';
          if($no_label == true) {
            $special = 'true';
					}
          $on_change = "onchange='change_variation($product_id, Array(\"".implode("\",\"", $variation_ids)."\"), $special)'";
	      } else { $on_change = ''; }
				$special_prefix = '';
				if($no_label == true) {
					$special_prefix = 'special_';
				}
				
				
				if (get_option("checkbox_variations")=='1') {
					$output .= "<br>";
				} else {
					$output .= "<select id='".$special_prefix."variation_select_".$product_id."_".$variation_data['id']."' name='variation[".$variation_data['id']."]' $on_change >";
				}
				
				
				foreach((array)$value_assoc_data as $value_association) {
          if($i == 0) { 
						$first_entries[] = $value_association['value_id']; 
					}
          $check_stock = false;
          
          if(($product_data['quantity_limited'] == 1) && (count($variation_assoc_data) == 1)) {
            $priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `product_id` = '$product_id' AND `value_id` IN ( '{$value_association['id']}' ) AND `all_variation_ids` IN('{$variation_data['id']}') GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '1' LIMIT 1");
            
            $variation_stock_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."variation_priceandstock` WHERE `id` = '{$priceandstock_id}' LIMIT 1",ARRAY_A);
            $check_stock = true;
            $stock = $variation_stock_data['stock'];
            $variation_price = $variation_stock_data['price'];
          }
		
					if (get_option('checkbox_variations')==1) {
						$output .= "<input type='checkbox' id='variation[".$value_association['id']."]' name='variation[".$variation_data['name']."][]'".$default_topping." value='".$value_association['id']."' onclick='manage_topping(".$product_id.",".$value_association['id'].",".$special.")'>".stripslashes($value_association['name'])."<br>";
					} else {
						if(($check_stock == true) && ($stock < 1)) {
							//$output .= "<option value='".$value_association['id']."' disabled='true'>".stripslashes($value_association['name'])." - ".TXT_WPSC_NO_STOCK."</option>";
						} else {
							$output .= "<option value='".$value_association['value_id']."'>".stripslashes($value_association['name'])."</option>";
							if($saved_variation_price == 0) {
							  $saved_variation_price = $variation_price;
							}
						}
					}
					$i++;
				}
				if (get_option("checkbox_variations")=='1') {
					$output .= "";
				} else {
					$output .= "</select><br>";
				}
				if($no_div !== true) {
		
				}
			}
		}
    
		if($update_price === true) {
			$first_entry_count = count($first_entries);
			if(($first_entry_count >= 1)) {
				if($saved_variation_price > 0) {
					$price = $saved_variation_price;
				
				} else {
					$price = calculate_product_price($product_id, $first_entries);
				}
			} else { $price = null; }
			return Array($output, $price);
		} else {
			return $output;
		}
	}
    
	function variations_add_grid_view($variations, $variation_values = null, $default_price = null, $limited_stock = true) {
		global $wpdb;
		$variation_count = count($variations);
		if($variation_count < 1) {
			return "";
			exit();
		}
			$stock_column_state = '';
    if($limited_stock == false) {
      $stock_column_state = " style='display: none;'";
    }
		if((float)$default_price == 0) {
		  $default_price = 0;
		}
    $default_price = number_format($default_price,2,'.', '');

    $output .= "<table class='product_variation_grid'>\n\r";   
    $output .= "  <tr>\n\r";
    $output .= "    <th class='variations titles'>".TXT_WPSC_VARIATION."</th>\n\r";
    $output .= "    <th class='titles stock' $stock_column_state>".TXT_WPSC_STOCK."</th>\n\r";
    $output .= "    <th class='titles price'>".TXT_WPSC_PRICE."</th>\n\r";
    $output .= "    <th class='titles'>".TXT_WPSC_MORE."&nbsp;</th>\n\r";
		$output .= "  </tr>\n\r";
		
		
		
		//echo "/* ".print_r($edit_variation_values,true)." */\n\r";
		$excluded_values = array_keys((array)$variation_values, 0);
    
    
    // Need to join the wp_variation_values variation_values`table to itself multiple times with no condition for joining, resulting in every combination of values being extracted
		foreach((array)$variations as $variation) {
      $variation = (int)$variation;
      
			$excluded_value_sql = '';
			if(count($excluded_values) > 0 ) {
				$excluded_value_sql = "AND `a{$variation}`.`id` NOT IN('".implode("','", $excluded_values)."')";
			}
      
      // generate all the various bits of SQL to bind the tables together
      $join_selected_cols[] = "`a{$variation}`.`id` AS `id_{$variation}`, `a{$variation}`.`name` AS `name_{$variation}`";
      $join_tables[] = "`{$wpdb->prefix}variation_values` AS `a{$variation}`";
      $join_conditions[] = "`a{$variation}`.`variation_id` = '{$variation}' $excluded_value_sql";
    }
    
    // implode the SQL statment segments into bigger segments
    $join_selected_cols = implode(", ", $join_selected_cols);
    $join_tables = implode(" JOIN ", $join_tables);
    $join_conditions = implode(" AND ", $join_conditions);
    
    // Assemble and execute the SQL query
    $associated_variation_values = $wpdb->get_results("SELECT {$join_selected_cols} FROM {$join_tables} WHERE {$join_conditions}", ARRAY_A);
		
		$variation_sets = array();
		$i = 0;
		foreach($associated_variation_values as $associated_variation_value_set) {
		  foreach($variations as $variation) {
		    $value_id = $associated_variation_value_set["id_$variation"];
		    $name_id = $associated_variation_value_set["name_$variation"];
		    $variation_sets[$i][$value_id] = $name_id;
		  }
      $i++;
		}
		
    foreach((array)$variation_sets as $key => $variation_set) {
      //echo "<pre>".print_r($asssociated_variation_set,true)."</pre>";
      $variation_names = implode(", ", $variation_set);
      $variation_id_array = array_keys((array)$variation_set);
      $variation_ids = implode(",", $variation_id_array);
      $variation_settings_uniqueid = "0_".str_replace(",","_",$variation_ids);
      
      $group_defining_class = '';
      
      $next_id_set = array_keys((array)$variation_sets[$key+1]);
      //echo "<pre>".print_r($variation_set,true)."</pre>";
      if($variation_id_array[0] != $next_id_set[0]) {
        $group_defining_class = "group_boundary";
      }
      
      $output .= "  <tr class='variation_row'>\n\r";
      $output .= "    <td class='variations'>".str_replace(" ", "&nbsp;", (stripslashes( $variation_names )))."</td>\n\r";
      $output .= "    <td class='stock' $stock_column_state><input type='text' name='variation_priceandstock[{$variation_ids}][stock]' value='' size='3' /></td>\n\r";
      $output .= "    <td class='price'><input type='text' name='variation_priceandstock[{$variation_ids}][price]' value='$default_price' size='6' /></td>\n\r";
//       $output .= "    <td class='weight'><input type='text' name='variation_priceandstock[{$variation_ids}][weight]' value='' size='3' /></td>\n\r";
      
			$output .= "    <td>\n\r";
			$output .= "      <a href='#' class='variation_edit_button' onclick='return open_variation_settings(\"variation_settings_$variation_settings_uniqueid\")' ><img src='".WPSC_URL."/images/gear__plus.png' alt='".TXT_WPSC_EDIT."' title='".TXT_WPSC_EDIT."'></a>\n\r";
			$output .= "    </td>\n\r";
      
      $output .= "  </tr>\n\r";
      $output .= "  <tr class='settings_row {$group_defining_class}' id='variation_settings_$variation_settings_uniqueid'>\n\r";
      $output .= "    <td colspan='5'>\n\r";
      $output .= "      <div class='variation_settings'>\n\r";
      
			$output .= "        <div class='variation_weight'>\n\r";
			$output .= "          <strong>".TXT_WPSC_WEIGHT_SETTINGS."</strong><br />";
			$output .= "          <input type='text' name='variation_priceandstock[{$associated_variation_ids}][weight]' value='{$associated_variation_row['weight']}' size='3' />";

			$output .= "          <select name='variation_priceandstock[{$associated_variation_ids}][weight_unit]'>\n\r";
			$output .= "            <option value='pound' ". (($associated_variation_row['weight_unit'] == 'pound') ? 'selected' : '') .">Pounds</option>\n\r";
			$output .= "            <option value='once' ". (($associated_variation_row['weight_unit'] == 'once') ? 'selected' : '') .">Ounces</option>\n\r";
			$output .= "            <option value='gram' ". (($associated_variation_row['weight_unit'] == 'gram') ? 'selected' : '') .">Grams</option>\n\r";
			$output .= "            <option value='kilogram' ". (($associated_variation_row['weight_unit'] == 'kilogram') ? 'selected' : '') .">Kilograms</option>\n\r";
			$output .= "          </select>\n\r";

			$output .= "        </div>\n\r";
      $output .= wpsc_select_variation_file($variation_ids);
      $output .= "      </div>\n\r";
      $output .= "    </td>\n\r";
      $output .= "  </tr>\n\r";
    }
  
    
    
		$output .= "</table>\n\r";    
		return $output;
	}
	
  function update_variation_values($product_id, $variation_values) {
    global $wpdb;
    $product_id = (int)$product_id;
    // declare function for sanitising the keys
    
    //echo("<pre>".print_r($variation_values,true)."</pre>");
    //exit("<pre>".print_r($_POST,true)."</pre>");
		foreach($variation_values as $form_key => $variation_data) {
		
			// split input
			$keys = explode(",",$form_key);
			// sanitise input
			array_walk($keys, 'wpsc_sanitise_keys');
			
			
      $variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `{$wpdb->prefix}variation_values` WHERE `id` IN ('".implode("','",$keys)."')");
      asort($variation_ids);
      $all_variation_ids = implode(",", $variation_ids);
			
			
      $variation_price = (float)str_replace(",","",$variation_data['price']);
      $variation_stock =(int)$variation_data['stock']; // having 1.2 stock makes no sense unless dealing with kilograms or some other such measurement
      $variation_weight = (float)$variation_data['weight'];		
      $variation_weight_unit = (string)$variation_data['weight_unit'];		
      $variation_file =(int)$variation_data['file'];		
      $variation_visibility =(int)(bool)$variation_data['visibility'];
      
      
      if((is_numeric($variation_data['stock']) || is_numeric($variation_price))) {
        $priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `product_id` = '$product_id' AND `value_id` IN ( '".implode("', '",$keys )."' ) AND `all_variation_ids` IN('$all_variation_ids') GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($keys)."' LIMIT 1");
        
        $variation_stock_data = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}variation_priceandstock` WHERE `id` = '{$priceandstock_id}' LIMIT 1", ARRAY_A);
        //echo("<pre>".print_r($all_variation_ids,true)."</pre>");
        //echo("<pre>".print_r($variation_stock_data,true)."</pre>");
        
        if(is_numeric($variation_stock_data['id'])) {
          foreach($keys as $key) {
            if($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `priceandstock_id` = '{$variation_stock_data['id']}' AND `value_id` = '$key'") < 1) {
              $variation_id = $wpdb->get_var("SELECT `{$wpdb->prefix}variation_values`.`variation_id` FROM `{$wpdb->prefix}variation_values` WHERE `id` = '{$key}'");
              $wpdb->query("INSERT INTO `{$wpdb->prefix}wpsc_variation_combinations` ( `product_id` , `priceandstock_id` , `value_id`, `variation_id`, `all_variation_ids` ) VALUES ( '$product_id', '{$variation_stock_data['id']}', '$key', '$variation_id', '$all_variation_ids' );");
            }
          }
          $variation_sql = null; // return the sql array to null for each trip round the loop
          if(($variation_stock_data['stock'] != $variation_stock)) {
            $variation_sql[] = "`stock` = '{$variation_stock}'";
          }
          
          if(($variation_stock_data['price'] != $variation_price)) {
            $variation_sql[] = "`price` = '{$variation_price}'";
          }
          
          if(($variation_stock_data['weight'] != $variation_weight)) {
            $variation_sql[] = "`weight` = '{$variation_weight}'";
          }
          
          
          if(($variation_stock_data['weight_unit'] != $variation_weight_unit)) {
            $variation_sql[] = "weight_unit = '{$variation_weight_unit}'";
          }
          
          if(($variation_stock_data['file'] != $variation_file)) {
            $variation_sql[] = "`file` = '{$variation_file}'";
          }
          
          //if(($variation_stock_data['visibility'] != $variation_visibility)) {
          //  $variation_sql[] = "`visibility` = '{$variation_visibility}'";
          //}
          if($variation_sql != null) {
            $wpdb->query("UPDATE `{$wpdb->prefix}variation_priceandstock` SET ".implode(",",$variation_sql)."WHERE `id` = '{$variation_stock_data['id']}' LIMIT 1 ;");
          }
        }	else {
          $wpdb->query("INSERT INTO `{$wpdb->prefix}variation_priceandstock` ( `product_id` , `stock`, `price`, `weight`, `file` ) VALUES ('{$product_id}', '{$variation_stock}', '{$variation_price}', '{$variation_weight}', '{$variation_file}');");
          $variation_priceandstock_id = $wpdb->get_var("SELECT LAST_INSERT_ID() FROM `{$wpdb->prefix}variation_priceandstock` LIMIT 1");
          foreach($keys as $key) {
            if($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `priceandstock_id` = '{$variation_priceandstock_id}' AND `value_id` = '$key'") < 1) {
              $variation_id = $wpdb->get_var("SELECT `{$wpdb->prefix}variation_values`.`variation_id` FROM `{$wpdb->prefix}variation_values` WHERE `id` = '{$key}'");
              $wpdb->query("INSERT INTO `{$wpdb->prefix}wpsc_variation_combinations` ( `product_id` , `priceandstock_id` , `value_id`, `variation_id`, `all_variation_ids` ) VALUES ( '$product_id', '{$variation_priceandstock_id}', '$key', '$variation_id', '$all_variation_ids' );");
            }
          }
        }
      }
    
		} //foreach ends here
		//exit();
  } //function ends here   
  
  
}

?>