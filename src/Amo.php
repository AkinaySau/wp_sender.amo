<?php
/**
 * Created for amo (extend sau_sender).
 * User: AkinaySau akinaysau@gmail.ru
 * Date: 18.09.2017
 * Time: 11:30
 */

namespace Sau\WP\Plugin\Sender\Amo;

use function print_r;
use function sprintf;

class Amo {

	const OPTION_NAME_SUBDOMAIN = 'sau_amo_subdomain';
	const OPTION_NAME_LOGIN = 'sau_amo_login';
	const OPTION_NAME_HASH = 'sau_amo_hash';
	const OPTION_THEME_LANG = 'sau_amo';

	public static function init() {
		self::addTranslate();
		self::addOption();
		self::requestAmo();
	}

	/**
	 * Подключение перевода
	 */
	private static final function addTranslate() {
		add_action( 'init', function () {
			load_plugin_textdomain( self::OPTION_THEME_LANG, false, dirname( plugin_basename( __FILE__ ) ) . '/l10n' );
		} );
	}

	/**
	 * Вывод доп поля в админке для email
	 */
	private static final function addOption() {
		add_action( 'admin_menu', function () {
			register_setting( 'general', self::OPTION_NAME_HASH );
			register_setting( 'general', self::OPTION_NAME_LOGIN );
			register_setting( 'general', self::OPTION_NAME_SUBDOMAIN );

			add_settings_section( 'sau_amo', __( 'Connect to AMO', self::OPTION_THEME_LANG ), function () {
				echo '<p>', __( 'This is connect properties for AmoCRM', self::OPTION_THEME_LANG ), '</p>';
			}, 'general' );

			//todo: надо прибраться
			// добавляем поля
			add_settings_field( 'sau' . self::OPTION_NAME_SUBDOMAIN, __( 'Subdomain', self::OPTION_THEME_LANG ), function ( $val ) {
				$id = $val['id'];
				echo '<input class="regular-text ltr" type="text" name="' . self::OPTION_NAME_SUBDOMAIN . '" id="' . $id . '" value="' . esc_attr( get_option( self::OPTION_NAME_SUBDOMAIN ) ) . '"/><p class="description">' . __( 'May be test, test.amocrm.ru or test.amocrm.com', self::OPTION_THEME_LANG ) . '</p>';
			}, 'general', 'sau_amo', array(
				'id'          => 'sau' . self::OPTION_NAME_SUBDOMAIN,
				'option_name' => self::OPTION_NAME_SUBDOMAIN,
			) );

			add_settings_field( 'sau' . self::OPTION_NAME_LOGIN, __( 'Login', self::OPTION_THEME_LANG ), function ( $val ) {
				$id = $val['id'];
				echo '<input class="regular-text ltr" type="text" name="' . self::OPTION_NAME_LOGIN . '" id="' . $id . '" value="' . esc_attr( get_option( self::OPTION_NAME_LOGIN ) ) . '"/><p class="description">' . __( 'Email user for sing in AmoCRM', self::OPTION_THEME_LANG ) . '</p>';
			}, 'general', 'sau_amo', array(
				'id'          => 'sau' . self::OPTION_NAME_LOGIN,
				'option_name' => self::OPTION_NAME_LOGIN,
			) );

			add_settings_field( 'sau' . self::OPTION_NAME_HASH, __( 'API key', self::OPTION_THEME_LANG ), function ( $val ) {
				$id = $val['id'];
				echo '<input class="regular-text ltr" type="text" name="' . self::OPTION_NAME_HASH . '" id="' . $id . '" value="' . esc_attr( get_option( self::OPTION_NAME_HASH ) ) . '"/>';
			}, 'general', 'sau_amo', array(
				'id'          => 'sau' . self::OPTION_NAME_HASH,
				'option_name' => self::OPTION_NAME_HASH,
			) );
		} );
	}

	static protected function requestAmo() {
		add_action( 'sau_sender_after_success_send_mail', function ( $data ) {
			try {
				$amo = new \AmoCRM\Client( esc_attr( get_option( self::OPTION_NAME_SUBDOMAIN ) ), esc_attr( get_option( self::OPTION_NAME_LOGIN ) ), esc_attr( get_option( self::OPTION_NAME_HASH ) ) );

				$contact = $amo->contact;
				//todo: добавить возможность работы с данными через админку
				$contact['name'] = $data['formData']['name']['value'] ?? __( 'Empty' );
				$contact['tags'] = [
					sprintf( 'Класс: %s', $data['formData']['class']['value'] ),
					sprintf( 'Подготовка к: %s', $data['formData']['type']['value'] ),
					sprintf( 'Предмет: %s', $data['formData']['discipline']['value'] ),
				];

				$contact->addCustomField( 190699, $data['formData']['email']['value'], 'PRIV' );
				$contact->addCustomField( 190697, $data['formData']['phone']['value'], 'MOB' );


				wp_send_json_success( [ 'New contact' . $contact->apiAdd() ] );
			}
			catch ( \AmoCRM\Exception $e ) {
				wp_send_json_error( [ sprintf( 'Error (%d): %s', $e->getCode(), $e->getMessage() ) ] );
			}
		} );
	}
}