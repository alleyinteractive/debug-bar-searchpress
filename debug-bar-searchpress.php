<?php
/*
	Plugin Name: Debug Bar SearchPress Add-on
	Plugin URI: http://www.alleyinteractive.com/
	Description: Simple debug-bar add-on for working with SearchPress
	Version: 0.1
	Author: Matthew Boynes
	Author URI: http://www.alleyinteractive.com/
*/
/*  This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


if ( !class_exists( 'Debug_Bar_SearchPress' ) ) :

class Debug_Bar_SearchPress {

	private static $instance;

	public $content;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}

	public function __clone() { wp_die( "Please don't __clone Debug_Bar_SearchPress" ); }

	public function __wakeup() { wp_die( "Please don't __wakeup Debug_Bar_SearchPress" ); }

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Debug_Bar_SearchPress;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		$this->content = array(
			'es_wp_query_args'   => array(),
			'es_query_args'      => array(),
			'sp_rp_args'           => array(),
			'response'           => array()
		);
		add_filter( 'debug_bar_panels',          array( $this, 'add_panels' )                );
		add_filter( 'sp_search_wp_query_args',   array( $this, 'es_wp_query_args' ),  99     );
		add_filter( 'sp_search_query_args',      array( $this, 'es_query_args' ),     99     );
		add_filter( 'sp_related_posts_args',     array( $this, 'sp_rp_args' ),          99     );
		add_filter( 'http_api_debug',            array( $this, 'post_request' ),      10, 5  );
		add_action( 'debug_bar_enqueue_scripts', array( $this, 'static_files' )              );
	}

	public function static_files() {
		wp_enqueue_style( 'debug-bar-searchpress', plugins_url( "css/debug-bar-searchpress.css", __FILE__ ), array(), '1.0.1' );
	}

	public function add_panels( $panels ) {
		require_once( 'class-debug-bar-searchpress-panel.php' );
		$panels[] = new Debug_Bar_SearchPress_Panel();
		return $panels;
	}

	public function es_wp_query_args( $args ) {
		$count = count( $this->content['es_wp_query_args'] ) + 1;
		$this->content['es_wp_query_args'][] = "
		<h4>WordPress Args Request #{$count}</h4>
		<pre>" . print_r( $args, 1 ) . "</pre>
		";
		return $args;
	}

	public function es_query_args( $args ) {
		$count = count( $this->content['es_query_args'] ) + 1;
		$this->content['es_query_args'][] = "
		<h4>ES Args #{$count}</h4>
		<dl>
			<dt>PHP</dt>
				<dd><pre>" . print_r( $args, 1 ) . "</pre></dd>
			<dt>JSON</dt>
				<dd><pre>" . json_encode( $args ) . "</pre></dd>
		</dl>
		";
		return $args;
	}

	public function sp_rp_args( $args ) {
		$count = count( $this->content['sp_rp_args'] ) + 1;
		$this->content['sp_rp_args'][] = "
		<h4>Related Posts Args #{$count}</h4>
		<dl>
			<dt>PHP</dt>
				<dd><pre>" . print_r( $args, 1 ) . "</pre></dd>
			<dt>JSON</dt>
				<dd><pre>" . json_encode( $args ) . "</pre></dd>
		</dl>
		";
		return $args;
	}

	public function post_request( $response, $type, $class, $args, $url ) {
		// Account for ES 6.0 and 7.0 doc types.
		if ( preg_match( "#/(post|_doc)/_search#i", $url ) ) {
			$body = wp_remote_retrieve_body( $response );
			$count = count( $this->content['response'] ) + 1;
			$this->content['response'][] = "
			<h4>HTTP Request #{$count}</h4>
			<dl>
				<dt>URL</dt>
					<dd>{$url}</dd>
				<dt>Request Body</dt>
					<dd><pre>{$args['body']}</pre></dd>
				<dt>Response (full)</dt>
					<dd><pre>" . print_r( $response, 1 ) . "</pre></dd>
				<dt>Response Body (raw)</dt>
					<dd><pre>{$body}</pre></dd>
				<dt>Response Body (decoded)</dt>
					<dd><pre>" . print_r( json_decode( $body ), 1 ) . "</pre></dd>
			</dl>
			";
		}
		return $response;
	}

	public function listify( $array ) {
		if ( !empty( $array ) )
			return "
			<ol class='searchpress-debug-list'>
				<li class='searchpress-debug-list-item'>
					" . implode( "</li>\n\t\t\t\t<li class='searchpress-debug-list-item'>", $array ) . "
				</li>
			</ol>
			";
		else
			return "<p>None found</p>";
	}
}

function Debug_Bar_SearchPress() {
	return Debug_Bar_SearchPress::instance();
}
add_action( 'plugins_loaded', 'Debug_Bar_SearchPress' );

endif;
