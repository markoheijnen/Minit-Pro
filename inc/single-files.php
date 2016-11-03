<?php

class Minit_Pro_Single_Files {
	private $folder;

	private $done = array(
		'style' => array(),
		'script'  => array()
	);

	public function __construct() {
		add_filter( 'print_styles_array', array( $this, 'process_styles' ), 1000 );
		add_filter( 'print_scripts_array', array( $this, 'process_scripts' ), 1000 );

		add_filter( 'style_loader_src', array( $this, 'replace_style_source' ), 1, 2 );
		add_filter( 'script_loader_src', array( $this, 'replace_script_source' ), 1, 2 );
	}


	public function process_styles( $todo ) {
		global $wp_styles;

		$this->process( $todo, 'style', 'css', $wp_styles );
		
		return $todo;
	}

	public function process_scripts( $todo ) {
		global $wp_scripts;

		$this->process( $todo, 'script', 'js', $wp_scripts );
		
		return $todo;
	}

	private function process( $todo, $type, $ext, $todo_info ) {
		$minit_pro   = Minit_Pro::instance();
		$folder_info = $this->get_folder_info();

		foreach ( $todo as $handle ) {
			$version = $todo_info->registered[ $handle ]->ver;
			$src     = $todo_info->registered[ $handle ]->src;

			if ( ! $src ) {
				continue;
			}

			$file = $type . '-' . $handle . '-' . $version . '.' . $ext;

			if ( file_exists( $folder_info['path'] . $file ) ) {
				$this->done[ $type ][ $handle ] = $folder_info['url'] . $file;
				continue;
			}

			$src_path = $this->get_asset_relative_path( $src );

			if ( ! file_exists( $src_path ) ) {
				continue;
			}

			$data = file_get_contents( $src_path );
			if ( 'style' == $type ) {
				$data = $minit_pro->minify_css( $data );
			}
			else {
				$data = $minit_pro->minify_js( $data );
			}

			file_put_contents( $folder_info['path'] . $file, $data );

			if ( apply_filters( 'minit_pro_create_gz_file', false ) ) {
				Minit_Pro_GZ::compress( $folder_info['path'] . $file, $data );
			}

			$this->done[ $type ][ $handle ] = $folder_info['url'] . $file;
		}

		return $todo;
	}


	public function replace_style_source( $src, $handle ) {
		return $this->replace_source( $src, $handle, 'style' );
	}

	public function replace_script_source( $src, $handle ) {
		return $this->replace_source( $src, $handle, 'script' );
	}

	private function replace_source( $src, $handle, $type ) {
		if ( isset( $this->done[ $type ][ $handle ] ) ) {
			$src = $this->done[ $type ][ $handle ];
		}

		return $src;
	}



	private function get_folder_info() {
		if ( ! $this->folder ) {
			$wp_upload_dir = wp_upload_dir();
			$this->folder  = array(
				'path' => $wp_upload_dir['basedir'] . '/minit/',
				'url'  => $wp_upload_dir['baseurl'] . '/minit/'
			);

			if ( ! is_dir( $this->folder['path'] ) ) {
				mkdir( $this->folder['path'] );
			}
		}

		return $this->folder;
	}

	/**
	 * Return asset URL relative to the `base_url`.
	 *
	 * @param string $handle Asset handle
	 *
	 * @return string|boolean Asset file path or `false` if not found
	 */
	protected function get_asset_relative_path( $src ) {
		//URL in WordPress folder
		if ( '/' === $src[0] ) {
			$full_path = ABSPATH . $src;
		}
		else {
			$full_path = $this->url_to_path( $src );
		}

		if ( file_exists( $full_path ) ) {
			return $full_path;
		}

		return false;
	}

	/**
	 * Return the server path of a given URL.
	 *
	 * @param string $url The URL of a file
	 *
	 * @return string Full server path of the URL
	 */
	protected function url_to_path( $url ) {
		$full_path = str_replace( WPMU_PLUGIN_URL, WPMU_PLUGIN_DIR, $url );
		$full_path = str_replace( plugins_url(), WP_PLUGIN_DIR, $full_path );
		$full_path = str_replace( get_theme_root_uri(), get_theme_root(), $full_path );
		$full_path = str_replace( content_url(), WP_CONTENT_DIR, $full_path );

		return $full_path;
	}


}
