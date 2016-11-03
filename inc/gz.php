<?php

class Minit_Pro_GZ {

	public static function compress( $source, $data, $level = 9 ) {
		$mode  = 'wb' . $level;
		$fp    = gzopen( $source . '.gz', $mode );

		gzwrite( $fp, $data );

		return gzclose( $fp );
	}

	public static function compress_source( $source, $level = 9 ) {
		$dest  = $source . '.gz';
		$mode  = 'wb' . $level;
		$error = false;

		if ( $fp_out = gzopen( $dest, $mode ) ) {
			if ( $fp_in = fopen( $source, 'rb' ) ) {
				while ( ! feof( $fp_in ) ) {
					gzwrite( $fp_out, fread( $fp_in, 1024 * 512 ) );
				}

				fclose( $fp_in );
			} else {
				$error = true;
			}

			gzclose( $fp_out );
		} else {
			$error = true;
		}

		if ( $error ) {
			return false;
		} else {
			return $dest;
		}
	}

}
