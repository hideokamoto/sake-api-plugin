<?php

class Recruite_admin {
	private static $instance;
	private $update_key = '';
  private $option_name = '';
  private $options = [];
	public function __construct( $key, $option_name, $options ) {
		$this->update_key = $key;
    $this->option_name = $option_name;
    $this->options = $options;
	}

	/**
	 * Get Instance Class
	 *
	 * @return Recruite_admin
	 * @since 4.0.0
	 * @access public
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}

	public function define_menus() {
		add_menu_page(
			__( 'リクルート API 設定', $this->option_name ),
			__( 'リクルート API 設定', $this->option_name ),
			'administrator',
			$this->option_name,
			array( $this, 'init_panel' )
		);
	}

  private function option_keys() {
    return $this->options;
  }

	public function init_panel() {
		$html	= "<div class='wrap' id=$this->option_name>";
		$html  = '';
		$html .= '<h3>'. __( 'General Settings', $this->option_name ). '</h3>';
		$html .= "<form method='post' action='' >";
		$html .= "<table class='widefat form-table'><tbody>";
    $keys = $this->option_keys();
    $options = get_option($this->option_name);
    foreach ( $this->options as $key ) {
      if ( ! is_array( $options ) || ! isset( $options[$key] ) ) {
        $value = '';
      } else {
        $value = esc_attr( $options[$key] );
      }
      $input = "<input name='{$key}' type='text' id='{$key}' value='{$value}' class='regular-text code' / >";
			$html .= '<tr>';
			$html .= '<th>　'. esc_html( $key ). '</th>';
			$html .= "<td>{$input}</td>";
			$html .= '</tr>';
    }
		$html .= '</tbody></table>';
		$html .= get_submit_button( __( 'Save Change' , $this->option_name ) , 'primary large' );
		$html .= wp_nonce_field( $this->update_key , $this->update_key , true , false );
		$html .= '</form>';
		$html .= '</div>';
		echo $html;
	}
}
