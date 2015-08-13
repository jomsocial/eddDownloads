<?php
/**
 * Plugin Name: EDD Downloads Page
 * Plugin URI: http://peepso.com
 * Description: This plugin overrides the default layout of the EDD (Easy Digital Downloads) downloads page. 
 				The new layout allows users to enter their support domain, see when their product expires, and choose to renew their license or upgrade to other products. 
				(Requires EDD Upgrades plugin).
 * Version: 1.0
 * Author: peepso.com
 * Author URI: peepso.com
 * Text Domain: edd_downloads 
 * License: 
 */

defined('ABSPATH') or die("No script kiddies please!");

/* Helpers functions */
function edd_downloads_css(){
	wp_register_style( 'edd_downloads', plugin_dir_url(__FILE__)."css/edd_downloads.css" );
	wp_enqueue_style( 'edd_downloads' );	
}
add_action('wp_enqueue_scripts', 'edd_downloads_css');

function edd_downloads_get_product_duration($item){

	$edd_plans_multiple_license   	= get_post_meta( $item['id'], 'edd_plans_multiple_license', true );
	$unlimited_license   			= get_post_meta( $item['id'], '_unlimited_license', true );	
	$price_id						= isset($item['item_number']['options']['price_id']) ? $item['item_number']['options']['price_id'] : NULL;
	if($unlimited_license == 1){//empty($edd_plans_multiple_license[$price_id]['length']) || 
		//multiple license prices
		echo " - ";
	}elseif( $price_id >= 0 && !empty($edd_plans_multiple_license[$price_id]['length']) && !empty($edd_plans_multiple_license[$price_id]['unit'])){ 
		echo $edd_plans_multiple_license[$price_id]['length'] . ' ' . $edd_multiple_license[$price_id]['unit'];
	}else{
		//standard edd prices
		$edd_sl_exp_length  = get_post_meta( $item['id'], '_edd_sl_exp_length', true );
		$edd_sl_exp_unit   	= get_post_meta( $item['id'], '_edd_sl_exp_unit', true );

		switch ($edd_sl_exp_unit) {
			case "year" :			
			case "years" :
				$edd_sl_exp_unit 	= 'year';
				$edd_sl_exp_units	= 'years';
			break;
			case "month" :			
			case "months" :
				$edd_sl_exp_unit 	= 'month';
				$edd_sl_exp_units	= 'months';
			break;
			case "week" :			
			case "weeks" :
				$edd_sl_exp_unit 	= 'week';
				$edd_sl_exp_units	= 'weeks';
			break;			
			case "day" :			
			case "days" :
				$edd_sl_exp_unit 	= 'day';
				$edd_sl_exp_units	= 'days';
			break;			
		}

		if(empty($edd_sl_exp_length) || empty($edd_sl_exp_unit)){
			echo " - ";
		}else{
			echo sprintf( _n( '1 '.$edd_sl_exp_unit, '%s '.$edd_sl_exp_units, $edd_sl_exp_length, 'edd_downloads' ), $edd_sl_exp_length );
		}

	}

}

function edd_downloads_get_time_difference($start, $end){
	$uts['start'] = $start;
	$uts['end'] = $end;
	if( $uts['start'] !== -1 && $uts['end'] !== -1){
		if($uts['end'] >= $uts['start']){
			$diff = $uts['end'] - $uts['start'];
			if($days=intval((floor($diff/86400)))){
				$diff = $diff % 86400;
			}
				
			if($hours=intval((floor($diff/3600)))){
				$diff = $diff % 3600;
			}	
			
			if($minutes=intval((floor($diff/60)))){
				$diff = $diff % 60;
			}	
			$diff = intval($diff);
			return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff));
		}
		else{
			return false;
		}
	}
	return false;
}

function edd_downloads_get_license_expiration($license_id){

	$license_date 		= get_post_meta( $license_id, '_edd_sl_expiration', true );
	$payment_id			= get_post_meta( $license_id, '_edd_sl_payment_id', true );
	$payment_total  	= get_post_meta( $payment_id, '_edd_payment_total', true );
	//$license_date 		= get_post_meta( $license_id, '_edd_sl_expiration', true );
	$product_id   		= get_post_meta( $license_id, '_edd_sl_download_id', true );		
	$price   			= get_post_meta( $product_id, 'edd_price', true );		
	$unlimited_license  = get_post_meta( $product_id, '_unlimited_license', true );	
	//$edd_sl_exp_length  = get_post_meta( $payment_id, '_edd_sl_exp_length', true );
    //$edd_sl_exp_unit   	= get_post_meta( $payment_id, '_edd_sl_exp_unit', true );	
	$now	 		= strtotime(date('Y-m-d H:i:s'));
	$date_int 		= $license_date;

	if($unlimited_license == 1){
		$expire = __( 'Expires in','edd_downloads' );
		$date = '<b>'.$expire.':</b> <span class="edd_downloads_active">'.__( 'Never','edd_downloads' ).'</span>';		
		echo $date;
		return '';
	}

	if($price > 0){
		if($now > $license_date){
			$expire = __( 'Expired','edd_downloads' );
			$difference_int = edd_downloads_get_time_difference($license_date, $now);
			$bool_expired = false;
			$expire = __( 'Expires in','edd_downloads' );
			//---------------------------
			if($difference_int["days"] == 0){
				$difference = $difference_int["hours"] > 0 ? (  $difference_int["hours"] == 1 ? $difference_int["hours"]." ".__( 'hour','edd_downloads' ) : $difference_int["hours"]." ".__( 'hours','edd_downloads' ) ) : $difference_int["minutes"]." ".__( 'minutes','edd_downloads' );
			} else{ 	
				$difference = $difference_int["days"] == 1 ? $difference_int["days"]." ". __( 'day','edd_downloads' ) : $difference_int["days"]." ". __( 'days','edd_downloads' );
			}
			$date = '<b>'.$expire.':</b> <span class="edd_downloads_expired">'.$difference.' ' .__( 'ago','edd_downloads' ) .'</span>';//('.date("m-d-Y", $date_int). ') 		
		}else{
			$difference_int = edd_downloads_get_time_difference($now, $license_date);	
			$bool_expired = false;
			$expire = __( 'Expires in','edd_downloads' );
			//---------------------------
			if($difference_int["days"] == 0){
				$difference = $difference_int["hours"] > 0 ? (  $difference_int["hours"] == 1 ? $difference_int["hours"]." ".__( 'hour','edd_downloads' ) : $difference_int["hours"]." ".__( 'hours','edd_downloads' ) ) : $difference_int["minutes"]." ".__( 'minutes','edd_downloads' );
			} else{ 
				$difference = $difference_int["days"] == 1 ? $difference_int["days"]." ".__( 'day','edd_downloads' ) : $difference_int["days"]." ".__( 'days','edd_downloads' );
			}
			$date = '<b>'.$expire.':</b> <span class="edd_downloads_active">'.$difference.'</span>';			
		}
	}else{
		$expire = __( 'Expires in','edd_downloads' );
		$date = '<b>'.$expire.':</b> <span class="edd_downloads_active">'.__( 'Never','edd_downloads' ).'</span>';		
	}
	echo $date;
}

