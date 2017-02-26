<?php
/**
 * @package Recruite_API
 * @version 1.0
 */
/*
Plugin Name: Recruite API
Plugin URI: https://wp-kyoto.net
Description: This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong: Hello, Dolly. When activated you will randomly see a lyric from <cite>Hello, Dolly</cite> in the upper right of your admin screen on every page.
Author: Hideokamoto
Version: 1.0
Author URI: https://wp-kyoto.net
*/

require_once( dirname( __FILE__ ).'/admin.php' );
require_once( dirname( __FILE__ ).'/save.php' );
add_shortcode ( 'shop-list' , 'get_shop_list');
$update_key = 'recruite-api-update';
$option_name = 'my-recruite-api';
$options = [
  'recruite_api_key',
  'vc_url'
];
$admin = new Recruite_admin( $update_key, $option_name, $options );
$save = new Recruite_save( $update_key, $option_name, $options );
add_action( 'admin_init', array( $save, 'update_settings' ) );
add_action( 'admin_menu', array( $admin, 'define_menus' ) );

function get_shop_list( $atts ) {
	// Attributes
	$atts = shortcode_atts(
		array(
			'area_code' => '',
      'area_type' => 'small',
			'curtome_api_query' => ''
		),
		$atts
	);

  $html = my_get_the_content( $atts['area_code'], $atts['curtome_api_query'], $atts['area_type'] );
    return $html;
}

function my_get_cta_link( $link, $device, $text ) {
	$html = "<a href=\"{$link}\" target=\"_blank\" class=\"btn btn-primary\" role=\"button\">{$text}</a> ";
	if ( 'mobile' === $device ) {
		$html .= "<a href=\"#\" class=\"btn btn-default\" role=\"button\">電話する</a> ";
	}
	return $html;
}

function my_area_type( $area_type, $area_code ) {
  $query = '';
  switch ( $area_type ) {
    case 'small':
      $query = "small_area={$area_code}";
      break;

    case 'middle':
      $query = "middle_area={$area_code}";
      break;

    case 'large':
      $query = "large_area={$area_code}";
      break;

    default:
      $query = "small_area={$area_code}";
      break;
  }
  return $query;
}

function my_get_the_content( $area_code, $curtome_api_query, $area_type ) {
    $options = get_option('my-recruite-api');
    $vc_url = $options['vc_url'];
    $key = $options['recruite_api_key'];
    if ( wp_is_mobile() ) {
        $device = 'mobile';
    } else {
        $device = 'pc';
    }
    $url = 'http://webservice.recruit.co.jp/hotpepper/gourmet/v1/?format=json&sake=1';
    $area_query = my_area_type( $area_type, $area_code );
    var_dump($area_query);
    $response = wp_remote_get("{$url}&key={$key}&{$area_query}&{$curtome_api_query}");

    $body = json_decode($response['body'],true);
    $count = $body['results']['results_available'];
    $html = "<p>{$count}件</p>";
    //$html .= "<table>";
    foreach ( $body['results']['shop'] as $id => $shop ) {
        $page = urlencode($shop['urls'][ $device ]);
        $link = "{$vc_url}{$page}";
        $html .= '<div class="row">';
        $html .= '<div class="col-sm-12 col-md-12">';
        $html .= '<div class="thumbnail clearfix">';
        $html .= "<div class=\" col-sm-4\">";
        $html .= "<img src=\"{$shop['photo'][ $device ]['l']}\" alt=\"{$shop['catch']}\">";
        $html .= '</div>';
        $html .= "<div class=\" col-sm-8\">";
        $html .= '<div class="caption">';
        $html .= "<p style=\"margin-bottom: 0em;margin-top:0;\"><small>{$shop['catch']}</small></p>";
        $html .= "<h3 style=\"margin-bottom: 0.5em;margin-top:0;\">{$shop['name']}</h3>";
        $html .= "<p style=\"margin-bottom: 0em;margin-top:0;\">";
        if ( '利用可' === $shop['card'] ) {
            $html .= "<span class=\"label label-default\"><i class=\"fa fa-credit-card\" aria-hidden=\"true\"></i> カードOK</span> ";
        }
        $html .= "<span class=\"label label-default\">禁煙席";
        if ( preg_match( '/なし/', $shop['non_smoking'] ) ) {
            $html .= "なし</span>";
        } else {
            $html .= "あり</span>";
        }
        $html .= '</p>';
        $html .= "<p><small>{$shop['shop_detail_memo']}</small></p>";

        $html .= '<p>';
        $html .= my_get_cta_link( $link, $device, '予約する' );
        $html .= "<a class=\"btn btn-primary\" role=\"button\" data-toggle=\"collapse\" href=\"#collapse{$id}\" aria-expanded=\"false\" aria-controls=\"collapse{$id}\">";
        $html .= "<i class=\"fa fa-info-circle\" aria-hidden=\"true\"></i> お店情報を見る";
        $html .= '</a>';
        $html .= '</p>';

        $html .= '</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= "<div class=\"clearfix col-sm-12\" class=\"collapse\" id=\"collapse{$id}\" style=\"height:0;overflow:hidden;\">";
        $html .= '<dl>';
        $html .= "<dt><i class=\"fa-calendar\" aria-hidden=\"true\"></i> 営業時間</dt>";
        $html .= "<dd>{$shop['open']}</dd>";
        $html .= "<dt><i class=\"fa fa-train\" aria-hidden=\"true\"></i> 最寄り駅</dt>";
        $html .= "<dd>{$shop['mobile_access']}</dd>";
        $html .= "<dt><i class=\"fa fa-map-marker\" aria-hidden=\"true\"></i> 住所</dt>";
        $html .= "<dd>{$shop['address']}</dd>";
        $html .= "<dt><i class=\"fa fa-jpy\" aria-hidden=\"true\"></i> 平均予算</dt>";
        $html .= "<dd>{$shop['budget']['average']}</dd>";

        $html .= "<dt><i class=\"fa fa-info-circle\" aria-hidden=\"true\"></i> 禁煙席</dt>";
        $html .= "<dd>{$shop['non_smoking']}</dd>";
        $html .= "<dt><i class=\"fa fa-info-circle\" aria-hidden=\"true\"></i> 個室</dt>";
        $html .= "<dd>{$shop['private_room']}</dd>";
        if ( '' !== $shop['other_memo'] ) {
            $html .= "<dt><i class=\"fa fa-info-circle\" aria-hidden=\"true\"></i> その他の情報</dt>";
            $html .= '<dd>';
            $html .= "<p><small>{$shop['other_memo']}</small></p>";
            $html .= '</dd>';
        }

        $html .= '</dl>';

        $html .= my_get_cta_link( $link, $device, 'もっと情報をみる（Hotpepper）' );
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    return $html;
}
