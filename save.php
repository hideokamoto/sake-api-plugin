<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class Recruite_save {
private $update_key = '';
private $option_name = '';
private $options = [];
public function __construct( $key, $option_name, $options ) {
  $this->update_key = $key;
  $this->option_name = $option_name;
  $this->options = $options;
}

	public function update_settings() {
		if ( empty( $_POST ) ) {
			return;
		}
		if ( isset( $_POST[ $this->update_key ] ) && $_POST[ $this->update_key ] ) {
			if ( check_admin_referer( $this->update_key, $this->update_key ) ) {
			$this->_update_option();
			}
		}
	}

  private function _update_option() {
    $options = [];
    foreach ( $this->options as $option ) {
      $options[$option] = esc_attr( $_POST[$option] );
    }
    update_option( $this->option_name, $options);
  }
}
