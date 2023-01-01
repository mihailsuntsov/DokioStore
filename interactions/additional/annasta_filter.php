<?php
    add_action('init', 'myInit');
	function myInit() {
		global $style;
	  $annasta_filter_value=trim(preg_replace( "/\r|\n/", "",str_replace(PHP_EOL, '', get_option( 'annasta_filter_value' ))));
		//$annasta_filter_value=trim(preg_replace( "/\r|\n/", "",get_option( 'annasta_filter_value' )));
		if(get_option( 'use_annasta_filter' )=='on' && $annasta_filter_value!=''){
			// array of string: '[attribute's slug name for which this condition made]:[conditions by categories separated by commas]|[parent attribute's slug]=[conditions by slugs of parent attribute, separated by commas]',
			$filters=explode( ';', $annasta_filter_value);
			//echo '1--'.get_option( 'annasta_filter_value' ).'--1';
			// print_r($filters);
			$new_url='';
			foreach ($filters as $filter)
			{
				//the slug of current attribute
				$current_attribute =  (explode( ':', $filter ))[0];
				//the conditions of current attribute. 
				$current_conditions = (explode( ':', $filter ))[1];
        //echo 'current_attribute - '.$current_attribute.'<br>';
        //echo 'current_conditions - '.$current_conditions.'<br>';
				$current_categories_conditions=      strlen((explode( '|', $current_conditions ))[0])>0?explode(',',((explode( '|', $current_conditions ))[0])):[];
				$current_parent_attribute_name=      strlen((explode( '|', $current_conditions ))[1])>0?explode('=',((explode( '|', $current_conditions ))[1]))[0]:'';
				$current_parent_attribute_conditions=strlen((explode( '|', $current_conditions ))[1])>0?explode(',',explode('=',((explode( '|', $current_conditions ))[1]))[1]):[];
				
         //print_r($current_categories_conditions);
         
         //echo "current_parent_attribute_name ->".$current_parent_attribute_name."<-<br>->";
         //echo ($current_parent_attribute_name!=''&&(!isset($_GET['pa_'.$current_parent_attribute_name.'-filter']) || (isset($_GET['pa_'.$current_parent_attribute_name.'-filter']) && !in_array($_GET['pa_'.$current_parent_attribute_name.'-filter'], $current_parent_attribute_conditions))));
         //echo "<-<br>";
        
				//  if there is a condition of parent attribute and (in the GET query there is not this parent attribute   or (in the GET query there is this parent attribute             and                    its value is in a list           of                  allowed values))
					if (($current_parent_attribute_name!=''&&(!isset($_GET['pa_'.$current_parent_attribute_name.'-filter']) || (isset($_GET['pa_'.$current_parent_attribute_name.'-filter']) && !in_array($_GET['pa_'.$current_parent_attribute_name.'-filter'], $current_parent_attribute_conditions)))) 
				//  or 
					||
				//	if there are the conditions of categories and (categories are not selected     or (categories are selected           but there is no overlaps of              the selected categories    and    current attribute categories))
					(count($current_categories_conditions)>0&&(!isset($_GET['product-categories']) || (isset($_GET['product-categories']) && count(array_intersect(explode( ',', $_GET['product-categories'] ), $current_categories_conditions))==0)))
					)
					{				
						$style=$style.' div[data-taxonomy=pa_'.$current_attribute.'-filter] {display:none;}';
						if (isset($_GET['pa_'.$current_attribute.'-filter'])){
							$new_url = remove_query_arg('pa_'.$current_attribute.'-filter');
						}
					}
			}
			if($new_url != ''){
				header("Location: ".$new_url);
				die();
			}
		}
	}

	add_action('wp_head','setFilterStyles');
	function setFilterStyles(){
		global $style;
		if(is_shop() && $style!=''){
			echo '<style>'.$style.'</style>';
		}
	}