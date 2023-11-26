<?php
/**
 * Drop-in Name: Scripts Manager
 * Description: Add custom scripts to the body and footer of your pages. Offers sitewide scripts and the ability to adds scripts to specific posts and pages from the post editor.
 * Author: Alex, Kolakube
 * AuthorURI: https://marketersdelight.com/
 * DropinURI: https://marketersdelight.com/dropins/
 * Slug: scripts
 * Version: 1.1
 * @since MD4.4.2
 */

class md_scripts extends md_api {

	/**
	 * Run actions and filters.
	 *
	 * @since 4.4.2
	 */

	public function actions() {
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_scripts' ) );
		add_filter( 'body_class', array( $this, 'body_class' ) );
	}

	/**
	 * Register various admin settings.
	 *
	 * @since 5.0
	 */

	public function register() {
		$this->name = __( 'Scripts', 'md' );
		$fields = $this->fields();

		return array(
			'meta_box' => array(
				'name' => $this->name,
				'page_settings' => true,
				'fields' => $fields
			),
			'term' => array(
				'name' => $this->name,
				'fields' => $fields,
				'callback' => array( $this, 'admin_fields' )
			)
		);
	}

	/**
	 * Register settings to save.
	 *
	 * @since 4.4.2
	 */

	public function fields() {
		$scripts = $this->dequeue_data( 'ids' );
		$save = array(
			'body_class' => array( 'type' => 'text' ),
			'header_scripts' => array( 'type' => 'code' ),
			'footer_scripts' => array( 'type' => 'code' )
		);

		if ( ! empty( $scripts ) )
			$save['scripts_manager'] = array(
				'type' => 'checkbox',
				'options' => $scripts
			);

		return $save;
	}

	/**
 	 * Filter body classes.
 	 *
 	 * @since 4.1
	 * @moved 5.6
 	*/

	public function body_class( $classes ) {
		// Add custom body classes
		$custom = md_module( array( 'scripts', 'body_class' ) );

		if ( $custom ) {
			$custom = explode( ' ' , $custom );
			foreach ( $custom as $class )
				$classes[] = esc_attr( $class );
		}

		// Remove excess WP classes
		$classes = array_diff( $classes, array(
			'single-format-standard',
			'single-format-' . get_post_format()
		) );

		return $classes;
	}

	/**
	 * Add settings template and script to Page Settings sections.
	 *
	 * @since 5.6
	 */

	public function admin_fields() { ?>
		<div class="md-widget md-toggle md-sep-small">
			<h3 class="md-widget-title"><?php echo esc_html( $this->name ); ?></h3>
			<div class="md-widget-item">
				<?php $this->admin_template(); ?>
			</div>
		</div>
	<?php }

	/**
	 * Meta box fields.
	 *
	 * @since 5.0
	 */

	public function meta_box() {
		echo "<div class=\"md-$this->_clean_id md-tab-content\">";
		$this->admin_template();
		echo '</div>';
	}

	/**
	 * Admin page content.
	 *
	 * @since 4.4.2
	 */

	public function admin_template() {
		$scripts = $this->dequeue_data( 'labels' );
		$screen = get_current_screen();
	?>

		<?php if ( $screen->base !== 'toplevel_page_md_settings' ) : ?>
			<div class="md-sep-small">
				<?php $this->fields->field( 'body_class', array(
					'type' => 'text',
					'label' => __( 'Body classes', 'md' ),
					'description' => __( 'Add custom CSS classes to the <code>body</code> tag of this page.', 'md' )
				) ); ?>
			</div>
		<?php endif; ?>
		<div class="md-sep-small">
			<?php $this->fields->field( 'header_scripts', array(
				'type' => 'code',
				'label' => __( 'Header Scripts', 'md' ),
				'description' => __( 'Print scripts to the <code>&lt;head></code> section (before opening <code>&lt;body></code> tag).', 'md' )
			) ); ?>
		</div>
		<div class="md-sep-small">
			<?php $this->fields->field( 'footer_scripts', array(
				'type' => 'code',
				'label' => __( 'Footer Scripts', 'md' ),
				'description' => __( 'Print scripts after the <code>&lt;footer></code> section (before closing <code>&lt;/body></code> tag).', 'md' )
			) ); ?>
		</div>
		<?php if ( $screen->base !== 'toplevel_page_md_settings' && ! empty( $scripts ) ) : ?>
			<div class="md-sep-small">
				<?php $this->fields->field( 'scripts_manager', array(
					'type' => 'checkbox',
					'label' => __( 'Scripts Optimization', 'md' ),
					'description' => __( '<b>Note:</b> Only remove the scripts and styles you know this page does not use, otherwise you may break some plugin functionality.', 'md' ),
					'options' => $scripts
				) ); ?>
			</div>
		<?php endif; ?>

	<?php }