/* Override layout (shortcode) */
function edd_downloads_receipt_shortcode($atts, $content = null){
	global $edd_receipt_args;

	$edd_receipt_args = shortcode_atts( array(
		'error'           => __( 'Sorry, trouble retrieving payment receipt.', 'edd_downloads' ),
		'price'           => true,
		'discount'        => true,
		'products'        => true,
		'date'            => true,
		'notes'           => true,
		'payment_key'     => false,
		'payment_method'  => true,
		'payment_id'      => true
	), $atts, 'edd_receipt' );

	$session = edd_get_purchase_session();
	if(isset($session['downloads']) && is_array($session['downloads'])){
		foreach($session['downloads'] as $download){
			if(isset($download['upgrade']) && $download['upgrade']['upgrade_license'] > 0){
				$payment_id_lic							= get_post_meta( absint($download['upgrade']['upgrade_license']), '_edd_sl_payment_id', true );
				$payment_key 							= edd_get_payment_key( $payment_id_lic );
				break;
			}
		}		
	}
	if ( !isset( $payment_key ) ) {
		if ( isset( $_GET[ 'payment_key' ] ) ) {
			$payment_key = urldecode( $_GET[ 'payment_key' ] );
		} elseif ( $edd_receipt_args['payment_key'] ) {
			$payment_key = $edd_receipt_args['payment_key'];
		} else if ( $session ) {
			$payment_key = $session[ 'purchase_key' ];
		}
	}
	// No key found
	if ( ! isset( $payment_key ) )
		return $edd_receipt_args[ 'error' ];

	$edd_receipt_args[ 'id' ] = edd_get_purchase_id_by_key( $payment_key );
	$customer_id              = edd_get_payment_user_id( $edd_receipt_args[ 'id' ] );

	/*
	 * Check if the user has permission to view the receipt
	 *
	 * If user is logged in, user ID is compared to user ID of ID stored in payment meta
	 *
	 * Or if user is logged out and purchase was made as a guest, the purchase session is checked for
	 *
	 * Or if user is logged in and the user can view sensitive shop data
	 *
	 */

	$user_can_view = ( is_user_logged_in() && $customer_id == get_current_user_id() ) || ( ( $customer_id == 0 || $customer_id == '-1' ) && ! is_user_logged_in() && edd_get_purchase_session() ) || current_user_can( 'view_shop_sensitive_data' );

	if ( ! apply_filters( 'edd_user_can_view_receipt', $user_can_view, $edd_receipt_args ) ) {
		return $edd_receipt_args[ 'error' ];
	}
	require plugin_dir_path(__FILE__)."template/shortcode-receipt.php";
}
add_shortcode( 'edd_receipt', 'edd_downloads_receipt_shortcode' );

function edd_downloads_purchase_history_shortcode($atts, $content = null){
	if ( is_user_logged_in() ) {
		$action = isset($_REQUEST['action']) ? esc_html( $_REQUEST['action'] ) : '';
		if($action == ''){
			require plugin_dir_path(__FILE__)."template/history-purchase.php";
		}
	} else {
		global $edd_login_redirect;
		
		$edd_login_redirect = get_permalink();
		
		require edd_get_templates_dir()."/shortcode-login.php";		
		
	}
}
add_shortcode( 'purchase_history', 'edd_downloads_purchase_history_shortcode' );

/*function edd_downloads_override_history_content( $content ) {

    if( empty( $_GET['action'] ) || 'manage_licenses' != $_GET['action'] ) {
		return $content;
	}

	if( empty( $_GET['payment_id'] ) ) {
		return $content;
	}

	$post = get_post();
	if ( is_user_logged_in() && 'purchase-confirmation' != $post->post_name ) {
		require plugin_dir_path(__FILE__)."template/licenses-manage-overview.php";
	}

}
add_filter( 'the_content', 'edd_downloads_override_history_content', 10000 );
*/

