<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, PT. Lingkar Kreasi (Circle Creative).
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, PT. Lingkar Kreasi (Circle Creative).
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Template\Drivers;

// ------------------------------------------------------------------------

use O2System\Glob\Factory\Registry;
use O2System\Glob\Interfaces\Drivers;
use O2System\Template\Exception;

/**
 * Template Themes Driver
 *
 * @package          Template
 * @subpackage       Library
 * @category         Driver
 * @version          1.0 Build 11.09.2012
 * @author           Steeven Andrian Salim
 * @copyright        Copyright (c) 2005 - 2014 PT. Lingkar Kreasi (Circle Creative)
 * @license          http://www.circle-creative.com/products/o2system/license.html
 * @link             http://www.circle-creative.com
 */
class Theme extends Drivers
{
	/**
	 * Theme Default
	 *
	 * @access public
	 * @type   object
	 */
	public $default;

	/**
	 * Theme Active
	 *
	 * @access public
	 * @type   object
	 */
	public $active;

	/**
	 * Theme Package Path
	 *
	 * @access public
	 * @type   string
	 */
	public $packages_path;

	public function set_default( $theme )
	{
		if ( $this->exists( $theme ) )
		{
			$this->default = $this->load( $theme );

			return TRUE;
		}

		return FALSE;
	}

	public function set_active( $theme )
	{
		if ( $this->exists( $theme ) )
		{
			$this->active = $this->load( $theme );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Load
	 *
	 * @param $theme
	 *
	 * @access  public
	 * @return  bool
	 */
	public function load( $theme )
	{
		$theme_path = $this->packages_path . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;

		if ( file_exists( $theme_path . 'theme.properties' ) )
		{
			$properties = json_decode( file_get_contents( $theme_path . 'theme.properties' ), TRUE );

			if ( json_last_error() === JSON_ERROR_NONE )
			{
				$properties = new Registry( $properties );
				$properties[ 'parameter' ] = $theme;
				$properties[ 'realpath' ] = $theme_path;

				if ( file_exists( $theme_path . 'theme.settings' ) )
				{
					$settings = json_decode( file_get_contents( $theme_path . 'theme.settings' ), TRUE );

					if ( json_last_error() === JSON_ERROR_NONE )
					{
						$properties[ 'settings' ] = new Registry( $settings );
					}
					else
					{
						$this->library->throw_error( 'Unable to read theme settings: ' . $theme_path . 'theme.settings' );
					}
				}

				if ( file_exists( $layout = $theme_path . 'theme.tpl' ) )
				{
					$properties[ 'layout' ] = $layout;
				}

				// Read Partials
				if ( is_dir( $theme_path . 'partials' . DIRECTORY_SEPARATOR ) )
				{
					$partials = scandir( $theme_path . 'partials' . DIRECTORY_SEPARATOR );

					foreach ( $partials as $partial )
					{
						if ( is_file( $theme_path . 'partials' . DIRECTORY_SEPARATOR . $partial ) )
						{
							$properties[ 'partials' ][ pathinfo( $partial, PATHINFO_FILENAME ) ] = $theme_path . 'partials' . DIRECTORY_SEPARATOR . $partial;
						}
					}
				}

				return $properties;
			}

			return $this->library->throw_error( 'Unable to read theme properties: ' . $theme_path . 'theme.properties' );
		}

		return $this->library->throw_error( 'Unable to load theme: ' . $theme_path );
	}

	/**
	 * Theme Checker
	 *
	 * @params  string  $theme  Theme Name
	 *
	 * @access  public
	 * @return  bool
	 */
	public function exists( $theme )
	{
		$theme_path = $this->packages_path . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;

		if ( is_dir( $theme_path ) AND file_exists( $theme_path . 'theme.properties' ) )
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Set Layout
	 *
	 * @params  string  $filename   Layout Name
	 * @params  string  $extension  Layout Extension
	 *
	 * @access  public
	 * @return  bool
	 */
	public function set_layout( $filename = 'theme', $extension = '.tpl' )
	{
		if ( is_dir( $this->active->realpath . 'layouts' . DIRECTORY_SEPARATOR ) )
		{
			$filepath = $this->active->realpath . 'layouts' . DIRECTORY_SEPARATOR . $filename . $extension;
		}
		else
		{
			$filepath = $this->active->realpath . $filename . $extension;
		}

		if ( file_exists( $filepath ) )
		{
			$this->active->layout = $filepath;

			$path = pathinfo( $filepath, PATHINFO_DIRNAME ) . DIRECTORY_SEPARATOR;
			$layout = pathinfo( $filepath, PATHINFO_FILENAME );

			if ( file_exists( $settings = $path . $layout . '.settings' ) )
			{
				$this->active->settings = json_decode( file_get_contents( $settings ), TRUE );
			}

			// Read Partials
			if ( is_dir( $this->active->realpath . 'partials' . DIRECTORY_SEPARATOR . $layout . DIRECTORY_SEPARATOR ) )
			{
				$partials = scandir( $this->active->realpath . 'partials' . DIRECTORY_SEPARATOR . $layout . DIRECTORY_SEPARATOR );
				$this->active->partials = array();

				foreach ( $partials as $partial )
				{
					if ( is_file( $this->active->realpath . 'partials' . DIRECTORY_SEPARATOR . $layout . DIRECTORY_SEPARATOR . $partial ) )
					{
						$this->active->partials[ pathinfo( $partial, PATHINFO_FILENAME ) ] = $this->active->realpath . 'partials' . DIRECTORY_SEPARATOR . $layout . DIRECTORY_SEPARATOR . $partial;
					}
				}
			}

			return TRUE;
		}

		return $this->library->throw_error( 'Unable to load requested layout: ' . $filename );
	}

	public function url( $uri = NULL )
	{
		if ( isset( $this->active ) )
		{
			$base_url = isset( $_SERVER[ 'REQUEST_SCHEME' ] ) ? $_SERVER[ 'REQUEST_SCHEME' ] : 'http';
			$base_url .= '://' . $_SERVER[ 'SERVER_NAME' ];

			// Add server port if needed
			$base_url .= $_SERVER[ 'SERVER_PORT' ] !== '80' ? ':' . $_SERVER[ 'SERVER_PORT' ] : '';

			// Add base path
			$base_url .= dirname( $_SERVER[ 'SCRIPT_NAME' ] );
			$base_url = str_replace( DIRECTORY_SEPARATOR, '/', $base_url );
			$base_url = trim( $base_url, '/' ) . '/';

			// Vendor directory
			$base_dir = explode( 'vendor' . DIRECTORY_SEPARATOR . 'o2system', __DIR__ );
			$base_dir = str_replace( [ 'o2system', '/' ], [ '', DIRECTORY_SEPARATOR ], $base_dir[ 0 ] );
			$base_dir = trim( $base_dir, DIRECTORY_SEPARATOR );

			// Theme directory
			$theme_dir = str_replace( '/', DIRECTORY_SEPARATOR, $this->active->realpath );
			$theme_path = str_replace( $base_dir, '', $theme_dir );
			$theme_path = str_replace( DIRECTORY_SEPARATOR, '/', $theme_path );
			$theme_path = trim($theme_path, '/') . '/';

			$theme_url = $base_url . $theme_path;

			if ( isset( $uri ) )
			{
				$uri = is_array( $uri ) ? implode( '/', $uri ) : $uri;

				return $theme_url . $uri;
			}

			return $theme_url;
		}

		return FALSE;
	}
}