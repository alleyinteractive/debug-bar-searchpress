<?php

class Debug_Bar_SearchPress_Panel extends Debug_Bar_Panel {

	public function init() {
		$this->title( __( 'Elasticsearch', 'debug-bar-searchpress' ) );
	}

	public function prerender() {
		$this->set_visible( true );
	}

	public function render() {
		?>
		<div id="debug-bar-searchpress">
			<h3>es_wp_query_args</h3>
			<?php echo Debug_Bar_SearchPress()->listify( Debug_Bar_SearchPress()->content['es_wp_query_args'] ) ?>

			<h3>es_query_args</h3>
			<?php echo Debug_Bar_SearchPress()->listify( Debug_Bar_SearchPress()->content['es_query_args'] ) ?>

			<h3>wrp_args</h3>
			<?php echo Debug_Bar_SearchPress()->listify( Debug_Bar_SearchPress()->content['wrp_args'] ) ?>

			<h3><?php _e( 'HTTP Endpoint Requests', 'debug-bar-searchpress' ); ?></h3>
			<?php echo Debug_Bar_SearchPress()->listify( Debug_Bar_SearchPress()->content['response'] ) ?>
		</div>
		<?php
	}
}
