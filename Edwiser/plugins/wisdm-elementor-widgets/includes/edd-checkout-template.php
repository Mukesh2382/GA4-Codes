<?php
/**
 *  This template is used to display the Checkout page when items are in the cart
 */
global $wdm_cart_nonce;
$wdm_cart_nonce = wp_create_nonce( 'wdm-elem-cart' );
global $post; ?>
asdasd
<table class="wdm-block-cart-table" id="edd_checkout_cart" <?php if ( ! edd_is_ajax_disabled() ) { echo 'class="ajaxed"'; } ?>>
	<thead>
		<tr class="edd_cart_header_row wdm_edd_cart_header_row">
			<?php do_action( 'edd_checkout_table_header_first' ); ?>
			<th class="edd_cart_item_name"><?php _e( 'Product Name', 'easy-digital-downloads' ); ?></th>
			<th class="edd_cart_item_license"><?php _e( 'No. of License', 'easy-digital-downloads' ); ?></th>
			<th class="edd_cart_item_price"><?php _e( 'Price', 'easy-digital-downloads' ); ?></th>
			<?php do_action( 'edd_checkout_table_header_last' ); ?>
		</tr>
	</thead>
	<tbody>
		<?php $cart_items = edd_get_cart_contents(); ?>
		<?php do_action( 'edd_cart_items_before' ); ?>
		<?php if ( $cart_items ) : ?>
			<?php foreach ( $cart_items as $key => $item ) : ?>
				<tr class="edd_cart_item" id="edd_cart_item_<?php echo esc_attr( $key ) . '_' . esc_attr( $item['id'] ); ?>" data-download-id="<?php echo esc_attr( $item['id'] ); ?>">
					<?php do_action( 'edd_checkout_table_body_first', $item ); ?>
					<?php $wdm_cart_item_show_more_option = apply_filters( 'wdm_elementor_edd_checkout_cart_item_show_more_option', $item );?>
					<td class="edd_cart_item_name wdm_edd_cart_item_name_value">
						<?php
							$renewal_data = wdm_minimal_renewal_notice( $item );
							list($license_priod,$license_options) = minimal_get_cart_table_details($item);
							$item_name = '<div class="wdm_edd_cart_item_name_div"><span class="product_item_name">'.wdm_minimal_checkout_get_product_title($item['id'])/* edd_get_cart_item_name( $item ) */.'</span>'.(!empty($license_priod)?'<span class="recurring_license_period">'.$license_priod.'</span>':'').'</div>';
							$item_name .= $renewal_data;

							echo $item_name;
							
							/**
							 * Runs after the item in cart's title is echoed
							 * @since 2.6
							 *
							 * @param array $item Cart Item
							 * @param int $key Cart key
							 */
							// do_action( 'edd_checkout_cart_item_title_after', $item, $key );
						?>
					</td>
					<td class="wdm_edd_cart_item_licenses" data-nonce="<?php echo $wdm_cart_nonce?>" data-cart-key="<?php echo $key?>">
						<?php
							echo wdm_minimal_get_license_options_html($license_options,$key,0);
							// echo !empty($wdm_cart_item_show_more_option)?$wdm_cart_item_show_more_option:'';
						?>
					</td>
					<td class="wdm_edd_cart_item_price_value edd_cart_item_price">
						<?php
						echo edd_cart_item_price( $item['id'], $item['options'] );
						do_action( 'edd_checkout_cart_item_price_after', $item );
						?>
					</td>
					<?php do_action( 'edd_checkout_table_body_last', $item ); ?>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php do_action( 'edd_cart_items_middle' ); ?>
		<!-- Show any cart fees, both positive and negative fees -->
		<?php if( edd_cart_has_fees() ) : ?>
			<?php foreach( edd_get_cart_fees() as $fee_id => $fee ) : ?>
				<tr class="edd_cart_fee" id="edd_cart_fee_<?php echo $fee_id; ?>">

					<?php do_action( 'edd_cart_fee_rows_before', $fee_id, $fee ); ?>

					<td class="edd_cart_fee_label"><?php echo esc_html( $fee['label'] ); ?></td>
					<td class="edd_cart_fee_amount"><?php echo esc_html( edd_currency_filter( edd_format_amount( $fee['amount'] ) ) ); ?></td>
					<td></td>
					<?php do_action( 'edd_cart_fee_rows_after', $fee_id, $fee ); ?>

				</tr>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php do_action( 'edd_cart_items_after' ); ?>
	</tbody>
	<tfoot>

		<?php if( has_action( 'edd_cart_footer_buttons' ) ) : ?>
			<tr class="edd_cart_footer_row<?php if ( edd_is_cart_saving_disabled() ) { echo ' edd-no-js'; } ?>">
				<th colspan="3">
					<?php do_action( 'edd_cart_footer_buttons' ); ?>
				</th>
			</tr>
		<?php endif; ?>

		<?php if( edd_use_taxes() && ! edd_prices_include_tax() ) : ?>
			<tr class="edd_cart_footer_row wdm_edd_cart_footer_row edd_cart_subtotal_row"<?php if ( ! edd_is_cart_taxed() ) echo ' style="display:none;"'; ?>>
				<?php do_action( 'edd_checkout_table_subtotal_first' ); ?>
				<th colspan="3" class="edd_cart_subtotal">
					<?php _e( 'Subtotal', 'easy-digital-downloads' ); ?>:&nbsp;<span class="edd_cart_subtotal_amount"><?php echo edd_cart_subtotal(); ?></span>
				</th>
				<?php do_action( 'edd_checkout_table_subtotal_last' ); ?>
			</tr>
		<?php endif; ?>

		<!-- <tr class="edd_cart_footer_row edd_cart_discount_row" <?php if( ! edd_cart_has_discounts() )  echo ' style="display:none;"'; ?>>
			<?php do_action( 'edd_checkout_table_discount_first' ); ?>
			<th colspan="3" class="edd_cart_discount">
				<?php edd_cart_discounts_html(); ?>
			</th>
			<?php do_action( 'edd_checkout_table_discount_last' ); ?>
		</tr> -->

		<?php if( edd_use_taxes() ) : ?>
			<tr class="edd_cart_footer_row wdm_edd_cart_footer_row edd_cart_tax_row"<?php if( ! edd_is_cart_taxed() ) echo ' style="display:none;"'; ?>>
				<?php do_action( 'edd_checkout_table_tax_first' ); ?>
				<th colspan="3" class="edd_cart_tax">
					<?php _e( 'Tax', 'easy-digital-downloads' ); ?>:&nbsp;<span class="edd_cart_tax_amount" data-tax="<?php echo edd_get_cart_tax( false ); ?>"><?php echo esc_html( edd_cart_tax() ); ?></span>
				</th>
				<?php do_action( 'edd_checkout_table_tax_last' ); ?>
			</tr>

		<?php endif; ?>

		<tr class="edd_cart_footer_row wdm_edd_cart_footer_row">
			<?php do_action( 'edd_checkout_table_footer_first' ); ?>
			<th class="edd_cart_total edd_cart_total-label"><?php _e( 'Sub Total', 'easy-digital-downloads' ); ?></th>
			<th colspan="2" class="edd_cart_total"> <span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal(); ?>" data-total="<?php echo edd_get_cart_subtotal(); ?>"><?php echo edd_cart_subtotal(); ?></span></th>
		</tr>

		<!-- <tr class="edd_cart_footer_row">
			<?php do_action( 'edd_checkout_discount' ); ?>
			<th colspan="3" class="edd_cart_total"><?php _e( 'Sub Total', 'easy-digital-downloads' ); ?>: <span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal(); ?>" data-total="<?php echo edd_get_cart_subtotal(); ?>"><?php echo edd_cart_subtotal(); ?></span></th>
		</tr> -->

		<!-- <tr class="edd_cart_footer_row">
			<th colspan="3" class="edd_cart_total"><?php _e( 'Total', 'easy-digital-downloads' ); ?>: <span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal(); ?>" data-total="<?php echo edd_get_cart_total(); ?>"><?php edd_cart_total(); ?></span></th>
			<?php do_action( 'edd_checkout_table_footer_last' ); ?>
		</tr> -->
	</tfoot>
