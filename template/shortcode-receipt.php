<?php

/**

 * This template is used to display the purchase summary with [edd_receipt]

 */

global $edd_receipt_args, $edd_options;



$payment   = get_post( $edd_receipt_args['id'] );

$meta      = edd_get_payment_meta( $payment->ID );

$cart      = edd_get_payment_meta_cart_details( $payment->ID, true );

$user      = edd_get_payment_meta_user_info( $payment->ID );

$email     = edd_get_payment_user_email( $payment->ID );

$status    = edd_get_payment_status( $payment, true );

?>

<table id="edd_purchase_receipt">

	<thead>

		<?php do_action( 'edd_payment_receipt_before', $payment, $edd_receipt_args ); ?>



		<?php if ( $edd_receipt_args['payment_id'] ) : ?>

		<tr>

			<th><strong><?php _e( 'Payment', 'edd_downloads' ); ?>:</strong></th>

			<th><?php echo edd_get_payment_number( $payment->ID ); ?></th>

		</tr>

		<?php endif; ?>

	</thead>



	<tbody>



		<tr>

			<td class="edd_receipt_payment_status"><strong><?php _e( 'Payment Status', 'edd_downloads' ); ?>:</strong></td>

			<td class="edd_receipt_payment_status <?php echo strtolower( $status ); ?>"><?php echo $status; ?></td>

		</tr>



		<?php if ( $edd_receipt_args['payment_key'] ) : ?>

			<tr>

				<td><strong><?php _e( 'Payment Key', 'edd_downloads' ); ?>:</strong></td>

				<td><?php echo get_post_meta( $payment->ID, '_edd_payment_purchase_key', true ); ?></td>

			</tr>

		<?php endif; ?>



		<?php if ( $edd_receipt_args['payment_method'] ) : ?>

			<tr>

				<td><strong><?php _e( 'Payment Method', 'edd_downloads' ); ?>:</strong></td>

				<td><?php echo edd_get_gateway_checkout_label( edd_get_payment_gateway( $payment->ID ) ); ?></td>

			</tr>

		<?php endif; ?>

		<?php if ( $edd_receipt_args['date'] ) : ?>

		<tr>

			<td><strong><?php _e( 'Date', 'edd_downloads' ); ?>:</strong></td>

			<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $meta['date'] ) ); ?></td>

		</tr>

		<?php endif; ?>



		<?php if ( ( $fees = edd_get_payment_fees( $payment->ID, 'fee' ) ) ) : ?>

		<tr>

			<td><strong><?php _e( 'Fees', 'edd_downloads' ); ?>:</strong></td>

			<td>

				<ul class="edd_receipt_fees">

				<?php foreach( $fees as $fee ) : ?>

					<li>

						<span class="edd_fee_label"><?php echo esc_html( $fee['label'] ); ?></span>

						<span class="edd_fee_sep">&nbsp;&ndash;&nbsp;</span>

						<span class="edd_fee_amount"><?php echo edd_currency_filter( edd_format_amount( $fee['amount'] ) ); ?></span>

					</li>

				<?php endforeach; ?>

				</ul>

			</td>

		</tr>

		<?php endif; ?>



		<?php if ( $edd_receipt_args['discount'] && $user['discount'] != 'none' ) : ?>

			<tr>

				<td><strong><?php _e( 'Discount code', 'edd_downloads' ); ?>:</strong></td>

				<td><?php echo $user['discount']; ?></td>

			</tr>

		<?php endif; ?>



		<?php if( edd_use_taxes() ) : ?>

			<tr>

				<td><strong><?php _e( 'Tax', 'edd_downloads' ); ?></strong></td>

				<td><?php echo edd_payment_tax( $payment->ID ); ?></td>

			</tr>

		<?php endif; ?>



		<?php if ( $edd_receipt_args[ 'price' ] ) : ?>



			<tr>

				<td><strong><?php _e( 'Subtotal', 'edd_downloads' ); ?></strong></td>

				<td>

					<?php echo edd_payment_subtotal( $payment->ID ); ?>

				</td>

			</tr>



			<tr>

				<td><strong><?php _e( 'Total Price', 'edd_downloads' ); ?>:</strong></td>

				<td><?php echo edd_payment_amount( $payment->ID ); ?></td>

			</tr>



		<?php endif; ?>



		<?php //do_action( 'edd_payment_receipt_after', $payment, $edd_receipt_args ); ?>

	</tbody>

</table>



<?php //do_action( 'edd_payment_receipt_after_table', $payment, $edd_receipt_args ); ?>



<?php if ( $edd_receipt_args[ 'products' ] ) : ?>



	<h3><?php echo apply_filters( 'edd_payment_receipt_products_title', __( 'Products in the order:', 'edd_downloads' ) ); ?></h3>



	<table id="edd_downloads_purchase_receipt_products">

		<thead class="edd_downloads_license_header">

			<th><?php _e( 'Product', 'edd_downloads' ); ?></th>

			<th><?php _e( 'Duration', 'edd_downloads' ); ?></th>

			<th><?php _e( 'Fee', 'edd_downloads' ); ?></th>

			<th><?php _e( 'Download', 'edd_downloads' ); ?></th>

		</thead>

		<tbody>

		<?php if( $cart ) : ?>

			<?php foreach ( $cart as $key => $item ) : ?>

				<?php if( empty( $item['in_bundle'] ) ) : ?>

				<tr class="edd_downloads_receipt_row">

					<td>

						<div class="edd_purchase_receipt_product_name">

                        	<?php

							$item_title = get_the_title( $item['id'] );

							if ( ! empty( $item['item_number']['options'] ) && edd_has_variable_prices( $item['id'] ) ) {

								$item_title .= ' - ' . edd_get_cart_item_price_name( $item );

							}							

							?>

							<a href="<?php echo get_permalink($item['id']); ?>" class="edd_download_file_link"><?php echo esc_html( $item_title ); //$item['name']?></a>

						</div>

					</td>

					<td><?php echo edd_downloads_get_product_duration( $item ); ?></td>

					<td><?php echo edd_currency_filter( edd_format_amount( $item[ 'price' ] ) ); ?></td>

					<td>

						<?php

						$price_id       = edd_get_cart_item_price_id( $item );



						$download_files = edd_get_download_files( $item['id'], $price_id );

                        						

						if( edd_is_payment_complete( $payment->ID ) && edd_receipt_show_download_files( $item['id'], $edd_receipt_args ) ) : 

				

							$page_id = get_page_by_content('[purchase_history]');

							if (empty($page_id)) {
								
								$page_id = new stdClass();
								
								$page_id->ID = 170;
								
							}
							
							?>

	                        <a href="<?php echo esc_url( add_query_arg( array( 'page_id' => $page_id->ID, 'action' => 'manage_licenses', 'payment_id' => $payment->ID ) ) ); ?>" class="edd_download_file_link"><?php _e( 'Download', 'edd_downloads' ); ?></a>

						<?php endif; ?>                                       

                    </td>

				</tr>

				<?php endif; ?>

			<?php endforeach; ?>

		<?php endif; ?>

		</tbody>        

	</table>

<?php endif; ?>