/* Upgrade and renew functionality */
function edd_downloads_load_upgrade_button_js() {
	//wp_register_script( 'edd_downloads_product_upgrade', plugins_url('js/jomsocial_layout.js', __FILE__) );
	//wp_enqueue_script( 'edd_downloads_product_upgrade' );	

	wp_enqueue_script( 'edd_downloads_product_upgrade', plugins_url('js/edd_downloads.js', __FILE__), array( 'jquery' ) );
	wp_localize_script( 'edd_downloads_product_upgrade', 'edd_downloads_scripts', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	//wp_localize_script( 'edd_downloads_product_upgrade', 'jsLayout', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

}
add_action( 'wp_enqueue_scripts', 'edd_downloads_load_upgrade_button_js' );

function edd_downloads_show_upgrade_products($product,$license_id,$hide_id,$payment_id){
	global $wpdb, $edd_displayed_form_ids;
    if( is_user_logged_in() ) {
        $user_id = get_current_user_id();
    } else {
        return false;
    }
	if(!edd_downloads_has_user_purchased(get_current_user_id(), $product['id'])){
		return false;
	}
	$products  = $wpdb->get_results( $wpdb->prepare( "	SELECT 
															post_id 
														FROM 
															" . $wpdb->prefix . "postmeta as pm RIGHT JOIN " . $wpdb->prefix . "posts as p ON (pm.post_id = p.ID)
														WHERE 
															p.post_status = 'publish' AND
															pm.meta_key = 'edd_product_family' AND 
															pm.meta_value = (	SELECT 
																				meta_value 
																			FROM 
																				" . $wpdb->prefix . "postmeta 
																			WHERE 
																				meta_key = 'edd_product_family' AND 
																				post_id = %d AND 
																				meta_value > '0'
																		  ) AND 
															pm.post_id != %d AND
															pm.post_id IN ( SELECT 
																			post_id
																		FROM 
																			" . $wpdb->prefix . "postmeta 
																		WHERE 
																			meta_key = 'edd_price' AND 
																			meta_value > %f
																			
																		)", $product['id'], $product['id'], $product['item_price']
													) 
									);

	if(!empty($products)){
		global $edd_options;
		unset($edd_options['payment_id']);
		?>
		<div class="edd_purchase_submit_wrapper">
			<?php //echo _e( 'Upgrade ' . $product['name'] . ' to:','edd_downloads' );	?>
			<select name="edd_downloads_upgrade_product" id="edd_downloads_upgrade_product" onChange="javascript:edd_downloads_show_button(this.value,<?php echo $hide_id ?>,<?php echo $license_id; ?>,<?php echo $product['id']; ?>);">
	            <option value="" selected><?php echo _e( 'Upgrade to:','edd_downloads' );	?></option>
            	<?php
				$cart_link = array();
				//$currency_symbol = edd_currency_symbol(edd_get_currency());
            	foreach($products as $product_row){

					$product_details   	= get_post($product_row->post_id);
					$product_price		= get_post_meta( $product_row->post_id, 'edd_price', true );
					$upgrade_price		= $product_price - $product['item_price'];					
					echo '<option value="' . $product_details->ID . '">' . $product_details->post_title . ' ' . __( '- Add','edd_downloads' ) . ' ' . edd_currency_filter( edd_format_amount($upgrade_price) ) . '</option>';
					$edd_displayed_form_ids = array();

					$cart_link[$product_details->ID] = edd_get_purchase_link( array( 'download_id' => $product_details->ID, 'form_id' => 'edd_purchase_' . $product_details->ID . '_' . $hide_id, 'payment_id' => $payment_id, 'price' => false ) );
                }
				?>
			</select>
		</div>
        <div id='upgrade_button_<?php echo $hide_id ?>'>
        	<?php
        	foreach($cart_link as $cart_link){
				echo $cart_link;
            }
			?>
        </div>
		<?php
	}
}

/* automatically renew license on click Renew button - start*/
function edd_downloads_remove_renewal_form(){
	remove_action( 'edd_before_purchase_form', 'edd_sl_renewal_form', -1 );
}
add_action( 'init', 'edd_downloads_remove_renewal_form', 11 );

function edd_downloads_add_purchase_args($download_ID, $args = array()){
	global $edd_options;
	
	$arguments = isset($edd_options[$download_ID]) ? $edd_options[$download_ID] : array();
	
	if(isset($arguments['license_id']) && $arguments['license_id'] > 0){
		?>
        <input type="hidden" name="license_id" value="<?php echo $arguments['license_id']; ?>" />
        <?php
	}
	if(isset($arguments['expired']) && absint($arguments['expired']) > 0){
		?>
        <input type="hidden" name="expired" value="<?php echo $arguments['expired']; ?>" />
        <?php
	}
	if(isset($arguments['product_id']) && absint($arguments['product_id']) > 0){
		?>
        <input type="hidden" name="product_id" value="<?php echo $arguments['product_id']; ?>" />
        <?php
	}		
}
add_action( 'edd_purchase_link_end', "edd_downloads_add_purchase_args", 11, 2);

function edd_downloads_renew_to_cart($download_id, $options){
	if( ! edd_sl_renewals_allowed() )
		return;
	$post_data 			= urldecode($_REQUEST['post_data']);
	$post_data_formated = array();

	if( strstr($post_data,"expired") ){
		$post_data_split = explode("&",$post_data);
		foreach($post_data_split as $data_split){
			$data_array = explode("=",$data_split);
			$post_data_formated[$data_array[0]] = $data_array[1];
		}
	}
	if(isset($post_data_formated['expired']) && $post_data_formated['expired'] == 1 && isset($post_data_formated['license_id']) && isset($post_data_formated['product_id'])){
		global $edd_options;
		$license_key 	= edd_software_licensing()->get_license_key($post_data_formated['license_id']);
		$data 			= array('edd_license_key' => $license_key ,'edd_action' => 'apply_license_renewal');

		$license 		= ! empty( $data['edd_license_key'] ) ? sanitize_text_field( $data['edd_license_key'] ) : false;
		$valid   		= true;
		if( ! $license ) {
			$valid = false;
		}
		$license_id = edd_software_licensing()->get_license_by_key( $license );
		if( empty( $license_id ) )
			$valid = false;
		$download_id = get_post_meta( $license_id, '_edd_sl_download_id', true );
		if( empty( $download_id ) || ! edd_item_in_cart( $download_id ) ) {
			$valid = false;
		}

		$options = array();
		// if product has variable prices, find previous used price id and add it to cart
		if ( edd_has_variable_prices( $download_id ) ) {
			$price_id = get_post_meta( $license_id, '_edd_sl_download_price_id', true );
			if( '' === $price_id ) {
				// If no $price_id is available, try and find it from the payment ID. See https://github.com/pippinsplugins/EDD-Software-Licensing/issues/110
				$payment_id = get_post_meta( $license_id, '_edd_sl_payment_id', true );
				$payment_items = edd_get_payment_meta_downloads( $payment_id );
				foreach( $payment_items as $payment_item ) {
					if( (int) $payment_item['id'] !== (int) $download_id ) {
						continue;
					}
					if( isset( $payment_item['options']['price_id'] ) ) {
						$options['price_id'] = $payment_item['options']['price_id'];
						break;
					}
				}
			} else {
				$options['price_id'] = $price_id;
			}
			$cart_key = edd_get_item_position_in_cart( $download_id, $options );
			edd_remove_from_cart( $cart_key );
			edd_add_to_cart( $download_id, $options );
			$valid = true;
	}		


	if( $valid ) {
			$keys = (array) EDD()->session->get( 'edd_renewal_keys' );
			$keys[ $download_id ] = $license;
			EDD()->session->set( 'edd_is_renewal', '1' );
			EDD()->session->set( 'edd_renewal_keys', $keys );
			//$redirect = edd_get_checkout_uri();
		}		
		//do_action('edd_apply_license_renewal', array('edd_license_key' => $license_key ,'edd_action' => 'apply_license_renewal'));
	}
	return true;
}
add_action( 'edd_post_add_to_cart', "edd_downloads_renew_to_cart", 11, 2);
/* automatically renew license on click Renew button - end */

function edd_downloads_upgrade_to_cart($item){
	$post_data 			= urldecode($_REQUEST['post_data']);
	$post_data_formated = array();
	if( (strstr($post_data,"upgrade_license") && strstr($post_data,"upgrade_product_to"))){
		$post_data_split = explode("&",$post_data);
		foreach($post_data_split as $data_split){
			$data_array = explode("=",$data_split);
			$post_data_formated[$data_array[0]] = $data_array[1];
		}
	}
	if(isset($post_data_formated['upgrade_license']) && isset($post_data_formated['upgrade_product_to'])){
		$price_id								= isset($item['options']['price_id']) ? $item['options']['price_id'] : 0;
		$download_files 						= edd_get_download_files( $post_data_formated['upgrade_product_to'], $price_id );
		$post_meta 								= get_post_meta(absint($post_data_formated['upgrade_license']), '_edd_sl_download_id', true);
		$old_price_id							= get_post_meta(absint($post_data_formated['upgrade_license']), '_edd_sl_download_price_id', true);
		$old_download_file 						= edd_get_download_files( $post_meta, $old_price_id );
		
		$item['upgrade'] 						= array();
		$item['upgrade']['old_product']			= absint($post_data_formated['old_product']);		
		$item['upgrade']['new_attachment_id']	= isset($download_files[0]['attachment_id']) ? absint($download_files[0]['attachment_id']) : 0;
		$item['upgrade']['old_attachment_id']	= isset($old_download_file[0]['attachment_id']) ? absint($old_download_file[0]['attachment_id']) : 0;

		$item['upgrade']['upgrade_license'] 	= absint($post_data_formated['upgrade_license']);
		$item['upgrade']['upgrade_product_to'] 	= absint($post_data_formated['upgrade_product_to']);
		if(isset($item['options']) && !empty($item['options'])){
			$item['upgrade']['options'] 	= $item['options'];	
		}
		$old_product_price 					= edd_get_cart_item_price( absint($post_data_formated['old_product']), $item['options'] );
		$new_product_price 					= edd_get_cart_item_price( absint($post_data_formated['upgrade_product_to']), $item['options'] );
		$item['upgrade']['upgrade_price']	= edd_format_amount($new_product_price - $old_product_price);//edd_get_cart_item_price( absint($post_data_formated['upgrade_product_to']), $item['options'] );		
		$payment_id_lic						= get_post_meta( $post_data_formated['upgrade_license'], '_edd_sl_payment_id', true );
		$item['upgrade']['upgrade_product_used'] 	= 0;
		
		$session 							= edd_get_purchase_session();
		$payment_key 						= edd_get_payment_key( absint($payment_id_lic) );
		$session['purchase_key']			= $payment_key;
		edd_set_purchase_session( $session );
		EDD()->session->set( 'upgrade_license', $payment_key );
	}
	return $item;
}
add_filter( 'edd_add_to_cart_item', 'edd_downloads_upgrade_to_cart', 11);

function edd_downloads_filter_cart_item_price($price, $download_id, $options, $include_taxes = NULL){
	if (!isset($options['cart_row'])) {
		
		return $price;
		
	}
	$cart_items = edd_get_cart_contents();
	if(!empty($cart_items)){
		$item = $cart_items[$options['cart_row']];
		//foreach($cart_items as $item){
			if($item['id'] == $download_id){
				if(isset($item['upgrade']) && isset($item['upgrade']['upgrade_price'])){
					return $item['upgrade']['upgrade_price'];
				}
				//break;
			}
		//}
	}
	return $price;
}
add_filter( 'edd_cart_item_price', 'edd_downloads_filter_cart_item_price', 100, 4);

function edd_downloads_upgrade_license($license_id, $d_id, $payment_id, $type){

	$payment_meta = edd_get_payment_meta( $payment_id );
	if(empty($payment_meta)){
		return true;
	}
	$remove = 0;
	foreach($payment_meta['cart_details'] as $product){
		if(isset($product['item_number']['upgrade']) && !empty($product['item_number']['upgrade'])){
			$remove 					= 1;
			$old_attachment_id			= absint($product['item_number']['upgrade']['old_attachment_id']);
			$new_attachment_id			= absint($product['item_number']['upgrade']['new_attachment_id']);
			$old_product 				= absint($product['item_number']['upgrade']['old_product']);
			//$old_product 				= get_post_meta( $product['item_number']['upgrade']['upgrade_license'], '_edd_sl_download_id', true );

			$payment_id_lic				= get_post_meta( $product['item_number']['upgrade']['upgrade_license'], '_edd_sl_payment_id', true );			
			$upgrade_meta 				= edd_get_payment_meta( absint($payment_id_lic) );
			//$redirect_key				= get_post_meta( $product['item_number']['upgrade']['upgrade_license'], '_edd_payment_purchase_key', true );
			$payment_key 				= edd_get_payment_key( absint($payment_id_lic) );
			$new_product				= get_post($product['item_number']['upgrade']['upgrade_product_to']);
			
			if(isset($upgrade_meta['downloads'])){
				foreach($upgrade_meta['downloads'] as $row => $download){
					//$log .= $download['id'] ."==". $old_product;
					if($download['id'] == $old_product){
						$upgrade_meta['downloads'][$row]['id'] = $product['item_number']['upgrade']['upgrade_product_to'];
						if(isset($product['item_number']['upgrade']['options']) && !empty($product['item_number']['upgrade']['options'])){
							$upgrade_meta['downloads'][$row]['options'] = $product['item_number']['upgrade']['options'];
						}else{
							$upgrade_meta['downloads'][$row]['options'] = array();							
						}
						if(isset($upgrade_meta['downloads'][$row]['upgrade'])){
							unset($upgrade_meta['downloads'][$row]['upgrade']);
						}
						
						
						$upgrade_meta['cart_details'][$row]['name']					= $new_product->post_title;
						$upgrade_meta['cart_details'][$row]['id'] 					= $product['item_number']['upgrade']['upgrade_product_to'];
						$upgrade_meta['cart_details'][$row]['item_number']['id'] 	= $product['item_number']['upgrade']['upgrade_product_to'];
						
						if(isset($payment_meta['cart_details'])){
							foreach($payment_meta['cart_details'] as $row_payment => $payment){
								if($old_product == $payment['item_number']['upgrade']['old_product']){
									$upgrade_meta['cart_details'][$row]['item_price'] 	= $payment['item_price'];
									$upgrade_meta['cart_details'][$row]['quantity'] 	= $payment['quantity'];
									$upgrade_meta['cart_details'][$row]['discount'] 	= $payment['discount'];
									$upgrade_meta['cart_details'][$row]['subtotal'] 	= $payment['subtotal'];
									$upgrade_meta['cart_details'][$row]['tax'] 			= $payment['tax'];
									$upgrade_meta['cart_details'][$row]['price'] 		= $payment['price'];
								}
							}
						}
						
						if(isset($product['item_number']['upgrade']['options']) && !empty($product['item_number']['upgrade']['options'])){
							$upgrade_meta['cart_details'][$row]['item_number']['options'] = $product['item_number']['upgrade']['options'];
						}else{
							$upgrade_meta['cart_details'][$row]['item_number']['options'] = array();							
						}						
						if(isset($upgrade_meta['cart_details'][$row]['item_number']['upgrade'])){
							unset($upgrade_meta['cart_details'][$row]['item_number']['upgrade']);
						}
												
					}
				}
			}
			
			$sites = edd_software_licensing()->get_sites( $product['item_number']['upgrade']['upgrade_license'] );

			if(array_key_exists($old_attachment_id, $sites)){
				$update_sites = $sites[$old_attachment_id];
				unset($sites[$old_attachment_id]);
				$sites[$new_attachment_id] = $update_sites;
				update_post_meta( $product['item_number']['upgrade']['upgrade_license'], '_edd_sl_sites', $sites );
			}
			
			update_post_meta( $payment_id_lic, 'edd_price', $product['item_number']['upgrade']['upgrade_price'] );
			update_post_meta( $payment_id_lic, '_edd_payment_total', $product['item_number']['upgrade']['upgrade_price'] );			
			update_post_meta( $payment_id_lic, '_edd_payment_meta', $upgrade_meta );

			// Store the updated user ID in the payment meta
			update_post_meta( $product['item_number']['upgrade']['upgrade_license'], '_edd_sl_download_id', $product['item_number']['upgrade']['upgrade_product_to'] );
		}
	}
	if($remove == 1){
		wp_delete_post($license_id);
		wp_delete_post($payment_id);	
		//delete_post_meta( $post_id, $field );
		
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "postmeta WHERE post_id = %d OR post_id = %d", $license_id, $payment_id) );
	}
	return true;
}
add_action( 'edd_sl_store_license', "edd_downloads_upgrade_license", 11, 4);