	/**
	 * Run dequeue actions on specified pages.
	 *
	 * @since 4.9.4
	 */

	public function dequeue_scripts() {
		$scripts = $this->dequeue_data();

		if ( ! empty( $scripts ) ) {
			if ( is_singular() ) {
				$meta = md_post_meta( array( 'scripts', 'scripts_manager' ) );

				if ( ! empty( $meta ) )
					$this->dequeue_action( $meta );
			}
			if ( is_category() || is_tax() ) {
				$tax = md_term_meta( array( 'scripts', 'scripts_manager' ) );

				if ( ! empty( $tax ) )
					$this->dequeue_action( $tax );
			}
		}
	}

	/**
	 * Call this action to dequeue specified scripts and styles.
	 *
	 * @since 4.9.4
	 */

	public function dequeue_action( $source ) {
		foreach ( $this->dequeue_data() as $plugin => $assets )
			if ( ! empty( $source[$plugin] ) ) {
				if ( isset( $assets['styles'] ) )
					foreach ( $assets['styles'] as $style )
						wp_dequeue_style( $style );

				if ( isset( $assets['scripts'] ) )
					foreach ( $assets['scripts'] as $script )
						wp_dequeue_script( $script );
			}
	}

	/**
	 * Compile scripts data into different formats from filter.
	 *
	 * @since 4.4.2
	 */

	public function dequeue_data( $sort = null ) {
		$scripts = apply_filters( 'md_filter_dequeue_scripts', array() );

		if ( ! empty( $scripts ) )
			if ( $sort == 'ids' ) {
				$ids = array();

				foreach ( $scripts as $plugin => $assets )
					$ids[] = $plugin;

				return $ids;
			}
			elseif ( $sort == 'labels' ) {
				$labels = array();

				foreach ( $scripts as $plugin => $assets )
					$labels[$plugin] = $assets['label'];

				return $labels;
			}

		return $scripts;
	}

	/**
	 * Add scripts to header.
	 *
	 * @since 4.4.2
	 */

	public function wp_head() {
		$scripts = '';
		$key = array( 'scripts', 'header_scripts' );

		$scripts .= md_setting( array( 'settings', 'header_scripts' ) );

		if ( is_singular() )
			$scripts .= md_post_meta( $key );
		elseif ( is_post_type_archive() || is_home() )
			$scripts .= md_post_type_field( $key );
		elseif ( is_category() || is_tax() )
			$scripts .= md_term_meta( $key );

		if ( $scripts )
			echo html_entity_decode( $scripts );
	}

	/**
	 * Add scripts to footer.
	 *
	 * @since 4.4.2
	 */

	public function wp_footer() {
		$scripts = '';
		$key = array( 'scripts', 'footer_scripts' );
		$google = md_setting( array( 'integrations', 'api_keys', 'google_analytics', 'key' ) );

		if ( $google )
			$this->google_analytics();

		$scripts .= md_setting( array( 'settings', 'footer_scripts' ) );

		if ( is_singular() )
			$scripts .= md_post_meta( $key );
		elseif ( is_post_type_archive() || is_home() )
			$scripts .= md_post_type_field( $key );
		elseif ( is_category() || is_tax() )
			$scripts .= md_term_meta( $key );

		if ( $scripts )
			echo html_entity_decode( $scripts );
	}

	/**
	 * Google Analytics Tracking Code.
	 *
	 * @since 4.9
	 */

	public function google_analytics() { ?>
		<!-- Global Site Tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $this->google_analytics['key'] ); ?>"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());
			gtag('config', '<?php echo esc_attr( $this->google_analytics['key'] ); ?>');
		</script>
	<?php }

}

new md_scripts;