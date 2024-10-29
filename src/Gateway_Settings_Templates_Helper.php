<?php

namespace Inspire_Labs\Apaczka_Woocommerce;

class Gateway_Settings_Templates_Helper {


	public function init() {

	}

	/**
	 * @return string
	 */
	private function get_ajax_action_name(): string {
		return 'wp_ajax_' . Plugin::APP_PREFIX . 'get_template';
	}

	/**
	 * @return string
	 */
	private function get_template_option_id(): string {
		return Plugin::APP_PREFIX . 'gateway_opts_templates';
	}

	/**
	 * @return false|string
	 */
	public function get_all_templates_json() {
		return wp_json_encode( get_option( $this->get_template_option_id() ) );
	}

	/**
	 * @return array|null
	 */
	public function get_all_templates_list(): ?array {
		$templates = get_option( $this->get_template_option_id() );
		$return    = [];

		if ( is_array( $templates ) ) {
			foreach ( $templates as $k => $template ) {
				$return[ $k ] = $template['name'];
			}
		}

		return $return;
	}

	/**
	 * @param array $options
	 * @param string $name
	 *
	 * @return void
	 */
	public function create( array $options, string $name ) {
		$option_id = $this->get_template_option_id();
		$templates = get_option( $option_id );
		$slug      = sanitize_title( $name );

		if ( ! is_array( $templates ) ) {
			$templates = [];
		}

		if ( ! empty( $name ) && ! isset( $templates[ $slug ] ) ) {
			$templates[ sanitize_title( $name ) ] = [
				'name'    => $name,
				'options' => $options,
			];
			update_option( $option_id, $templates );
		}
	}

	/**
	 * @return array|null
	 */
	public function get_all_templates(): ?array {
		$all = get_option( $this->get_template_option_id() );

		return ! empty( $all ) ? $all : null;
	}

	/**
	 * @param string $slug
	 *
	 * @return string|null
	 */
	public function get_template_name_by_template_slug( string $slug
	): ?string {
		$all = $this->get_all_templates_list();
		if ( isset( $all[ $slug ] ) ) {
			return $all[ $slug ];
		}

		return null;
	}

	/**
	 * @param $old_slug
	 * @param $new_name
	 *
	 * @return bool
	 */
	public function rename_template( $old_slug, $new_name ): ?string {
		$new_slug = sanitize_title( $new_name );

		if ( empty( $new_slug ) ) {
			return null;
		}

		if ( $new_slug === $old_slug ) {
			return null;
		}

		$all_templates = $this->get_all_templates();

		if ( empty( $all_templates ) ) {
			return null;
		}

		if ( key_exists( $new_slug, $all_templates ) ) {
			return null;
		}

		if ( ! key_exists( $old_slug, $all_templates ) ) {
			return null;
		}

		$template_data = $all_templates[ $old_slug ];
		unset( $all_templates[ $old_slug ] );
		$template_data['name']      = $new_name;
		$all_templates[ $new_slug ] = $template_data;

		update_option( $this->get_template_option_id(), $all_templates );

		return $new_slug;
	}

	/**
	 * @param string $name
	 *
	 * @return array|null
	 */
	public function get_by_name( string $name ): ?array {
		$templates = get_option( $this->get_template_option_id() );
		$slug      = sanitize_title( $name );

		if ( ! is_array( $templates ) && isset( $templates[ $slug ] ) ) {
			return $templates[ $slug ];
		}

		return null;
	}


	/**
	 * @param string $slug
	 *
	 * @return void
	 */
	public function remove_by_slug( string $slug ) {
		$option_id = $this->get_template_option_id();
		$templates = get_option( $option_id );

		if ( is_array( $templates ) && isset( $templates[ $slug ] ) ) {
			unset( $templates[ $slug ] );
			update_option( $option_id, $templates );
		}
	}

	/**
	 * @param string $name
	 *
	 * @return void
	 */
	public function remove_by_name( string $name ) {
		$option_id = $this->get_template_option_id();
		$templates = get_option( $option_id );
		$slug      = sanitize_title( $name );

		if ( ! is_array( $templates ) && isset( $templates[ $slug ] ) ) {
			unset( $templates[ $slug ] );
			update_option( $option_id, $templates );
		}
	}

}