</table>
<table style="display:none" class="m-wdm-block-cart-table" id="m_edd_checkout_cart" <?php if ( ! edd_is_ajax_disabled() ) { echo 'class="ajaxed"'; } ?>>
	<thead>
		<tr class="edd_cart_header_row wdm_edd_cart_header_row">
			<?php do_action( 'edd_checkout_table_header_first' ); ?>
			<th class="edd_cart_item_name"><?php _e( 'Product Name', 'easy-digital-downloads' ); ?></th>
			<!-- <th class="edd_cart_item_license"><?php //_e( 'No. of License', 'easy-digital-downloads' ); ?></th> -->
			<th class="edd_cart_item_price"><?php _e( 'Price', 'easy-digital-downloads' ); ?></th>
			<?php do_action( 'edd_checkout_table_header_last' ); ?>
		</tr>
	</thead>
	<tbody>
		<?php $cart_items = edd_get_cart_contents(); ?>
		<?php do_action( 'edd_cart_items_before' ); ?>
		<?php if ( $cart_items ) : ?>
			<?php foreach ( $cart_items as $key => $item ) : ?>
				<tr class="edd_cart_item" id="edd_cart_item_<?php echo esc_attr( $key ) . '_' . esc_attr( $item['id'] ); ?>" data-download-id="<?php echo esc_attr( $item['id'] ); ?>">
					<?php do_action( 'edd_checkout_table_body_first', $item ); ?>
					<?php $wdm_cart_item_show_more_option = apply_filters( 'wdm_elementor_edd_checkout_cart_item_show_more_option', $item );?>
					<td class="edd_cart_item_name wdm_edd_cart_item_name_value">
						<?php
							$renewal_data = wdm_minimal_renewal_notice( $item );
							list($license_priod,$license_options) = minimal_get_cart_table_details($item);
							$item_name = '<div class="wdm_edd_cart_item_name_div"><span class="product_item_name">'.wdm_minimal_checkout_get_product_title($item['id'])/* edd_get_cart_item_name( $item ) */.'</span>'.(!empty($license_priod)?'<span class="recurring_license_period">'.$license_priod.'</span>':'').'</div>';
							$item_name .= $renewal_data;

							echo $item_name;
							
							/**
							 * Runs after the item in cart's title is echoed
							 * @since 2.6
							 *
							 * @param array $item Cart Item
							 * @param int $key Cart key
							 */
							// do_action( 'edd_checkout_cart_item_title_after', $item, $key );
						?>
						<div class="wdm_edd_cart_item_licenses" data-nonce="<?php echo $wdm_cart_nonce?>" data-cart-key="<?php echo $key?>">
							<span class="wdm_edd_cart_item_licenses_heading">No. of Licence</span>
							<?php
								echo wdm_minimal_get_license_options_html($license_options,$key,1);
								// echo !empty($wdm_cart_item_show_more_option)?$wdm_cart_item_show_more_option:'';
							?>
						</div>
					</td>
					<td class="wdm_edd_cart_item_price_value edd_cart_item_price">
						<?php
						echo edd_cart_item_price( $item['id'], $item['options'] );
						do_action( 'edd_checkout_cart_item_price_after', $item );
						?>
					</td>
					<?php do_action( 'edd_checkout_table_body_last', $item ); ?>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php do_action( 'edd_cart_items_middle' ); ?>
		<!-- Show any cart fees, both positive and negative fees -->
		<?php if( edd_cart_has_fees() ) : ?>
			<?php foreach( edd_get_cart_fees() as $fee_id => $fee ) : ?>
				<tr class="edd_cart_fee" id="edd_cart_fee_<?php echo $fee_id; ?>">

					<?php do_action( 'edd_cart_fee_rows_before', $fee_id, $fee ); ?>

					<td class="edd_cart_fee_label"><?php echo esc_html( $fee['label'] ); ?></td>
					<td class="edd_cart_fee_amount"><?php echo esc_html( edd_currency_filter( edd_format_amount( $fee['amount'] ) ) ); ?></td>
					<td></td>
					<?php do_action( 'edd_cart_fee_rows_after', $fee_id, $fee ); ?>

				</tr>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php do_action( 'edd_cart_items_after' ); ?>
	</tbody>
	<tfoot>

		<?php if( has_action( 'edd_cart_footer_buttons' ) ) : ?>
			<tr class="edd_cart_footer_row<?php if ( edd_is_cart_saving_disabled() ) { echo ' edd-no-js'; } ?>">
				<th colspan="3">
					<?php do_action( 'edd_cart_footer_buttons' ); ?>
				</th>
			</tr>
		<?php endif; ?>

		<?php if( edd_use_taxes() && ! edd_prices_include_tax() ) : ?>
			<tr class="edd_cart_footer_row wdm_edd_cart_footer_row edd_cart_subtotal_row"<?php if ( ! edd_is_cart_taxed() ) echo ' style="display:none;"'; ?>>
				<?php do_action( 'edd_checkout_table_subtotal_first' ); ?>
				<th colspan="3" class="edd_cart_subtotal">
					<?php _e( 'Subtotal', 'easy-digital-downloads' ); ?>:&nbsp;<span class="edd_cart_subtotal_amount"><?php echo edd_cart_subtotal(); ?></span>
				</th>
				<?php do_action( 'edd_checkout_table_subtotal_last' ); ?>
			</tr>
		<?php endif; ?>

		<!-- <tr class="edd_cart_footer_row edd_cart_discount_row" <?php if( ! edd_cart_has_discounts() )  echo ' style="display:none;"'; ?>>
			<?php do_action( 'edd_checkout_table_discount_first' ); ?>
			<th colspan="3" class="edd_cart_discount">
				<?php edd_cart_discounts_html(); ?>
			</th>
			<?php do_action( 'edd_checkout_table_discount_last' ); ?>
		</tr> -->

		<?php if( edd_use_taxes() ) : ?>
			<tr class="edd_cart_footer_row wdm_edd_cart_footer_row edd_cart_tax_row"<?php if( ! edd_is_cart_taxed() ) echo ' style="display:none;"'; ?>>
				<?php do_action( 'edd_checkout_table_tax_first' ); ?>
				<th colspan="3" class="edd_cart_tax">
					<?php _e( 'Tax', 'easy-digital-downloads' ); ?>:&nbsp;<span class="edd_cart_tax_amount" data-tax="<?php echo edd_get_cart_tax( false ); ?>"><?php echo esc_html( edd_cart_tax() ); ?></span>
				</th>
				<?php do_action( 'edd_checkout_table_tax_last' ); ?>
			</tr>

		<?php endif; ?>

		<tr class="edd_cart_footer_row wdm_edd_cart_footer_row">
			<?php do_action( 'edd_checkout_table_footer_first' ); ?>
			<th class="edd_cart_total edd_cart_total-label"><?php _e( 'Sub Total', 'easy-digital-downloads' ); ?></th>
			<th colspan="2" class="edd_cart_total"> <span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal(); ?>" data-total="<?php echo edd_get_cart_subtotal(); ?>"><?php echo edd_cart_subtotal(); ?></span></th>
		</tr>

		<!-- <tr class="edd_cart_footer_row">
			<?php do_action( 'edd_checkout_discount' ); ?>
			<th colspan="3" class="edd_cart_total"><?php _e( 'Sub Total', 'easy-digital-downloads' ); ?>: <span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal(); ?>" data-total="<?php echo edd_get_cart_subtotal(); ?>"><?php echo edd_cart_subtotal(); ?></span></th>
		</tr> -->

		<!-- <tr class="edd_cart_footer_row">
			<th colspan="3" class="edd_cart_total"><?php _e( 'Total', 'easy-digital-downloads' ); ?>: <span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal(); ?>" data-total="<?php echo edd_get_cart_total(); ?>"><?php edd_cart_total(); ?></span></th>
			<?php do_action( 'edd_checkout_table_footer_last' ); ?>
		</tr> -->
	</tfoot>
</table>