/* Domains functionality */
function edd_downloads_check_domain(){
	$response 	= new stdClass();
	if( (isset($_REQUEST['edd_downloads_domains_domain'])) ){	
		$_REQUEST['edd_downloads_domains_domain'] = filter_var($_REQUEST['edd_downloads_domains_domain'], FILTER_SANITIZE_URL);
		if (!strstr($_REQUEST['edd_downloads_domains_domain'], 'http')) {
			$_REQUEST['edd_downloads_domains_domain'] = "http://".$_REQUEST['edd_downloads_domains_domain'];
		}
		if (preg_match("|[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,8}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?|i",$_REQUEST['edd_downloads_domains_domain'])) {
			$response->message = "Ok";		
		}else{
			$response->error = "Error";
		}
	} else {
		$response->error = "Error";
	}
	echo json_encode($response);
	exit;	
}
add_action( 'wp_ajax_edd_downloads_check_domain', "edd_downloads_check_domain", 11);

/* Domains functionality */
function edd_downloads_save_domains_sites(){

	if( is_user_logged_in() ) {
		$user_id = get_current_user_id();
	} else {
		return false;
	}

	if( (isset($_POST['domains_action']) && "save_domains_sites" == esc_html($_POST['domains_action'])) ){	
		// Remove all illegal characters from a url
		$_POST['edd_downloads_domains_domain'] = filter_var($_POST['edd_downloads_domains_domain'], FILTER_SANITIZE_URL);

		if (!strstr($_POST['edd_downloads_domains_domain'], 'http')) {
			$_POST['edd_downloads_domains_domain'] = "http://".$_POST['edd_downloads_domains_domain'];
		}

		if (preg_match("|[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,8}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?|i",$_POST['edd_downloads_domains_domain'])) {
			//$_POST['edd_downloads_domains_domain'] = substr($_POST['edd_downloads_domains_domain'], 0, -1);
			$license_id 					= isset($_REQUEST['license_id']) ? absint($_REQUEST['license_id']) : false;
			$domain_changes 				= isset($_REQUEST['domain_changes']) ? absint($_REQUEST['domain_changes']) + 1 : 1;		
			$product_id 					= isset($_REQUEST['product_id']) ? absint($_REQUEST['product_id']) : false;		
			$edd_downloads_domains_domain	= isset($_REQUEST['edd_downloads_domains_domain']) ? esc_url($_REQUEST['edd_downloads_domains_domain']) : '';
			if( empty( $license_id ) || empty( $product_id ))
				return false;	
				
				if($edd_downloads_domains_domain != ''){
					//edd_software_licensing()->insert_site( $license_id, $edd_downloads_domain_insert );
					$sites = edd_software_licensing()->get_sites( $license_id );
					if($sites[$product_id."_changes"] > 1){
						return false;
					}
					$edd_downloads_domains_domain = trailingslashit( $edd_downloads_domains_domain );
					$edd_downloads_domains_domain = str_replace('https://','',$edd_downloads_domains_domain);
					$edd_downloads_domains_domain = str_replace('http://','',$edd_downloads_domains_domain);				
					$sites[$product_id] = $edd_downloads_domains_domain;
					$sites[$product_id."_changes"] = $domain_changes;
					
					$license_key = get_post_meta( $license_id, '_edd_sl_key', true );
					if ($license_key != '') {
						
						$download_id = edd_software_licensing()->get_download_by_license( $license_key );
						
						$post      = get_post( $download_id );
						edd_software_licensing()->activate_license(array('key' => $license_key, 'item_name' => sanitize_title( $post->post_title )));
						
					}
					return update_post_meta( $license_id, '_edd_sl_sites', $sites );				
				}
		}else{
			$_POST['edd_downloads_domain_error'] = 1;
			return FALSE;
		}
	}
}
add_action( 'init', 'edd_downloads_save_domains_sites', 11 );

