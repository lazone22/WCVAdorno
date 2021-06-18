<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 * @author     Jamie Madden <support@wcvendors.com>
 */
class WCVendors_Pro_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $wcvendors_pro The ID of this plugin.
	 */
	private $wcvendors_pro;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Is the plugin in debug mode
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool $debug plugin is in debug mode
	 */
	private $debug;

	/**
	 * Script suffix for debugging
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $suffix script suffix for including minified file versions
	 */
	private $suffix;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $wcvendors_pro The name of the plugin.
	 * @param      string $version       The version of this plugin.
	 * @param      bool   $debug         Plugin is in debug mode.
	 */
	public function __construct( $wcvendors_pro, $version, $debug ) {

		$this->wcvendors_pro = $wcvendors_pro;
		$this->version       = $version;
		$this->debug         = $debug;
		$this->base_dir      = plugin_dir_url( __FILE__ );
		$this->suffix        = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || $this->debug ? '' : '.min';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @todo     check if any of the styles are already loaded before enqueing them
	 */
	public function enqueue_styles() {

		global $post;

		$current_page_id  = get_the_ID();
		$feedback_page_id = get_option( 'wcvendors_feedback_page_id', null );
		$view_feedback    = apply_filters( 'wcv_view_feedback', $current_page_id == $feedback_page_id ? true : false );

		// selectWoo style.
		wp_enqueue_style( 'select2' );

		// Store Style.
		if ( is_shop() || is_product() ) {
			wp_enqueue_style( 'wcv-pro-store-style', apply_filters( 'wcv_pro_store_style', $this->base_dir . 'assets/css/store' . $this->suffix . '.css' ), false, $this->version );
		}

		// Dashboard styles.
		if ( wcv_is_dashboard_page( $current_page_id ) || ( $view_feedback ) || ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wcv_pro_dashboard_nav' ) ) || ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wcv_pro_dashboard' ) ) ) {

			wp_enqueue_style( 'parsley-stype', $this->base_dir . 'assets/lib/parsley/parsley' . $this->suffix . '.css' );

			// Ink system.
			wp_enqueue_style( 'wcv-ink', apply_filters( 'wcv_pro_ink_style', $this->base_dir . 'assets/lib/ink-3.1.10/dist/css/ink.min.css' ), array(), '3.1.10', 'all' );

			if ( is_user_logged_in() ) {
				// Dashboard Style.
				wp_enqueue_style( 'wcv-pro-dashboard', apply_filters( 'wcv_pro_dashboard_style', $this->base_dir . 'assets/css/dashboard' . $this->suffix . '.css' ), false, $this->version );
			}

			// flatpickr flatpickr.min
			wp_enqueue_style( 'wcv-datetimepicker-flatpickr-style', $this->base_dir . 'assets/lib/flatpickr/flatpickr.min.css', array(), WCV_PRO_VERSION );
		}

		// SVG Icon Styles.
		wp_enqueue_style(
			'wcv-icons',
			WCV_PRO_PUBLIC_ASSETS_URL . 'css/wcv-icons' . $this->suffix . '.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Add custom wcvendors pro css classes
	 *
	 * @version  1.7.6
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param array $classes - body css classes.
	 *
	 * @return array $classes - body css classes.
	 */
	public function body_class( $classes ) {
		global $post;

		$dashboard_page_id = (array) get_option( 'wcvendors_dashboard_page_id', array() );
		$feedback_page_id  = get_option( 'wcvendors_feedback_page_id' );

		// If the page is a 404 don't load anything
		if ( is_404() ) {
			return;
		}

		$pages = wcv_get_pages();

		$general_class = 'wcvendors wcvendors-pro wcvendors-page';
		if ( is_object( $post ) && in_array( $post->ID, $dashboard_page_id ) || in_array( $post->ID, $pages ) ) {
			$classes[] = $general_class;
		}

		foreach ( $dashboard_page_id as $page_id ) {
			if ( is_page( $page_id ) ) {
				$classes[] = 'wcvendors-pro-dashboard wcvendors-is-single';
			}
		}

		if ( is_page( $feedback_page_id ) ) {
			$classes[] = 'wcv-ratings-page';
		}

		foreach ( $pages as $slug => $_page_id ) {
			if ( is_page( $_page_id ) ) {
				$classes[] = "wcvendors-{$slug}-page";
			}
		}

		if ( is_object( $post ) && WCV_Vendors::is_vendor( $post->post_author ) && 'product' === $post->post_type ) {
			$classes[] = 'wcvendors-single-product wcvendors-product-page';
		}

		if ( WCV_Vendors::is_vendor_page() && is_archive() ) {
			$classes[] = 'wcvendors-store';
		}

		$page = get_query_var( 'page' );
		if ( $page ) {
			$classes[] = "wcvendors-{$page}-page";
		}

		$object = get_query_var( 'object' );
		if ( $object ) {
			if ( 'shop_coupon' === $object ) {
				$object = 'coupon';
			}
			$classes[] = "wcvendors-dashboard-{$object}-page";
		}

		if ( is_object( $post ) && has_shortcode( $post->post_content, 'wcv_pro_vendorslist' ) ) {
			$classes[] = 'wcvendors-vendorlist';
		}

		if ( is_object( $post ) && has_shortcode( $post->post_content, 'wcv_feedback_form' ) ) {
			$classes[] = 'wcvendors-feedback-form';
		}

		return $classes;

	} // body_class()

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since   1.0.0
	 * @version 1.7.4
	 */
	public function enqueue_scripts() {

		$current_page_id     = get_the_ID();
		$file_display        = get_option( 'wcvendors_file_display', '' );
		$tag_separator       = get_option( 'wcvendors_tag_separator', '' );
		$category_limit      = get_option( 'wcvendors_category_limit', '' );
		$tag_limit           = get_option( 'wcvendors_tag_limit', '' );
		$feedback_page_id    = get_option( 'wcvendors_feedback_page_id', null );
		$shipping_settings   = get_option( 'woocommerce_wcv_pro_vendor_shipping_settings', wcv_get_default_vendor_shipping() );
		$store_shipping_type = get_user_meta( get_current_user_id(), '_wcv_shipping_type', true );
		$vendor_select       = wc_string_to_bool( get_option( 'wcvendors_vendor_select_shipping', 'no' ) );
		$shipping_type       = ( $store_shipping_type != '' ) ? $store_shipping_type : $shipping_settings['shipping_system'];

		if ( wcv_is_dashboard_page( $current_page_id ) ) {

			if ( is_user_logged_in() ) {

				wp_enqueue_media();
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-sortable' );

				$localize_search_args = array(
					'i18n_no_matches'        => __( 'No tags found', 'wcvendors-pro' ),
					'i18n_ajax_error'        => __( 'Loading failed', 'wcvendors-pro' ),
					'i18n_input_too_short_1' => __( 'Please enter 1 or more characters', 'wcvendors-pro' ),
					'i18n_input_too_short_n' => __( 'Please enter %qty% or more characters', 'wcvendors-pro' ),
					'i18n_input_too_long_1'  => __( 'Please delete 1 character', 'wcvendors-pro' ),
					'i18n_input_too_long_n'  => __( 'Please delete %qty% characters', 'wcvendors-pro' ),
					'i18n_load_more'         => __( 'Loading more results&hellip;', 'wcvendors-pro' ),
					'i18n_searching'         => sprintf( __( 'Searching %s', 'wcvendors-pro' ), '&hellip;' ),
					'ajax_url'               => admin_url( 'admin-ajax.php' ),
					'nonce'                  => wp_create_nonce( 'wcv-search' ),
					'tag_limit'              => $tag_limit,
				);

				// ChartJS 1.0.2.
				wp_register_script( 'chartjs', $this->base_dir . 'assets/lib/chartjs/Chart' . $this->suffix . '.js', array( 'jquery' ), '1.0.2', true );
				wp_enqueue_script( 'chartjs' );

				// Chart colors.
				$chartjs_colors = array(

					'use_random'               => apply_filters( 'wwcv_order_totals_use_random_colors', get_option( 'wcv_order_totals_chart_use_random_colors' ) ),
					'fill_color'               => apply_filters( 'wcv_order_totals_fill_color', get_option( 'wcv_order_totals_chart_fill_color' ) ),
					'stroke_color'             => apply_filters( 'wcv_order_totals_stroke_color', get_option( 'wcv_order_totals_chart_stroke_color' ) ),
					'hover_fill_color'         => apply_filters( 'wcv_order_totals_hover_fill_color', get_option( 'wcv_order_totals_chart_hover_fill_color' ) ),
					'hover_stroke_color'       => apply_filters( 'wcv_order_totals_hover_stroke_color', get_option( 'wcv_order_totals_chart_hover_stroke_color' ) ),

					// Opaciy settings.
					'fill_opacity'             => apply_filters( 'wcv_order_totals_fill_opacity', get_option( 'wcv_order_totals_chart_fill_opacity' ) ),
					'stroke_opacity'           => apply_filters( 'wcv_order_totals_stroke_opacity', get_option( 'wcv_order_totals_chart_stroke_opacity' ) ),
					'highlight_fill_opacity'   => apply_filters( 'wcv_order_totals_hover_fill_opacity', get_option( 'wcv_order_totals_chart_hover_fill_opacity' ) ),
					'highlight_stroke_opacity' => apply_filters( 'wcv_order_totals_hover_stroke_opacity', get_option( 'wcv_order_totals_chart_hover_stroke_opacity' ) ),

					// Product Totals.
					'wcv_product_totals_chart_base_fill_color' => apply_filters( 'wcv_product_totals_chart_base_fill_color', get_option( 'wcv_product_totals_chart_base_fill_color' ) ),

					'wcv_product_totals_chart_base_hover_color' => apply_filters( 'wcv_product_totals_chart_base_hover_color', get_option( 'wcv_product_totals_chart_base_hover_color' ) ),

				);
				wp_localize_script( 'chartjs', 'chartjs_colors', $chartjs_colors );

				// WCV chart init.
				wp_register_script( 'wcvendors-pro-charts', $this->base_dir . 'assets/js/wcvendors-pro-charts' . $this->suffix . '.js', array( 'chartjs' ), $this->version, true );
				wp_enqueue_script( 'wcvendors-pro-charts' );

				$ink_vars = array();

				// Ink js.
				wp_register_script( 'ink-js', $this->base_dir . 'assets/lib/ink-3.1.10/dist/js/ink-all' . $this->suffix . '.js', array(), '1.11.4', true );
				wp_localize_script( 'ink-js', 'wcv_ink_vars', $ink_vars );
				wp_enqueue_script( 'ink-js' );

				// Ink autoloader.
				wp_register_script( 'ink-autoloader-js', $this->base_dir . 'assets/lib/ink-3.1.10/dist/js/autoload' . $this->suffix . '.js', array( 'jquery' ), '1.11.4', true );
				wp_enqueue_script( 'ink-autoloader-js' );

				// Accounting.
				wp_register_script( 'accounting', $this->base_dir . 'assets/lib/accounting/accounting' . $this->suffix . '.js', array( 'jquery' ), '0.4.2', true );
				wp_localize_script( 'accounting', 'accounting_params', array( 'mon_decimal_point' => wc_get_price_decimal_separator() ) );

				// Product search.
				$localize_product_search_args = array_merge(
					$localize_search_args,
					array(
						'nonce'     => wp_create_nonce( 'wcv-search-products' ),
						'separator' => apply_filters(
							'wcv_product_search_args_separator',
							array(
								',',
								' ',
							)
						),
					)
				);
				wp_register_script( 'wcv-product-search', $this->base_dir . 'assets/js/select' . $this->suffix . '.js', array( 'jquery', 'select2' ), '3.5.2', true );
				wp_localize_script( 'wcv-product-search', 'wcv_product_select_params', $localize_product_search_args );
				wp_enqueue_script( 'wcv-product-search' );

				// Tag search.
				$localize_tag_search_args = array_merge(
					$localize_search_args,
					array(
						'i18n_matches_1'            => __( 'One tag is available, press enter to select it.', 'wcvendors-pro' ),
						'i18n_matches_n'            => __( '%qty% tags are available, use up and down arrow keys to navigate.', 'wcvendors-pro' ),
						'i18n_selection_too_long_1' => __( 'You can only select 1 tag', 'wcvendors-pro' ),
						'i18n_selection_too_long_n' => __( 'You can only select %qty% tags', 'wcvendors-pro' ),
						'nonce'                     => wp_create_nonce( 'wcv-search-product-tags' ),
						'separator'                 => apply_filters( 'wcv_tag_search_args_separator', $this->select2_separator( $tag_separator ) ),
					)
				);
				wp_register_script( 'wcv-tag-search', $this->base_dir . 'assets/js/tags' . $this->suffix . '.js', array( 'jquery', 'select2' ), WCV_PRO_VERSION, true );
				wp_localize_script( 'wcv-tag-search', 'wcv_tag_search_params', $localize_tag_search_args );
				wp_enqueue_script( 'wcv-tag-search' );

				// Product Edit.
				$product_params = array(
					'ajax_url'                       => admin_url( 'admin-ajax.php' ),
					'assets_url'                     => WCV_PRO_PUBLIC_ASSETS_URL,
					'product_types'                  => array_map(
						'sanitize_title',
						get_terms(
							'product_type',
							array(
								'hide_empty' => false,
								'fields'     => 'names',
							)
						)
					),
					'wcv_add_attribute_nonce'        => wp_create_nonce( 'wcv-add-attribute' ),
					'wcv_add_new_attribute_nonce'    => wp_create_nonce( 'wcv-add-new-attribute' ),
					'remove_attribute'               => __( 'Remove this attribute?', 'wcvendors-pro' ),
					'name_label'                     => __( 'Name', 'wcvendors-pro' ),
					'remove_label'                   => __( 'Remove', 'wcvendors-pro' ),
					'click_to_toggle'                => __( 'Click to toggle', 'wcvendors-pro' ),
					'values_label'                   => __( 'Value(s)', 'wcvendors-pro' ),
					'text_attribute_tip'             => __( 'Enter some text, or some attributes by pipe (|) separating values.', 'wcvendors-pro' ),
					'visible_label'                  => __( 'Visible on the product page', 'wcvendors-pro' ),
					'used_for_variations_label'      => __( 'Used for variations', 'wcvendors-pro' ),
					'new_attribute_prompt'           => __( 'Enter a name for the new attribute term:', 'wcvendors-pro' ),
					'wc_deliminator'                 => WC_DELIMITER,
					'wcv_file_display'               => $file_display,
					'category_limit'                 => $category_limit,
					'tag_limit'                      => $tag_limit,
					'category_limit_msg'             => apply_filters( 'wcv_category_limit_msg', sprintf( __( 'You can only select %s categories', 'wcvendors-pro' ), $category_limit ) ),
					'require_featured_image'         => wc_string_to_bool( get_option( 'wcvendors_required_product_media_featured', 'no' ) ),
					'require_featured_image_msg'     => apply_filters( 'wcv_require_featured_image_msg', __( 'Featured image is required.', 'wcvendors-pro' ) ),
					'require_gallery_image'          => wc_string_to_bool( get_option( 'wcvendors_required_product_media_gallery', 'no' ) ),
					'require_gallery_image_msg'      => apply_filters( 'wcv_require_featured_image_msg', __( 'A gallery image is required.', 'wcvendors-pro' ) ),
					'require_category'               => wc_string_to_bool( get_option( 'wcvendors_required_product_basic_categories', 'no' ) ),
					'require_category_msg'           => apply_filters( 'wcv_require_category_msg', __( 'A category is required.', 'wcvendors-pro' ) ),
					'require_download_file'          => wc_string_to_bool( get_option( 'wcvendors_required_product_general_download_files', 'no' ) ),
					'require_download_file_msg'      => apply_filters( 'wcv_required_download_file_msg', __( 'A download file is required.', 'wcvendors-pro' ) ),
					'require_attributes'             => wc_string_to_bool( get_option( 'wcvendors_required_product_basic_attributes', 'no' ) ),
					'require_attributes_msg'         => apply_filters( 'wcv_require_attributes_msg', __( 'An attribute is required.', 'wcvendors-pro' ) ),
					'select2_errorLoading'           => __( 'The results could not be loaded.', 'wcvendors-pro' ),
					'select2_loadingMore'            => __( 'Loading more results…', 'wcvendors-pro' ),
					'select2_maximumSelected_single' => sprintf( __( 'You can only select %s category', 'wcvendors-pro' ), $category_limit ),
					'select2_maximumSelected_plural' => sprintf( __( 'You can only select %s categories', 'wcvendors-pro' ), $category_limit ),
					'select2_noResults'              => __( 'No results found', 'wcvendors-pro' ),
					'select2_searching'              => __( 'Searching…', 'wcvendors-pro' ),
					'select2_removeAllItems'         => __( 'Remove all items', 'wcvendors-pro' ),
				);

				wp_register_script( 'wcv-frontend-product', $this->base_dir . 'assets/js/product' . $this->suffix . '.js', array( 'jquery-ui-core', 'select2' ), WCV_PRO_VERSION, true );
				wp_localize_script( 'wcv-frontend-product', 'wcv_frontend_product', $product_params );
				wp_enqueue_script( 'wcv-frontend-product' );

				// Product Variation.
				$product_variation_params = array(
					'ajax_url'                            => admin_url( 'admin-ajax.php' ),
					'wcv_add_variation_nonce'             => wp_create_nonce( 'wcv-add-variation' ),
					'wcv_link_variation_nonce'            => wp_create_nonce( 'wcv-link-variations' ),
					'wcv_delete_variations_nonce'         => wp_create_nonce( 'wcv-delete-variations' ),
					'wcv_json_link_all_variations_nonce'  => wp_create_nonce( 'wcv-link-all-variations' ),
					'wcv_load_variations_nonce'           => wp_create_nonce( 'wcv-load-variations' ),
					'wcv_bulk_edit_variations_nonce'      => wp_create_nonce( 'wcv-bulk-edit-variations' ),
					'wcv_woocommerce_placeholder_img_src' => wc_placeholder_img_src(),
					'wc_deliminator'                      => WC_DELIMITER,
					'i18n_link_all_variations'            => esc_js( __( 'Are you sure you want to link all variations? This will create a new variation for each and every possible combination of variation attributes (max 50 per run).', 'wcvendors-pro' ) ),
					'i18n_enter_a_value'                  => esc_js( __( 'Enter a value', 'wcvendors-pro' ) ),
					'i18n_enter_menu_order'               => esc_js( __( 'Variation menu order (determines position in the list of variations)', 'wcvendors-pro' ) ),
					'i18n_enter_a_value_fixed_or_percent' => esc_js( __( 'Enter a value (fixed or %)', 'wcvendors-pro' ) ),
					'i18n_delete_all_variations'          => esc_js( __( 'Are you sure you want to delete all variations? This cannot be undone.', 'wcvendors-pro' ) ),
					'i18n_last_warning'                   => esc_js( __( 'Last warning, are you sure?', 'wcvendors-pro' ) ),
					'i18n_choose_image'                   => esc_js( __( 'Choose an image', 'wcvendors-pro' ) ),
					'i18n_set_image'                      => esc_js( __( 'Set variation image', 'wcvendors-pro' ) ),
					'i18n_variation_added'                => esc_js( __( 'variation added', 'wcvendors-pro' ) ),
					'i18n_variations_added'               => esc_js( __( 'variations added', 'wcvendors-pro' ) ),
					'i18n_no_variations_added'            => esc_js( __( 'No variations added', 'wcvendors-pro' ) ),
					'i18n_remove_variation'               => esc_js( __( 'Are you sure you want to remove this variation?', 'wcvendors-pro' ) ),
					'i18n_scheduled_sale_start'           => esc_js( __( 'Sale start date (YYYY-MM-DD format or leave blank)', 'wcvendors-pro' ) ),
					'i18n_scheduled_sale_end'             => esc_js( __( 'Sale end date (YYYY-MM-DD format or leave blank)', 'wcvendors-pro' ) ),
					'i18n_edited_variations'              => esc_js( __( 'Save changes before changing page?', 'wcvendors-pro' ) ),
					'i18n_variation_count_single'         => esc_js( __( '%qty% variation', 'wcvendors-pro' ) ),
					'i18n_variation_count_plural'         => esc_js( __( '%qty% variations', 'wcvendors-pro' ) ),
					'i18n_any_label'                      => esc_js( __( 'Any', 'wcvendors-pro' ) ),
					'variations_per_page'                 => absint( apply_filters( 'woocommerce_admin_meta_boxes_variations_per_page', 15 ) ),
					'variation_actions_placeholder'       => esc_js( __( 'Select action', 'wcvendors-pro' ) ),
				);

				wp_register_script(
					'wcv-frontend-product-variation',
					$this->base_dir . 'assets/js/product-variation' . $this->suffix . '.js',
					array(
						'jquery',
						'jquery-ui-core',
						'accounting',
						'select2',
					),
					WCV_PRO_VERSION,
					true
				);
				wp_localize_script( 'wcv-frontend-product-variation', 'wcv_frontend_product_variation', $product_variation_params );
				wp_enqueue_script( 'wcv-frontend-product-variation' );

				// Order.
				wp_register_script( 'wcv-frontend-order', $this->base_dir . 'assets/js/order' . $this->suffix . '.js', array( 'jquery' ), WCV_PRO_VERSION, true );
				wp_enqueue_script( 'wcv-frontend-order' );

				wp_localize_script(
					'wcv-frontend-order',
					'wcv_pro_order',
					array(
						'confirm_shipped' => __( 'Are you sure the item was shipped?', 'wcvendors-pro' ),
					)
				);

				$decimal_separator  = wc_get_price_decimal_separator();
				$thousand_separator = wc_get_price_thousand_separator();

				if ( ! $thousand_separator ) {
					if ( '.' !== $decimal_separator ) {
						$thousand_separator = '.';
					} else {
						$thousand_separator = ',';
					}
				}

				$general_settings_params = array(
					'date_format'                      => apply_filters( 'wcv-datepicker-dateformat', 'Y-m-d' ),
					'ajax_url'                         => admin_url( 'admin-ajax.php' ),
					'wcv_json_unique_store_name_nonce' => wp_create_nonce( 'wcv-unique-store-name' ),
					'use_location_picker_text'         => apply_filters( 'wcv_use_location_picker_text', __( 'Show map', 'wcvendors-pro' ) ),
					'hide_location_picker_text'        => apply_filters( 'wcv_hide_location_picker_text', __( 'Hide map', 'wcvendors-pro' ) ),
					'cannot_find_address_text'         => apply_filters( 'wcv_cannot_find_address_test', __( 'Cannot determine address at this location.', 'wcvenodrs-pro' ) ),
					'map_zoom_level'                   => apply_filters( 'wcv_google_maps_zoom_level', get_option( 'wcvendors_pro_google_maps_zoom_level', '' ) ),
					'decimal_separator'                => $decimal_separator,
					'thousand_separator'               => $thousand_separator,
					'digits_after_decimal'             => wc_get_price_decimals(),
					'invalid_number_format'            => __( 'This value should be a valid number.', 'wcvendors-pro' ),
					'invalid_price_format'             => __( 'This value should be a valid price.', 'wcvendors-pro' ),
					'required_file_msg'                => __( 'This field is required. Please upload or choose a file.', 'wcvendors-pro' ),
				);

				// Country select.
				$country_select_args = array(
					'countries'                 => json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
					'i18n_select_state_text'    => esc_attr__( 'Select an option&hellip;', 'wcvendors-pro' ),
					'i18n_matches_1'            => _x( 'One country is available, press enter to select it.', 'enhanced select', 'wcvendors-pro' ),
					'i18n_matches_n'            => _x( '%qty% countries are available, use up and down arrow keys to navigate.', 'enhanced select', 'wcvendors-pro' ),
					'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'wcvendors-pro' ),
					'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'wcvendors-pro' ),
					'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'wcvendors-pro' ),
					'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'wcvendors-pro' ),
					'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'wcvendors-pro' ),
					'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'wcvendors-pro' ),
					'i18n_selection_too_long_1' => _x( 'You can only select 1 country', 'enhanced select', 'wcvendors-pro' ),
					'i18n_selection_too_long_n' => _x( 'You can only select %qty% countries', 'enhanced select', 'wcvendors-pro' ),
					'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'wcvendors-pro' ),
					'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'wcvendors-pro' ),
				);

				wp_register_script( 'wcv-country-select', $this->base_dir . '../includes/assets/js/country-select' . $this->suffix . '.js', array( 'jquery', 'select2' ), WCV_PRO_VERSION, true );
				wp_localize_script( 'wcv-country-select', 'wcv_country_select_params', $country_select_args );
				wp_enqueue_script( 'wcv-country-select' );

				$dashboard_args = array(
					'shipping_settings'   => $shipping_settings,
					'store_shipping_type' => $store_shipping_type,
					'vendor_select'       => $vendor_select,
					'shipping_type'       => $shipping_type,
				);

				// Dashboard forms.
				wp_register_script(
					'wcv-frontend-forms',
					$this->base_dir . 'assets/js/forms' . $this->suffix . '.js',
					array(
						'jquery',
						'select2',
					),
					WCV_PRO_VERSION,
					true
				);
				wp_localize_script( 'wcv-frontend-forms', 'wcv_fronted_forms', $dashboard_args );
				wp_enqueue_script( 'wcv-frontend-forms' );

				wp_register_script(
					'wcv-frontend-forms-country-states',
					$this->base_dir . 'assets/js/country-states' . $this->suffix . '.js',
					array(
						'jquery',
						'select2',
					),
					WCV_PRO_VERSION,
					true
				);
				wp_enqueue_script( 'wcv-frontend-forms-country-states' );
				wp_localize_script(
					'wcv-frontend-forms-country-states',
					'wcv_countries_states',
					array(
						'countries'              => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
						'i18n_select_state_text' => esc_attr__( 'Select an option&hellip;', 'wc-vendors-pro' ),
					)
				);

				// General settings.
				wp_register_script(
					'wcv-frontend-general',
					$this->base_dir . 'assets/js/general' . $this->suffix . '.js',
					array(
						'jquery',
						'select2',
					),
					WCV_PRO_VERSION,
					true
				);
				wp_localize_script( 'wcv-frontend-general', 'wcv_frontend_general', $general_settings_params );
				wp_enqueue_script( 'wcv-frontend-general' );

				// Jquery-ui datepicker.
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_script( 'jquery-ui-slider' );

				// Load datepicker flatpickr.
				wp_enqueue_script( 'wcv-timepicker', $this->base_dir . 'assets/lib/flatpickr/flatpickr.js', array(), WCV_PRO_VERSION );

				$current_page = get_query_var( 'object' );

				$google_map_api_key = apply_filters( 'wcvendors_pro_google_maps_api_key', get_option( 'wcvendors_pro_google_maps_api_key', '' ) );
				$map_zoom_level     = get_option( 'wcvendors_pro_google_maps_zoom_level', '' );
				$key_exists         = empty( $google_map_api_key ) ? false : true;

				if ( $key_exists && ! empty( $map_zoom_level ) && ( $current_page == 'settings' || ( isset( $_GET['terms'] ) && $_GET['terms'] == 1 ) ) ) {

					wp_enqueue_script(
						'google-maps-api',
						esc_url(
							add_query_arg(
								array(
									'sensor'    => false,
									'key'       => $google_map_api_key,
									'libraries' => 'places',
								),
								'//maps.googleapis.com/maps/api/js'
							)
						),
						null,
						null,
						false
					);

					wp_enqueue_script(
						'wcv-maps-address-picker',
						$this->base_dir . 'assets/js/maps-address-picker' . $this->suffix . '.js',
						array( 'jquery', 'select2' ),
						null,
						true
					);
				}
			}  // user logged in check

		} // on dashboard page

		if ( wcv_is_dashboard_page( $current_page_id ) || $current_page_id == $feedback_page_id ) {
			// Parsley JS - http://parsleyjs.org/.
			wp_register_script( 'parsley', $this->base_dir . 'assets/lib/parsley/parsley' . $this->suffix . '.js', array( 'jquery' ), '2.8.1', true );
			wp_enqueue_script( 'parsley' );

			if ( $current_page_id == $feedback_page_id ) {
				wp_register_script( 'feedback', $this->base_dir . 'assets/js/feedback' . $this->suffix . '.js', array( 'jquery' ), WCV_PRO_VERSION, true );
				wp_enqueue_script( 'feedback' );

				$wcv_feedback_args = apply_filters(
					'wcv_frontend_feedback_strings',
					array(
						'select_stars_message' => __( 'Please select your star rating.', 'wcvendors-pro' ),
					)
					);
				wp_localize_script( 'feedback', 'wcv_frontend_feedback', $wcv_feedback_args );
			}
		}

	}

	/**
	 * Select 2 seperator options for tag search
	 *
	 * @since  1.3.6
	 * @access public
	 * @return array separator types
	 * @param  string $option sprarator option.
	 */
	public function select2_separator( $option ) {

		switch ( $option ) {
			case 'space':
				return array( ' ' );
				break;
			case 'comma':
				return array( ',' );
				break;
			default:
				return apply_filters( 'wcv_tag_separator_defaults', array( ',', ' ' ) );
				break;
		}

	} // select2_separator()

	/**
	 * Change the post title to the specified SEO Title for the product
	 *
	 * @param string $title   title of the product.
	 * @param int    $post_id product id.
	 *
	 * @return string $title
	 * @since 1.5.8
	 */
	public function seo_title( $title, $post_id ) {
		global $post;

		$hide_seo = wc_string_to_bool( get_option( 'wcvendors_hide_product_seo', 'no' ) );

		if ( ! $hide_seo ) {
			$title = get_post_meta( $post_id, 'wcv_product_seo_title', true );

			if ( false === $title ) {
				$product = wc_get_product( $post );
				$title   = $product->get_name();
			}

			return $title;
		}

		return $title;
	} // seo_title()

	/**
	 * Output SEO & OpenGraph meta tags
	 *
	 * @return    void
	 * @since      1.5.8
	 * @version    1.5.9
	 */
	public function product_seo_meta() {

		global $post;

		if ( is_archive() ) {
			return;
		}

		$hide_seo = wc_string_to_bool( get_option( 'wcvendors_hide_product_seo', 'no' ) );

		if ( ! is_a( $post, 'WP_Post' ) ) {
			return;
		}

		if ( ! $hide_seo ) {

			$product = wc_get_product( $post );

			if ( ! is_a( $product, 'WC_Product' ) ) {
				return;
			}

			$product_id = $product->get_id();

			$seo_title       = get_post_meta( $product_id, 'wcv_product_seo_title', true );
			$seo_description = get_post_meta( $product_id, 'wcv_product_seo_description', true );
			$seo_keywords    = get_post_meta( $product_id, 'wcv_product_seo_keywords', true );
			$seo_image_url   = get_the_post_thumbnail_url( $post, 'large' );

			$seo_opengraph    = get_post_meta( $product_id, 'wcv_product_seo_opengraph', true );
			$seo_twitter_card = get_post_meta( $product_id, 'wcv_product_seo_twitter_card', true );

			$seo_store_name     = get_user_meta( $post->post_author, 'pv_shop_name', true );
			$seo_store_url      = WCV_Vendors::is_vendor( $post->post_author ) ? WCV_Vendors::get_vendor_shop_page( $post->post_author ) : '';
			$seo_twitter_author = get_user_meta( $post->post_author, '_wcv_twitter_username', true );

			// use categories for keywords if none are defined
			if ( '' == $seo_keywords ) {
				$categories   = get_the_term_list( $product_id, 'product_cat', '', ',', '' );
				$seo_keywords = wcv_strip_html( $categories );
			}

			$seo_title       = ! empty( $seo_title ) ? $seo_title : $product->get_name();
			$seo_description = ! empty( $seo_description ) ? substr( $product->get_description(), 0, apply_filters( 'wcv_seo_description_length', 155 ) ) : $seo_description;

			$seo_product_price   = $product->get_price();
			$seo_product_amount  = $product->get_price();
			$seo_currency_code   = get_woocommerce_currency();
			$seo_currency_symbol = get_woocommerce_currency_symbol( $seo_currency_code );

			wc_get_template(
				'product-seo-meta.php',
				array(
					'product_id'          => $product_id,
					'seo_title'           => wcv_strip_html( $seo_title ),
					'seo_description'     => wcv_strip_html( $seo_description ),
					'seo_keywords'        => wcv_strip_html( $seo_keywords ),
					'seo_image_url'       => $seo_image_url,
					'seo_product_price'   => $seo_product_price,
					'seo_product_amount'  => $seo_product_amount,
					'seo_currency_code'   => $seo_currency_code,
					'seo_currency_symbol' => $seo_currency_symbol,
					'seo_store_name'      => $seo_store_name,
					'seo_twitter_author'  => $seo_twitter_author,
					'seo_opengraph'       => $seo_opengraph,
					'seo_twitter_card'    => $seo_twitter_card,
					'seo_store_url'       => $seo_store_url,
				),
				'wc-vendors/product/',
				WCV_PRO_ABSPATH_TEMPLATES . 'product/'
			);
		}
	}

	/**
	 * Preview for file_uploder
	 */
	public static function file_uploader_preview() {

		$file_url = $_POST['file_url'];
		global $wp_embed;
		echo( do_shortcode( $wp_embed->run_shortcode( '[embed]' . esc_url( $file_url ) . '[/embed]' ) ) );
		wp_die();
	}


	/**
	 * Load theme support automatically
	 *
	 * @since 1.6.0
	 * @version 1.7.9
	 */
	public function load_theme_support() {

		$theme = wp_get_theme();

		switch ( $theme->template ) {
			case 'Divi':
				include_once 'theme-support/class-divi.php';
				break;
			case 'storefront':
				include_once 'theme-support/class-storefront.php';
				break;
			case 'my-listing':
				include_once 'theme-support/class-mylisting.php';
				break;
			case 'astra':
				include_once 'theme-support/class-astra.php';
				break;
			case 'generatepress':
				include_once 'theme-support/class-generatepress.php';
				break;
			default:
				// Allow hook into the theme support.
				// @todo fix this filter, it isn't accesible from functions.php - jamie.
				$theme_support_file = apply_filters( 'wcv_load_theme_support', '' );
				if ( $theme_support_file ) {
					include $theme_support_file;
				}
				break;
		}

	}

	/**
	 * Policy tab
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function product_policy_tab( $tabs ) {

		global $post;
		$vendor_id = $post->post_author;

		if ( ! WCV_vendors::is_vendor( $vendor_id ) ) {
			return $tabs;
		}

		if ( wc_string_to_bool( get_option( 'wcvendors_hide_settings_tab_policies', 'no' ) ) ) {
			return $tabs;
		}

		if (
			wc_string_to_bool( get_option( 'wcvendors_hide_settings_store_policy_privacy', 'no' ) )
			&& wc_string_to_bool( get_option( 'wcvendors_hide_settings_store_policy_terms', 'no' ) )
			&& wc_string_to_bool( get_option( 'wcvendors_hide_settings_shipping_shipping_policy', 'no' ) )
			&& wc_string_to_bool( get_option( 'wcvendors_hide_settings_shipping_return_policy', 'no' ) )
		) {
			return $tabs;
		}

		$privacy               = trim( get_user_meta( $vendor_id, 'wcv_policy_privacy', true ) );
		$terms                 = trim( get_user_meta( $vendor_id, 'wcv_policy_terms', true ) );
		$shipping_detail       = get_user_meta( $vendor_id, '_wcv_shipping', true );
		$shipping              = '';
		$return                = '';
		$wcv_shipping_settings = get_option( 'woocommerce_wcv_pro_vendor_shipping_settings', wcv_get_default_vendor_shipping() );

		if ( wc_string_to_bool( $wcv_shipping_settings['enabled'] ) ) {
			$shipping = isset( $wcv_shipping_settings['shipping_policy'] ) ? $wcv_shipping_settings['shipping_policy'] : '';
			$return   = isset( $wcv_shipping_settings['return_policy'] ) ? $wcv_shipping_settings['return_policy'] : '';
		}

		if ( isset( $shipping_detail['shipping_policy'] ) && $shipping_detail['shipping_policy'] ) {
			$shipping = $shipping_detail['shipping_policy'];
		}
		if ( isset( $shipping_detail['return_policy'] ) && $shipping_detail['return_policy'] ) {
			$return = $shipping_detail['return_policy'];
		}

		if ( ! ( $privacy || $terms || $shipping || $return ) ) {
			return $tabs;
		}

		$tabs['policies'] = apply_filters(
			 'wcv_single_product_policies_tab',
			array(
				'title'    => sprintf( __( '%s Policies', 'wcvendors-pro' ), wcv_get_vendor_name() ),
				'priority' => 50,
				'callback' => array( $this, 'product_policy_tab_content' ),
			)
			);

		return $tabs;

	}

	/**
	 * Policy tab content
	 */
	public function product_policy_tab_content() {
		global $post;
		$vendor_id = $post->post_author;
		$policies  = array();

		$privacy = wpautop( trim( get_user_meta( $vendor_id, 'wcv_policy_privacy', true ) ) );
		if ( ! wc_string_to_bool( get_option( 'wcvendors_hide_settings_store_policy_privacy', 'no' ) ) && $privacy ) {
			$policies['privacy'] = array(
				'title'   => __( 'Privacy Policy', 'wcvendors-pro' ),
				'content' => $privacy,
			);
		}

		$terms = wpautop( trim( get_user_meta( $vendor_id, 'wcv_policy_terms', true ) ) );
		if ( ! wc_string_to_bool( get_option( 'wcvendors_hide_settings_store_policy_terms', 'no' ) ) && $terms ) {
			$policies['terms'] = array(
				'title'   => __( 'Terms and Conditions', 'wcvendors-pro' ),
				'content' => $terms,
			);
		}

		$shipping              = '';
		$return                = '';
		$shipping_detail       = get_user_meta( $vendor_id, '_wcv_shipping', true );
		$wcv_shipping_settings = get_option( 'woocommerce_wcv_pro_vendor_shipping_settings', wcv_get_default_vendor_shipping() );

		if ( wc_string_to_bool( $wcv_shipping_settings['enabled'] ) ) {
			$shipping = isset( $wcv_shipping_settings['shipping_policy'] ) ? $wcv_shipping_settings['shipping_policy'] : '';
			$return   = isset( $wcv_shipping_settings['return_policy'] ) ? $wcv_shipping_settings['return_policy'] : '';
		}

		if ( isset( $shipping_detail['shipping_policy'] ) && $shipping_detail['shipping_policy'] ) {
			$shipping = wpautop( $shipping_detail['shipping_policy'] );
		}
		if ( isset( $shipping_detail['return_policy'] ) && $shipping_detail['return_policy'] ) {
			$return = wpautop( $shipping_detail['return_policy'] );
		}
		if ( ! empty( $shipping_detail ) ) {
			if (
				! wc_string_to_bool( get_option( 'wcvendors_hide_settings_shipping_shipping_policy', 'no' ) )
				&& $shipping
			) {
				$policies['shipping'] = array(
					'title'   => __( 'Shipping Policy', 'wcvendors-pro' ),
					'content' => $shipping,
				);
			}

			if (
				! wc_string_to_bool( get_option( 'wcvendors_hide_settings_shipping_return_policy', 'no' ) )
				&& $return
			) {
				$policies['return'] = array(
					'title'   => __( 'Return Policy', 'wcvendors-pro' ),
					'content' => $return,
				);
			}
		}

		$policies = apply_filters( 'wcv_product_policy_tab_content_args', $policies, $vendor_id );

		foreach ( $policies as $id => $policy ) { ?>
			<div class="policy <?php echo esc_attr( $id ); ?>" id="policy-<?php echo esc_attr( $id ); ?>">
				<h3><?php echo esc_html( $policy['title'] ); ?></h3>
				<div class="policy-content">
					<?php echo wp_kses_post( $policy['content'] ); ?>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Filter the category list output.
	 *
	 * Change the url of each category in the list by replacing the shortened url with a full url.
	 *
	 * @param string $output The categories list html output to be changed.
	 * @param array  $args   The arguments used to search for the categories.
	 * @return string
	 * @version 1.7.3
	 * @since   1.7.3
	 */
	public function filter_categories_list_output( $output, $args ) {
		$vendor_id = wcv_get_vendor_id();

		if ( ! $vendor_id ) {
			return $output;
		}

		$vendor_shop_url = WCV_Vendors::get_vendor_shop_page( $vendor_id );

		if ( $vendor_shop_url ) {
			return str_replace(
				'href="?',
				apply_filters( 'wcvendors_categories_vendor_shop_url', 'href="' . esc_url_raw( $vendor_shop_url ) . '?' ),
				$output
			);
		}

		return $output;
	}
}
