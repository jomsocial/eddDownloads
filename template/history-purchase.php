<?php

// Retrieve all purchases for the current user

$purchases = edd_get_users_purchases( get_current_user_id(), 20, true, 'any' );

if ( !empty($purchases) ) : ?>

	<table id="edd_user_history">

		<thead>

			<tr class="edd_purchase_row">

				<?php do_action('edd_purchase_history_header_before'); ?>

				<th class="edd_purchase_id"><?php _e('ID', 'edd_downloads'); ?></th>

                <th class="edd_purchase_product"><?php _e('Product', 'edd_downloads'); ?></th>

				<th class="edd_purchase_date"><?php _e('Date', 'edd_downloads'); ?></th>

				<th class="edd_purchase_amount"><?php _e('Amount', 'edd_downloads'); ?></th>

				<th class="edd_purchase_details"><?php _e('Details', 'edd_downloads'); ?></th>

				<?php do_action('edd_purchase_history_header_after'); ?>

			</tr>

		</thead>

		<?php foreach ( $purchases as $post ) : setup_postdata( $post );?>

			<?php 

				$purchase_data 	= edd_get_payment_meta( $post->ID ); //print_r($purchase_data);

				$licenses 		= edd_software_licensing()->get_licenses_of_purchase( $post->ID );

				if(empty($licenses)){

					continue;

				}

				$cart      		= edd_get_payment_meta_cart_details( $post->ID, true );

				//print_r($cart); exit;

				$cart_total_products = array();

				$cart_total_amount 	 = 0;				

				if(is_array($cart) && !empty($cart)){

					foreach($cart as $cart_product){

						$cart_total_products[] = $cart_product['name'];

						$cart_total_amount += $cart_product['price'];

					}

				}

				

				$css_expired 	= '';

				$expired_content= '';

				if( 'expired' == edd_software_licensing()->get_license_status( $licenses[0]->ID ) ) {

					$css_expired 		= '_expired';

					$post_meta 			= get_post_meta($licenses[0]->ID, '_edd_sl_download_id', true);

					$expired_content 	= __(' - Expired', 'edd_downloads') . " " .edd_get_purchase_link( array( 'download_id' => $post_meta ) );

				}				

			?>

			<tr class="edd_purchase_row">

				<?php do_action( 'edd_purchase_history_row_start', $post->ID, $purchase_data ); ?>

				<td class="edd_purchase_id">#<?php echo edd_get_payment_number( $post->ID ); ?></td>

                <td class="edd_purchase_product<?php echo $css_expired; ?>">

                	<?php echo implode(", ",$cart_total_products); echo $expired_content; ?>

				</td>

				<td class="edd_purchase_date"><?php echo date_i18n( get_option('date_format'), strtotime( get_post_field( 'post_date', $post->ID ) ) ); ?></td>

				<td class="edd_purchase_amount">

					<span class="edd_purchase_amount"><?php echo edd_currency_filter( edd_format_amount( $cart_total_amount ) );//edd_currency_filter( edd_format_amount( edd_get_payment_amount( $post->ID ) ) ); ?></span>

				</td>

				<td class="edd_purchase_details">

					<?php if( $post->post_status != 'publish' ) : ?>

					<span class="edd_purchase_status <?php echo $post->post_status; ?>"><?php echo edd_get_payment_status( $post, true ); ?></span>

					<a href="<?php echo add_query_arg( 'payment_key', edd_get_payment_key( $post->ID ), edd_get_success_page_uri() ); ?>">&raquo;</a>

					<?php else: ?>

					<a href="<?php echo add_query_arg( 'payment_key', edd_get_payment_key( $post->ID ), edd_get_success_page_uri() ); ?>"><?php _e( 'View Details', 'edd_downloads' ); ?></a>

					<?php endif; ?>

				</td>

				<?php do_action( 'edd_purchase_history_row_end', $post->ID, $purchase_data ); ?>

			</tr>

		<?php endforeach; ?>

	</table>

	<div id="edd_purchase_history_pagination" class="edd_pagination navigation">

		<?php

		$big = 999999;

		echo paginate_links( array(

			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),

			'format'  => '?paged=%#%',

			'current' => max( 1, get_query_var( 'paged' ) ),

			'total'   => ceil( edd_count_purchases_of_customer() / 20 ) // 20 items per page

		) );

		?>

	</div>

	<?php wp_reset_postdata(); ?>

<?php else : 

	$page_id = get_page_by_content('[downloads columns="3"]', NULL, 'page');

	if (empty($page_id)) {
		
		$page_id = new stdClass();
		
		$page_id->ID = 174;
		
	}	

?>

	<p class="edd-no-purchases"><?php _e('No orders found. To purchase a product please visit our <a href="' . get_permalink( $page_id->ID ) . '">Get it Now</a> page', 'edd_downloads'); ?></p>

<?php endif;