<?php

// Retrieve all purchases for the current user

$purchases = edd_get_users_purchases( get_current_user_id(), 20, true, 'any' );

if( !empty($purchases) ) {

	?>

    <table id="edd_downloads_license_keys" class="edd_downloads_table">

        <thead>

            <tr class="edd_downloads_license_header">

                <?php do_action('edd_sl_license_header_before'); ?>

                <th><?php _e( 'Product', 'edd_downloads' ); ?></th>

                <th><?php _e( 'Duration', 'edd_downloads' ); ?></th>

                <th><?php _e( 'Support Domain', 'edd_downloads' ); ?></th>

                <th><?php _e( 'Download', 'edd_downloads' ); ?></th>

                <th><?php _e( 'Renew / Upgrade', 'edd_downloads' ); ?></th>                

                <?php do_action('edd_sl_license_header_after'); ?>

            </tr>

        </thead>    

		<?php	

        foreach($purchases as $purchase){

    

            $payment_id = absint( $purchase->ID );//$_GET['payment_id' ]

            $user_id    = edd_get_payment_user_id( $payment_id );

            

            if( ! current_user_can( 'edit_shop_payments' ) && $user_id != get_current_user_id() ) {

                return;

            }

            

            global $edd_receipt_args, $edd_options;

            

            $payment   	= get_post( $edd_receipt_args['id'] );

            $meta      	= edd_get_payment_meta( $payment_id );

            $cart      	= edd_get_payment_meta_cart_details( $payment_id, true );

            $user      	= edd_get_payment_meta_user_info( $payment_id );

            $email     	= edd_get_payment_user_email( $payment_id );

            $status    	= edd_get_payment_status( $payment, true );

            $licensing 	= edd_software_licensing();

            $Path 		= $_SERVER['REQUEST_URI'];

            

            // Retrieve all license keys for the specified payment

            $licenses = edd_software_licensing()->get_licenses_of_purchase( $payment_id );

            

            //print_r($cart); exit;

            if ( $licenses ) : ?>

    

				<?php foreach ( $cart as $cart_row => $cart_item ) : ?>

                <?php if( empty( $cart_item['in_bundle'] ) ) : 

                    $price_id       	= edd_get_cart_item_price_id( $cart_item );

                    $download_files 	= edd_get_download_files( $cart_item['id'], $price_id );						

                    $products_domains 	= edd_software_licensing()->get_sites( $licenses[$cart_row]->ID );

        

                    if( edd_is_payment_complete( $payment_id ) ) : //&& edd_receipt_show_download_files( $cart_item['id'], $edd_receipt_args )?>

                        <?php

                        if ( $download_files && is_array( $download_files ) ) :

        

                            foreach ( $download_files as $filekey => $file ) :

                                $post_meta = get_post_meta($licenses[$cart_row]->ID, '_edd_sl_download_id', true);

                                $product   = get_post($file['attachment_id']);

                                

                                if(!empty($product)){

                                    $license_date 	= get_post_meta( $licenses[$cart_row]->ID, '_edd_sl_expiration', true );

									$unlimited_license  = get_post_meta( $cart_item['id'], '_unlimited_license', true );	

			

									$now	 		= strtotime(date('Y-m-d H:i:s'));

		

									$expired_license= $license_date > $now || $unlimited_license == 1 ? 0 : 1;

                                    if (1 === $expired_license) {

                                    ?>

                                    <tr class="edd_downloads_license_row_expired">

                                        <td colspan="6"><?php _e( 'Your license has expired. Please renew your license to be able to download again.', 'edd_downloads' ); ?></td>

                                    </tr>

                                    <?php

                                    }

                                    ?>

                                    <tr class="edd_downloads_license_row">

                                        <td>

											

											<?php $license_number = edd_software_licensing()->get_latest_version( $cart_item['id'] ) ?>

                                            <span class="edd_downloads_file_name">

											

                                            	<?php echo $cart_item['name']; 

													  echo $license_number != '' ? ' (v. ' . $license_number . ') ' : '';

													  echo '<br/>' . $file['name']. '.zip'; 

												?>

											

                                            </span><br/>

                                            <?php edd_downloads_get_license_expiration($licenses[$cart_row]->ID) ?><br/>

                                            <span class="edd_downloads_license_number"><?php _e( 'License No: '.$licensing->get_license_key( $licenses[$cart_row]->ID ), 'edd_downloads' ); ?></span>

                                        </td>

                                        <td><strong><?php echo edd_downloads_get_product_duration($cart_item); ?></strong></td>

                                        <td>

                                            <?php $disabled = isset($products_domains[$product->ID]) && !empty($products_domains[$product->ID]) ? 'disabled' :'';?>

											<form action="<?php echo site_url($Path); ?>" method="post" class="edd_downloads_domains" id="edd_downloads_domains_domain_form_<?php echo absint($bundle_license->ID); ?>">

                                                <input id="edd_downloads_domains_domain" class="ks input" <?php echo $disabled; ?> style="width: 150px;" type="text" name="edd_downloads_domains_domain" value="<?php echo (isset($products_domains[$product->ID])) ? esc_url($products_domains[$product->ID]) : '' ; ?>" autocomplete="off" />

                                                <?php if($disabled == 'disabled'){ ?>

													<button style="submit" onClick="return check_domain(<?php echo absint($bundle_license->ID); ?>);" class="button edd-submit" <?php echo $disabled; ?>><?php echo _e('Save','edd_downloads'); ?></button>

                                                <?php }else{ ?>

													<button style="submit" onClick="return check_domain(<?php echo absint($bundle_license->ID); ?>);" class="button blue edd-submit"><?php echo _e('Save','edd_downloads'); ?></button><br/>

                                                    <span class="edd_downloads_enter_domain">

                                                        <?php 

                                                        if(isset($_POST['edd_downloads_domain_error']) && $_POST['edd_downloads_domain_error'] == 1){

                                                            echo _e('Invalid domain. Please re-enter it in this format: mysite.com','edd_downloads');

                                                        }else{

                                                            //echo _e('Please enter a support domain to download','edd_downloads_');

                                                        }

                                                        ?>                                        

                                                    </span>

                                                <?php } ?>

                                                

												<input type="hidden" name="domains_action" value="save_domains_sites" />

                                                <input type="hidden" name="product_id" value="<?php echo $product->ID; ?>" />

                                                <input type="hidden" name="license_id" value="<?php echo absint( $licenses[$cart_row]->ID ); ?>" /> 

                                                

                                                <input type="hidden" name="domain_changes" value="<?php echo isset($products_domains[$product->ID."_changes"]) ? absint( $products_domains[$product->ID."_changes"] ) : 0; ?>" />                                                               

                                            </form>

											<span class="edd-loading" id="edd_downloads_domains_domain_<?php echo absint($licenses[$cart_row]->ID); ?>_loading"  style="margin-left: 0px; margin-top: 10px; display:none;"><i class="edd-icon-spinner edd-icon-spin"></i></span>

                                        </td>                        

                                        <td><?php

                                            $download_url = edd_get_download_file_url( $meta['key'], $email, $filekey, $cart_item['id'], $price_id );

                                            if(isset($products_domains[$product->ID]) && !empty($products_domains[$product->ID]) && !empty($download_url) && 0 === $expired_license){

                                                ?><button style="submit" onClick="window.location = '<?php echo esc_url( $download_url ); ?>';" class="button green edd-submit"><?php echo _e('Download','edd_downloads'); ?></button><?php

                                            }else{

                                                ?><button style="submit" onClick="javascript:alert('<?php echo _e('Please enter a support domain and click Save to download','edd_downloads'); ?>'); return false;" class="button edd-submit"><?php echo _e('Download','edd_downloads'); ?></button><?php

                                            }

                                            ?>

                                        </td>

                                        <td>

                                            <?php

                                            //show renew option for expired product

        

                                            if( 'expired' == edd_software_licensing()->get_license_status( $licenses[$cart_row]->ID ) ) {

                                                ?>

                                                <div class="edd_purchase_submit_wrapper">

                                                    <?php echo _e( 'Product ' . $cart_item['name'] . ' expired ' ,'edd_downloads' );

                                                    //echo $payment_id;

                                                    //echo $post_meta;

                                                    if(!isset($edd_options['expired'])){

                                                        $edd_options['expired']   				= array();

                                                        $edd_options['expired'][$payment_id] 	= $post_meta;

                                                        $edd_options['payment_id'] 				= $payment_id;

                                                    }else{

                                                        $edd_options['expired'][$payment_id] 	= $post_meta;

                                                        $edd_options['payment_id'] 				= $payment_id;

                                                    }

                                                    //print_r($edd_options);

													$edd_options[$post_meta]					= array( 'download_id' => $post_meta, 'payment_id' => $payment_id );

                                                    echo edd_get_purchase_link( array( 'download_id' => $post_meta, 'payment_id' => $payment_id ) );

                                                    //echo edd_downloads_get_update_link( array( 'download_id' => $post_meta ) );

                                                    ?>

                                                </div>

                                                <br class="clear" />

                                                <?php								

                                            }

                                            edd_downloads_show_upgrade_products($cart_item,$licenses[$cart_row]->ID,$payment_id.$cart_row.$product->ID,$payment_id);

                                            ?>

                                            <script type="text/javascript">edd_downloads_hide_all_buttons(<?php echo $payment_id.$cart_row.$product->ID ?>)</script>                                

                                        </td>                

                                    </tr>

                                    <?php

                                }

                            endforeach;

                        elseif( edd_is_bundled_product( $cart_item['id'] ) ) :

							$bundled_products = edd_get_bundled_products( $cart_item['id'] );
		
							foreach( $bundled_products as $bundle_item ) : 
		
								$product   		= get_post($bundle_item);
		
								$bundle_license = edd_software_licensing()->get_license_by_purchase( $payment_id, $product->ID );
		
								//$post_meta 				= get_post_meta($product->ID, '_edd_sl_download_id', true);
								
								$price_id       	= edd_get_cart_item_price_id( 0 );
								
								$download_files 	= edd_get_download_files( $product->ID, $price_id );
		
								$product_domains_check  = get_post($download_files[0]['attachment_id']);
								
		
								//$cart_key = array_search($bundle_item, array_column($cart, 'id')); // (PHP 5 >= 5.5.0)
		
								$products_domains 	= edd_software_licensing()->get_sites( $bundle_license->ID );
		
								foreach($cart as $cart_row => $cart_item){
		
									if($cart_item['id'] == $bundle_item){
		
										$cart_key = $cart_row;
		
										break;
		
									}
		
								}
								
								$download_files = edd_get_download_files( $bundle_item );
		
								$license_date 	= get_post_meta( $bundle_license->ID, '_edd_sl_expiration', true );
		
								$unlimited_license  = get_post_meta( $bundle_license->ID, '_unlimited_license', true );	
		
								$now	 		= strtotime(date('Y-m-d H:i:s'));
		
								$expired_license= $license_date > $now || $unlimited_license == 1 ? 0 : 1;		
					
		
							?>
		
								<tr class="edd_downloads_license_row">
		
									<td>
		
										<?php $license_number = edd_software_licensing()->get_latest_version( $product->ID ) ?>
		
										<span class="edd_downloads_file_name">
		
									   
		
											<?php echo get_the_title( $product ); 
		
												  echo $license_number != '' ? ' (v. ' . $license_number . ') ' : '';
		
												  echo '<br/>' . $download_files[0]['name']. '.zip';
		
											?>
		
										
		
										</span><br/>                                
		
		
		
										<?php edd_downloads_get_license_expiration($bundle_license->ID) ?><br/>
		
										<strong><?php _e( '(License #'.$licensing->get_license_key( $bundle_license->ID ).')', 'edd_downloads' ); ?></strong>
		
									</td>                        
		
									<td><strong><?php echo edd_downloads_get_product_duration($cart[$cart_key]); ?></strong></td>
		
									<td>
		
										<?php $disabled = isset($products_domains[$product_domains_check->ID]) && !empty($products_domains[$product_domains_check->ID]) ? 'disabled' :'';?>
										
										<?php $disabled = isset($products_domains[$product_domains_check->ID."_changes"]) && $products_domains[$product_domains_check->ID."_changes"] > 1 ? 'disabled' :'';?>
		
										<form action="<?php echo site_url($Path); ?>" method="post" class="edd_downloads_domains" id="edd_downloads_domains_domain_form_<?php echo absint($bundle_license->ID); ?>">
		
											<input id="edd_downloads_domains_domain_<?php echo absint($bundle_license->ID); ?>" class="ks input" <?php echo $disabled; ?> type="text" name="edd_downloads_domains_domain" value="<?php echo (isset($products_domains[$product_domains_check->ID])) ? esc_url($products_domains[$product_domains_check->ID]) : '' ; ?>" autocomplete="off" />
		
											<?php if($disabled == 'disabled'){ ?>
		
												<button style="submit" onClick="return check_domain(<?php echo absint($bundle_license->ID); ?>);" class="button edd-submit" <?php echo $disabled; ?>><?php echo _e('Save','edd_downloads'); ?></button>
		
											<?php }else{ ?>
		
												<button style="submit" onClick="return check_domain(<?php echo absint($bundle_license->ID); ?>);" class="button blue edd-submit"><?php echo _e('Save','edd_downloads'); ?></button><br/>
		
												<span class="edd_downloads_enter_domain">
		
												
		
													<?php 
		
													if(isset($_POST['edd_downloads_domain_error']) && $_POST['edd_downloads_domain_error'] == 1){
		
														echo _e('Invalid domain. Please re-enter it in this format: mysite.com','edd_downloads');
		
													}else{
		
														//echo _e('Please enter a support domain to download','edd_downloads');
		
													}
		
													?>    
		
																					   
		
												</span>
		
											<?php } ?>
		
											<input type="hidden" name="domains_action" value="save_domains_sites" />
		
											<input type="hidden" name="product_id" value="<?php echo $product_domains_check->ID; ?>" />
		
											<input type="hidden" name="license_id" value="<?php echo absint( $bundle_license->ID ); ?>" /> 
		
											
		
											<input type="hidden" name="domain_changes" value="<?php echo isset($products_domains[$product_domains_check->ID."_changes"]) ? absint( $products_domains[$product_domains_check->ID."_changes"] ) : 0; ?>" />
		
										</form>
		
										<span class="edd-loading" id="edd_downloads_domains_domain_<?php echo absint($bundle_license->ID); ?>_loading"  style="margin-left: 0px; margin-top: 10px; display:none;"><i class="edd-icon-spinner edd-icon-spin"></i></span>
		
									</td>                            
		
									<td>
		
										<?php
		
		
										if( $download_files && is_array( $download_files ) ) :
		
		
		
											foreach ( $download_files as $filekey => $file ) :
		
		
		
												$download_url = edd_get_download_file_url( $meta['key'], $email, $filekey, $bundle_item );
		
												if(isset($products_domains[$product_domains_check->ID]) && !empty($products_domains[$product_domains_check->ID]) && !empty($download_url)){
		
													?><button style="submit" onClick="window.location = '<?php echo esc_url( $download_url ); ?>';" class="button green edd-submit"><i class="uk-icon-caret-square-o-down uk-visible-small"></i><span class="uk-hidden-small"><?php echo _e('Download','edd_downloads'); ?></span></button><?php
		
												}elseif(1 === $expired_license){
		
												
		
												?><button style="submit" onClick="javascript:alert('<?php echo _e('Your license has expired. Please renew your license to be able to download again.','edd_downloads'); ?>'); return false;" class="button edd-submit"><i class="uk-icon-caret-square-o-down uk-visible-small"></i><span class="uk-hidden-small"><?php echo _e('Download','edd_downloads'); ?></span></button><?php
		
		
		
											   }else{
		
													?><button style="submit" onClick="javascript:alert('<?php echo _e('Please enter a support domain and click Save to download','edd_downloads'); ?>'); return false;" class="button edd-submit"><i class="uk-icon-caret-square-o-down uk-visible-small"></i><span class="uk-hidden-small"><?php echo _e('Download','edd_downloads'); ?></span></button><?php
		
												}
		
											endforeach;
		
										else :
		
											echo '<li>' . __( 'No downloadable files found for this bundled item.', 'edd_downloads' ) . '</li>';
		
										endif;
		
										?>
		
									</td>
		
									<td>
		
										<?php
		
										//show renew option for expired product
		
										if( 'expired' == edd_software_licensing()->get_license_status( $bundle_license->ID ) ) {
		
											?>
		
											<div class="edd_purchase_submit_wrapper">
		
												<?php
		
												if(!isset($edd_options['expired'])){
		
													$edd_options['expired']   				= array();
		
													$edd_options['expired'][$payment_id] 	= $bundle_item;
		
													$edd_options['payment_id'] 				= $payment_id;
		
												}else{
		
													$edd_options['expired'][$payment_id] 	= $bundle_item;
		
													$edd_options['payment_id'] 				= $payment_id;
		
												}													
		
												$edd_options[$bundle_item]					= array( 'download_id' => $post_meta, 'payment_id' => $payment_id );
		
												echo edd_get_purchase_link( array( 'download_id' => $bundle_item, 'payment_id' => $payment_id ) );
												echo _e( 'Product ' . get_the_title( $bundle_item ) . ' expired ' ,'edd_downloads' );
		
												?>
		
											</div>
		
											<br class="clear" />
		
											<?php								
		
										}
		
										edd_downloads_show_upgrade_products($cart_item,$bundle_license->ID,$payment_id.$cart_row.$bundle_item,$payment_id);
		
										?>
		
										<script type="text/javascript">edd_downloads_hide_all_buttons(<?php echo $payment_id.$cart_row.$bundle_item ?>)</script>                                
		
									</td>                
		
								</tr>
		
								<?php
		
							endforeach;

        

                        else :

                            echo '<li>' . __( 'No downloadable files found.', 'edd_downloads' ) . '</li>';

                        endif;

                    endif;	

                endif;						

                endforeach; ?>

		<?php else : ?>

            <p class="edd_sl_no_keys"><?php _e( 'There are no license keys for this purchase', 'edd_downloads' ); ?></p>

        <?php endif;?>

    <?php } ?>

</table>        

<?php } else{ ?>

	<p class="edd_sl_no_keys"><?php _e( 'You have no purchases', 'edd_downloads' ); ?></p>

<?php }