function edd_downloads_check_download_file($files, $download_id, $variable_price_id){

	global $wpdb;
	global $edd_receipt_args, $edd_options;

    if( is_user_logged_in() ) {
        $user_id = get_current_user_id();
    } else {
        return false;
    }

	if( empty( $edd_receipt_args ) )
		return $files;

	$payment   = get_post( $edd_receipt_args['id'] );

	$license_ids  = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_edd_sl_payment_id' AND meta_value = %d ", $payment->ID ) );

	if( empty( $license_ids ) )
		return false;

	foreach($license_ids as $license_id){
		$post_meta = get_post_meta($license_id->post_id, '_edd_sl_download_id', true);
		if($post_meta == $download_id){
			$row 				= new stdClass();
			$row->domain 		= edd_software_licensing()->get_sites( $license_id->post_id );
			$row->dev_domains	= get_post_meta( $license_id->post_id, '_edd_sl_sites_dev', true );

			//hide download if domains are not added by user
			if(empty($row->domain) || empty($row->dev_domains)){
				echo _('You must enter support domain and install domain(s) before you can download','edd_downloads');				
				return array();
			}
			
			//hide download if user license for this product expired
			if( 'expired' == edd_software_licensing()->get_license_status( $license_id->post_id ) ) {
				echo _('Your license expired','edd_downloads');
				return array();
			}			
		}
	}
	return $files;
}

/* Discount functionality override */
function edd_downloads_ajax_apply_discount(){

	$existing_discount 	= edd_get_cart_discounts();
	$discount_code 		= $_POST['code'];
	if(!empty($existing_discount) && in_array($discount_code, $existing_discount)){
		$return['msg']  = __('This code has already been applied','edd_downloads');
		edd_unset_error( 'edd-discount-error' );
		
		// Allow for custom discount code handling
		$return = apply_filters( 'edd_ajax_discount_response', $return );

		echo json_encode($return);					
	}else{
		edd_ajax_apply_discount();
	}
	edd_die();	
}
add_action( 'wp_ajax_edd_apply_discount', 'edd_downloads_ajax_apply_discount', 1 );
add_action( 'wp_ajax_nopriv_edd_apply_discount', 'edd_downloads_ajax_apply_discount', 1 );

/* Cart module price format */
function edd_downloads_filter_edd_cart_item($item, $id){
	return str_replace("<span class=\"edd-cart-item-separator\">-</span>","",$item);
}
add_filter( 'edd_cart_item', 'edd_downloads_filter_edd_cart_item', 10000, 2  );

function edd_downloads_add_screenshots($first = 0)
{
	global $post;
	$screenshots = get_post_meta($post->ID,'edd_screenshots',true);
	$screenshots = explode(',',$screenshots);
	$screenshots = array_filter($screenshots);
	$thumbnail_width = get_post_meta($post->ID,'edd_screenshots_thumbnail_width',true);
	$thumbnail_height = get_post_meta($post->ID,'edd_screenshots_thumbnail_height',true);
	$render_before = get_post_meta($post->ID,'edd_screenshots_render_before',true);
	$render_after = get_post_meta($post->ID,'edd_screenshots_render_after',true);
	//echo $render_before;exit;
	$captions_primary = get_post_meta($post->ID,'edd_screenshots_captions_primary',true);
	$captions_primary = explode(',',$captions_primary);
	$captions_secondary = get_post_meta($post->ID,'edd_screenshots_captions_secondary',true);
	$captions_secondary = explode(',',$captions_secondary);

	if (count($screenshots)>0)
	{
		//print_r($screenshots);exit;
		$html = "<div class='edd-screenshots-container'>";
		$html .= $render_before;
		$html .= "<ul class='edd-ul-screenshots' >";
		
		if(1 == $first)
		{
			$screenshots_first[0] 	= $screenshots[0];
			$screenshots			= $screenshots_first;
		} else {
			unset($screenshots[0]);
		}
		
		foreach ($screenshots as $key=>$image)
		{
			$image = trim($image);
			$html .= "<li class='screenshot-item{$key}' data-id='id-{$key}' >	
						<div>
							<span class='image-block'>
							<a rel='prettyPhoto[gallery]' href='{$image}' title='".str_replace("'","", $captions_primary[$key])." - ".str_replace("'","", $captions_secondary[$key])." '>
								<img  src='".EDD_SCREENSHOTS_URLPATH."edd-thumb.php?src=".urlencode($image)."&w={$thumbnail_width}&h={$thumbnail_width}' alt='".str_replace("'","", $captions_secondary[$key])."' title='".str_replace("'","", $captions_primary[$key])."' />                    
							</a>
							</span>
							<div class='home-portfolio-text'>
								<h2 class='screenshot-caption-primary'>{$captions_primary[$key]}</h2>
								<p class='screenshot-caption-secondary'>{$captions_secondary[$key]}</p>
							</div>                    
						</div>	
					  </li>";
		}

		$html .= "</ul>";
		$html .= $render_after;
		$html .= "</div>";

		return $html;
	}
}

function edd_downloads_get_template_part($templates, $slug, $name){
	if('shortcode' == $slug && 'content-image' == $name){
		$return = array("shortcode-content-image.php");
		return $return;
	}
	return $templates;
}
add_filter( 'edd_get_template_part', 'edd_downloads_get_template_part', 10000, 3  );

/* remove renew option from cart page */
/*function edd_downloads_remove_edd_renew() {
	global $edd_options;
	if (isset($edd_options['edd_sl_renewals']) ){
		unset($edd_options['edd_sl_renewals']);
	}
}
add_action( 'init', "edd_downloads_remove_edd_renew", 1);*/

// Remove EDD error action and display the errors messages on top
remove_action( 'edd_purchase_form_before_submit', 'edd_print_errors' );
remove_action( 'edd_ajax_checkout_errors', 'edd_print_errors' );

function edd_downloads_print_errors_header() {
	$errors = edd_get_errors();
	if ( $errors ) {
		$classes = apply_filters( 'edd_error_class', array(
			'edd_errors'
		) );
		echo '<div class="' . implode( ' ', $classes ) . '">';
			// Loop error codes and display errors
			foreach ( $errors as $error_id => $error ) {
				echo '<p class="edd_error" id="edd_error_' . $error_id . '"><strong>' . __( 'Error', 'edd_downloads' ) . '</strong>: ' . $error . '</p>';
			}
		echo '</div>';	
	}
}
add_action( 'edd_before_purchase_form', 'edd_downloads_print_errors_header' );

function edd_downloads_print_errors() {
	$errors = edd_get_errors();
	if ( $errors ) {
		$classes = apply_filters( 'edd_error_class', array(
			'edd_errors'
		) );
		// Loop error codes and display errors
		foreach ( $errors as $error_id => $error ) {
	
			$field_id = "edd_purchase_submit";
	
/*			$field_id = "";
			switch ( $error_id ) {
				case 'logged_in_only':
			
			case 'registration_required':
					$field_id = "edd_purchase_submit";//"edd_checkout_user_info";
				break;
				case 'invalid_email':
					$field_id = "edd_purchase_submit";//"edd_email";
				break;
				case 'invalid_first_name':
					$field_id = "edd_purchase_submit";//"edd_first";
				break;
				case 'invalid_zip_code':
					$field_id = "edd_purchase_submit";//"card_zip";
				break;
				case 'invalid_city':
					$field_id = "edd_purchase_submit";//"card_city";
				break;
				case 'invalid_country':
					$field_id = "edd_purchase_submit";//"billing_country";
				break;
				case 'invalid_state':
					$field_id = "edd_purchase_submit";//"card_state";
				break;
				case 'empty_card':
					$field_id = "edd_purchase_submit";//"card_number";
				break;
				case 'empty_card_name':
					$field_id = "edd_purchase_submit";//"card_name";
				break;
				case 'empty_cvc':
					$field_id = "edd_purchase_submit";//"card_cvc";
				break;																													
			}*/
			if ("" !== $field_id) {
				echo "<script type=\"text/javascript\">edd_downloads_edd_print_error_line(\"".$error_id."\",\"".$error."\",\"".__( 'Error', 'edd_downloads' )."\",\"".$field_id."\")</script>";
			}
	   }
	}
}

function edd_downloads_print_errors_clear(){
	edd_downloads_print_errors();
	edd_clear_errors();
}
add_action( 'edd_purchase_form_before_submit', 'edd_downloads_print_errors_clear', 1 );
add_action( 'edd_ajax_checkout_errors', 'edd_downloads_print_errors_clear', 1 );

/* create My Downloads page*/
function edd_downloads_downloads_history_shortcode($atts, $content = null){
	if ( is_user_logged_in() ) {
		$action = isset($_REQUEST['action']) ? esc_html( $_REQUEST['action'] ) : '';
		if($action == ''){
			require plugin_dir_path(__FILE__)."template/downloads_history.php";
		}
	} else {
		global $edd_login_redirect;
		
		$edd_login_redirect = get_permalink();
		
		require edd_get_templates_dir()."/shortcode-login.php";		
		
	}
}
add_shortcode( 'downloads_history', 'edd_downloads_downloads_history_shortcode' );

function edd_downloads_filter_edd_settings_email($fields){
	if(!array_key_exists('renew_receipt',$fields)){
		$fields['renew_subject'] = array(
					'id' => 'renew_subject',
					'name' => __( 'Renew Email Subject', 'edd_downloads' ),
					'desc' => __( 'Enter the subject line for the renew receipt email', 'edd_downloads' ),
					'type' => 'text',
					'std'  => __( 'Renew Receipt', 'edd_downloads' )
				);
		$fields['renew_receipt'] = array(
					'id' => 'renew_receipt',
					'name' => __( 'Renew Receipt', 'edd_downloads' ),
					'desc' => __('Enter the email that is sent to users after completing a successful renew. HTML is accepted. Available template tags:', 'edd_downloads') . '<br/>' . edd_get_emails_tags_list(),
					'type' => 'rich_editor',
					'std'  => __( "Dear", "edd_downloads" ) . " {name},\n\n" . __( "Thank you for your renewal.", "edd_downloads" ) . "\n\n{sitename}"
				);
		$fields['purchase_receipt'] = array(
					'id' => 'purchase_receipt',
					'name' => __( 'Purchase Receipt', 'edd_downloads' ),
					'desc' => __('Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:', 'edd_downloads') . '<br/>' . edd_get_emails_tags_list(),
					'type' => 'rich_editor',
					'std'  => __( "Dear", "edd_downloads" ) . " {name},\n\n" . __( "Thank you for your purchase. ", "edd_downloads" ) . "\n\n{sitename}"
				);				
	}
	return $fields;
}
add_filter( 'edd_settings_emails', 'edd_downloads_filter_edd_settings_email' );

/**
 * Retrieve a page given its title.
 *
 * @since 2.1.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string       $page_title Page title
 * @param string       $output     Optional. Output type. OBJECT, ARRAY_N, or ARRAY_A.
 *                                 Default OBJECT.
 * @param string|array $post_type  Optional. Post type or array of post types. Default 'page'.
 * @return WP_Post|null WP_Post on success or null on failure
 */
function get_page_by_content( $content, $output = OBJECT, $post_type = 'page' ) {
	global $wpdb;

	if ( is_array( $post_type ) ) {
		$post_type = esc_sql( $post_type );
		$post_type_in_string = "'" . implode( "','", $post_type ) . "'";
		$sql = "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_content = '" . $content . "'
			AND post_type IN (" . $post_type_in_string . ")";
	} else {
		$sql = "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_content LIKE '%" . $content . "%'
			AND post_type = '" . $post_type . "'";
	}
	$page = $wpdb->get_var( $sql );
	if ( $page )
		return get_post( $page, $output );

	return null;
}
function edd_downloads_add_file_paths($file_paths){
	
	array_unshift($file_paths, plugin_dir_path( __FILE__ )."template");
	return $file_paths;
}
add_filter( 'edd_template_paths', 'edd_downloads_add_file_paths' );
function edd_downloads_add_renew_title($title, $id){
	$post = get_post();
	$keys = (array) EDD()->session->get( 'edd_renewal_keys' );
	$cart = (array) EDD()->session->get( 'edd_cart' );
	//$license_key 	= edd_software_licensing()->get_license_key($post_data_formated['license_id']);
	
	//$keys[ $download_id ] = $license;
	if(array_key_exists($id,$keys) && $post->post_content == '[download_checkout]'){
		$title .= __( ' (renewal)', 'edd_downloads' );
	} 
	return $title;
}
add_filter( 'the_title', 'edd_downloads_add_renew_title', 10, 2 );

function edd_downloads_change_page_title($title, $sep){
	$post = get_post();
	if($post->post_content == '[purchase_history]' && $post->post_title == 'My Orders' && esc_html($_REQUEST['action']) == 'manage_licenses'){
		$title = __( 'Downloads for order #', 'edd_downloads' ).edd_get_payment_number( absint($_REQUEST['payment_id']) );
	}
	return $title;
}
add_filter( 'wp_title', 'edd_downloads_change_page_title', 100, 2 );

function edd_downloads_change_the_title($title, $sep){
	$post = get_post($sep);
	if($post->post_content == '[purchase_history]' && $post->post_title == 'My Orders' && esc_html($_REQUEST['action']) == 'manage_licenses'){
		$title = __( 'Downloads for order #', 'edd_downloads' ).edd_get_payment_number( absint($_REQUEST['payment_id']) );
	}
	return $title;
}
add_filter( 'the_title', 'edd_downloads_change_the_title', 100, 2 );

/**	
 * Has User Purchased	
 *	
 * Checks to see if a user has purchased a download.	
 *	
 * @access      public	
 * @since       1.0	
 * @param       int $user_id - the ID of the user to check	
 * @param       array $downloads - Array of IDs to check if purchased. If an int is passed, it will be converted to an array	
 * @param       int $variable_price_id - the variable price ID to check for	
 * @return      boolean - true if has purchased and license is active, false otherwise	
 */	
function edd_downloads_has_user_purchased( $user_id, $downloads, $variable_price_id = null, $verify_purchase = false ) {	
	$users_purchases = edd_get_users_purchases( $user_id );	
	$return = false;	
	if ( ! is_array( $downloads ) ) {	
		$downloads = array( $downloads );	
	}	
	$now	 		= strtotime(date('Y-m-d H:i:s'));	
	if ( $users_purchases ) {	
		foreach ( $users_purchases as $purchase ) {	
			$purchased_files = edd_get_payment_meta_downloads( $purchase->ID );	
				
			$licenses = edd_software_licensing()->get_licenses_of_purchase( $purchase->ID );	
			$licenses_products = array();	
			if( is_array( $licenses ) ){	
				foreach($licenses as $license){	
					$download_id 	= get_post_meta($license->ID, '_edd_sl_download_id', true);	
					$status 		= get_post_meta($license->ID, '_edd_sl_status', true);	
					$expire 		= get_post_meta($license->ID, '_edd_sl_expiration', true);	
					$licenses_products[$download_id] 			= array();	
					$licenses_products[$download_id]['status'] 	= $status;	
					$licenses_products[$download_id]['expire'] 	= $expire;					
				}	
			}else{	
				return false;	
			}	
			if ( is_array( $purchased_files ) ) {	
				foreach ( $purchased_files as $download ) {	
					if ( in_array( $download['id'], $downloads ) ) {	
						//check to see if the license is active	
						//echo $licenses_products[$download['id']]['expire'] . ">" . $now . "==========";	
						if(isset($licenses_products[$download['id']]['expire']) && $now > $licenses_products[$download['id']]['expire']){// || $licenses_products[$download['id']]['status'] == 'inactive'	
							if($verify_purchase){
								return "purchased_expired";
							}else{
								return false;
							}
						}	
						$variable_prices = edd_has_variable_prices( $download['id'] );	
						if ( $variable_prices && ! is_null( $variable_price_id ) && $variable_price_id !== false ) {	
							if ( isset( $download['options']['price_id'] ) && $variable_price_id == $download['options']['price_id'] ) {	
								return true;	
							} else {	
								return false;	
							}	
						} else {	
							return true;	
						}	
					}	
				}	
			}	
		}	
	}	
	return false;	
}

function registration_checkout_user_fields($user_args, $user_data){
	
	$user_args['user_login'] = isset( $user_data['user_login'] ) ? $user_data['user_login'] : $user_args['user_login'];

	// set user pass
	$user_args['user_pass'] = isset( $user_data['user_pass'] ) ? $user_data['user_pass'] : wp_generate_password( 12, false ); // generate random password

	return $user_args;
}
add_filter( 'edd_auto_register_insert_user_args', 'registration_checkout_user_fields', 1, 2 );

function edd_downloads_updated_sites($meta_id, $object_id, $meta_key, $_meta_value) {
	
	if ($meta_key == '_edd_sl_sites' && is_admin()) {
		
		$license_id 	= absint( $_GET['license_id'] );
		$site_url   	= urldecode( $_GET['site_url'] );
		$sites 			= edd_software_licensing()->get_sites( $license_id );
		$download_id 	= get_post_meta( $license_id, '_edd_sl_download_id', true );
		$download_files = edd_get_download_files( $download_id );

		if (empty($download_files)) {
			return true;
		} else {
			$product_id = $download_files[0]['attachment_id'];
		}
		
/*		unset($sites[$product_id.'_changes']);
		$current_values = count($sites);

		if ($current_values > 0) {
			$sites[$product_id] = $sites[0];
			$sites[$product_id.'_changes'] = $current_values;
			unset ($sites[0]);
		}*/ 

		$updated_sites = array();
		$updated_sites[$product_id] = end($sites);
		$updated_sites[$product_id.'_changes'] = count($sites)-1;		

		if (!empty($sites)) {
			$new_sites = serialize($updated_sites);
		} else {
			$new_sites = "";
		}

		global $wpdb;
		$wpdb->query( "UPDATE ".$wpdb->prefix."postmeta SET `meta_value` = '" . $new_sites . "' WHERE `post_id` = " . $license_id . " AND `meta_key` = '_edd_sl_sites' " );
	}
}
add_action( 'updated_post_meta', 'edd_downloads_updated_sites', 10, 4 );

function edd_downloads_get_sites($sites, $license_id){
	
	if (is_admin() && !empty($sites)) {	

		foreach ($sites as $sites_row => $sites_value) {
			if (strstr($sites_row,"_changes")) {
				unset($sites[$sites_row]);
			}
		}
	}

	return $sites;
}
add_filter( 'edd_sl_get_sites', 'edd_downloads_get_sites', 1, 2 );
/*function edd_dwqa_get_product_by_question_category($cat_id){	
	global $wpdb;	
	$product_id  = $wpdb->get_var( $wpdb->prepare( "SELECT edd_product_id FROM " . $wpdb->prefix . "edd_dwqa_categories WHERE dwqa_category_id = %d ", $cat_id ) );	
	if($product_id > 0){	
		return $product_id;	
	} else {	
		return false;	
	}	
}	

function edd_dwqa_get_question_category_by_product_id($product_id){	
	global $wpdb;	
	$cat_id  = $wpdb->get_var( $wpdb->prepare( "SELECT dwqa_category_id FROM " . $wpdb->prefix . "edd_dwqa_categories WHERE edd_product_id = %d ", $product_id ) );	
	if($cat_id > 0){	
		return $cat_id;	
	} else {	
		return false;	
	}	
function edd_downloads_save_domains_sites(){
	$response 	= new stdClass();
	if( is_user_logged_in() ) {
		$user_id = get_current_user_id();
	} else {
		//return false;
		$response->error = "Error";
	}

	if( (isset($_POST['domains_action']) && "save_domains_sites" == esc_html($_POST['domains_action'])) ){	
		// Remove all illegal characters from a url
		$_POST['edd_downloads_domains_domain'] = filter_var($_POST['edd_downloads_domains_domain'], FILTER_SANITIZE_URL);

		if (!strstr($_POST['edd_downloads_domains_domain'], 'http')) {
			$_POST['edd_downloads_domains_domain'] = "http://".$_POST['edd_downloads_domains_domain'];
		}

		if (preg_match("|[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,8}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?|i",$_POST['edd_downloads_domains_domain'])) {
			//$_POST['edd_downloads_domains_domain'] = substr($_POST['edd_downloads_domains_domain'], 0, -1);
			$license_id 					= isset($_REQUEST['license_id']) ? absint($_REQUEST['license_id']) : false;
			$domain_changes 				= isset($_REQUEST['domain_changes']) ? absint($_REQUEST['domain_changes']) + 1 : 1;		
			$product_id 					= isset($_REQUEST['product_id']) ? absint($_REQUEST['product_id']) : false;		
			$edd_downloads_domains_domain	= isset($_REQUEST['edd_downloads_domains_domain']) ? esc_url($_REQUEST['edd_downloads_domains_domain']) : '';
			if( empty( $license_id ) || empty( $product_id ))
				//return false;	
				$response->error = "Error";
				if($edd_downloads_domains_domain != ''){
					//edd_software_licensing()->insert_site( $license_id, $edd_downloads_domain_insert );
					$sites = edd_software_licensing()->get_sites( $license_id );
					if($sites[$product_id."_changes"] > 1){
						//return false;
				
						$response->error = "Error";
					}
					$edd_downloads_domains_domain = trailingslashit( $edd_downloads_domains_domain );
					$edd_downloads_domains_domain = str_replace('https://','',$edd_downloads_domains_domain);
					$edd_downloads_domains_domain = str_replace('http://','',$edd_downloads_domains_domain);				
					$sites[$product_id] = $edd_downloads_domains_domain;
					$sites[$product_id."_changes"] = $domain_changes;
					
					$license_key = get_post_meta( $license_id, '_edd_sl_key', true );
					if ($license_key != '') {
						
						$download_id = edd_software_licensing()->get_download_by_license( $license_key );
						
						$post      = get_post( $download_id );
						edd_software_licensing()->activate_license(array('key' => $license_key, 'item_name' => sanitize_title( $post->post_title )));
						
					}
					if ( update_post_meta( $license_id, '_edd_sl_sites', $sites ) ) {
					
					$response->message = "Ok";
						
				} else {
					
					$response->error = "Error";
					
				}
			}
		}else{
			//$_POST['edd_downloads_domain_error'] = 1;
			//return FALSE;
		$response->error = "Error";
		}
	} else {
	
	$response->error = "Error";
	
}

	echo json_encode($response);
	exit;	
}
add_action( 'wp_ajax_edd_downloads_check_domain', 'edd_downloads_save_domains_sites', 11 );//init
}*/

