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

use O2System\Bootstrap\Factory\Tag;
use O2System\Glob\Interfaces\Drivers;

use MatthiasMullie\Minify;
use O2System\Cache;

/**
 * Template Assets
 *
 * Manage Template Assets Library
 *
 * @package       o2system
 * @subpackage    libraries
 * @category      Library Driver
 * @author        Steeven Andrian Salim
 * @link          http://o2system.center/framework/user-guide/library/template/drivers/assets.html
 */
final class Assets extends Drivers
{
	/**
	 * Class Config
	 *
	 * @access  public
	 *
	 * @type    array
	 */
	public $config;

	/**
	 * Assets Paths
	 *
	 * List of assets paths
	 *
	 * @access  protected
	 *
	 * @type array
	 */
	protected $_paths = array();

	protected $_header = array();

	protected $_footer = array();

	protected $_frameworks = array(
		'jquery',
		'jquery-ui',
		'bootstrap',
	);

	/**
	 * CSS Assets
	 *
	 * List of loaded css assets
	 *
	 * @access  private
	 *
	 * @type    array
	 */
	protected $_css = array();

	/**
	 * Javascript Assets
	 *
	 * List of loaded javascripts assets
	 *
	 * @access  private
	 *
	 * @type    array
	 */
	protected $_js = array();

	/**
	 * Assets Output
	 *
	 * @access  private
	 *
	 * @type    array
	 */
	protected $_output;

	// ------------------------------------------------------------------------

	/**
	 * Class Constructor
	 *
	 * @access  public
	 */
	public function __reconstruct()
	{
		$this->destroy();
	}

	// --------------------------------------------------------------------

	final public function destroy()
	{
		$this->_output = new \stdClass();
		$this->_output->header = array();
		$this->_output->footer = array();
	}

	/**
	 * @param $paths
	 *
	 * @return $this
	 */
	final public function add_paths( $paths )
	{
		if ( is_array( $paths ) )
		{
			foreach ( $paths as $path )
			{
				$this->add_paths( $path );
			}
		}
		elseif ( is_dir( $paths = $paths . 'assets' . DIRECTORY_SEPARATOR ) )
		{
			$this->_paths[] = $paths;
		}

		return $this;
	}
	// --------------------------------------------------------------------

	/**
	 * Parse Theme Settings
	 *
	 * @param array $settings
	 */
	final public function parse_settings( array $settings = array() )
	{
		// Load jQuery
		$this->_load_js( 'jquery' );

		// Load Bootstrap
		$this->_load_packages( [ 'package' => 'bootstrap', 'theme' => 'no-theme' ] );

		// Load jQuery-UI Package
		$this->_load_packages( [ 'package' => 'jquery-ui', 'theme' => 'no-theme' ] );


		if ( count( $settings ) == 0 ) return;

		foreach ( $settings as $extension => $assets )
		{
			if ( method_exists( $this, $method = '_load_' . $extension ) )
			{
				if ( is_array( $assets ) )
				{
					if ( $extension === 'packages' )
					{
						foreach ( $assets as $asset )
						{
							$this->{$method}( $asset );
						}
					}
					else
					{
						foreach ( $assets as $asset )
						{
							$this->{$method}( $asset );
						}
					}
				}
				else
				{
					$this->{$method}( $assets );
				}
			}
		}
	}

	final public function path_to_url( $path )
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

		$path = str_replace( [ $base_dir, DIRECTORY_SEPARATOR ], [ '', '/' ], $path );
		$path = trim( $path, '/' );

		return trim( $base_url . $path, '/' );
	}

	final protected function _load_css( $css )
	{
		if ( is_array( $css ) AND isset( $css[ 'src' ] ) )
		{
			foreach ( $this->_paths as $path )
			{
				if ( file_exists( $filepath = $path . 'css' . DIRECTORY_SEPARATOR . $css[ 'src' ] . '.css' ) )
				{
					$css[ 'realpath' ] = $filepath;
					$this->link_css( $this->path_to_url( $filepath ), $css );
					break;
				}
			}
		}
		elseif ( strpos( $css, '://' ) !== FALSE )
		{
			$this->link_css( $css );
		}
		else
		{
			foreach ( $this->_paths as $path )
			{
				if ( file_exists( $filepath = $path . 'css' . DIRECTORY_SEPARATOR . $css . '.css' ) )
				{
					$this->link_css( $this->path_to_url( $filepath ), [ 'realpath' => $filepath ] );
					break;
				}
			}
		}
	}

	final protected function _load_js( $js )
	{
		if ( is_array( $js ) AND isset( $js[ 'src' ] ) )
		{
			foreach ( $this->_paths as $path )
			{
				if ( file_exists( $filepath = $path . 'js' . DIRECTORY_SEPARATOR . $js[ 'src' ] . '.js' ) )
				{
					$js[ 'realpath' ] = $filepath;
					$this->link_js( $this->path_to_url( $filepath ), $js );
					break;
				}
			}
		}
		elseif ( strpos( $js, '://' ) !== FALSE )
		{
			$this->link_js( $js );
		}
		else
		{
			foreach ( $this->_paths as $path )
			{
				if ( file_exists( $filepath = $path . 'js' . DIRECTORY_SEPARATOR . $js . '.js' ) )
				{
					$this->link_js( $this->path_to_url( $filepath ), [ 'realpath' => $filepath ] );
					break;
				}
			}
		}
	}

	/**
	 * Link Icons
	 *
	 * @access  public
	 * @final   This method can't be overwritten
	 *
	 * @param   string $src  CSS Link Href
	 * @param   array  $attr CSS Link Attributes
	 */
	final public function link_icons( $src, array $attr = array() )
	{
		if ( empty( $attr ) )
		{
			$attr = array(
				'media' => 'shortcut-icon',
				'rel'   => 'favicon.ico',
				'type'  => 'image/x-icon',
			);
		}

		$attr[ 'href' ] = $src;

		$this->_header[ 'icons' ][ pathinfo( $src, PATHINFO_FILENAME ) ] = $attr;
	}

	final protected function _load_icons( $icons )
	{
		if ( is_array( $icons ) AND isset( $icons[ 'src' ] ) )
		{
			foreach ( $this->_paths as $path )
			{
				if ( file_exists( $filepath = $path . 'images' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $icons[ 'src' ] ) )
				{
					$this->link_icons( $this->path_to_url( $filepath ), $icons );
					break;
				}
			}
		}
		elseif ( strpos( $icons, '://' ) !== FALSE )
		{
			$this->link_icons( $icons );
		}
		else
		{
			foreach ( $this->_paths as $path )
			{
				if ( file_exists( $filepath = $path . 'images' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $icons ) )
				{
					$this->link_icons( $this->path_to_url( $filepath ) );
					break;
				}
			}
		}
	}


	/**
	 * Link Fonts
	 *
	 * @access  public
	 * @final   This method can't be overwritten
	 *
	 * @param   string $src  CSS Link Href
	 * @param   array  $attr CSS Link Attributes
	 */
	final public function link_fonts( $src, $path = NULL, array $attr = array() )
	{
		if ( empty( $attr ) )
		{
			$attr = array(
				'media' => 'screen',
				'rel'   => 'stylesheet',
				'type'  => 'text/css',
			);
		}

		$attr[ 'href' ] = $src;

		$this->_header[ 'font' ][ pathinfo( $src, PATHINFO_FILENAME ) ] = $attr;
	}

	final protected function _load_fonts( $fonts )
	{
		if ( is_array( $fonts ) AND isset( $fonts[ 'src' ] ) )
		{
			foreach ( $this->_paths as $path )
			{
				if ( file_exists( $filepath = $path . 'fonts' . DIRECTORY_SEPARATOR . $fonts[ 'src' ] . DIRECTORY_SEPARATOR . $fonts[ 'src' ] . '.css' ) )
				{
					$this->link_fonts( $this->path_to_url( $this->path_to_url( $filepath ), $fonts ), [ 'realpath' => $filepath ] );
					break;
				}
			}
		}
		elseif ( strpos( $fonts, '://' ) !== FALSE )
		{
			$this->link_fonts( $fonts );
		}
		else
		{
			foreach ( $this->_paths as $path )
			{
				if ( file_exists( $filepath = $path . 'fonts' . DIRECTORY_SEPARATOR . $fonts . DIRECTORY_SEPARATOR . $fonts . '.css' ) )
				{
					$this->link_fonts( $this->path_to_url( $filepath ), [ 'realpath' => $filepath ] );
					break;
				}
			}
		}
	}

	final protected function _load_packages( $package )
	{
		if ( is_array( $package ) )
		{
			if ( isset( $package[ 'package' ] ) )
			{
				foreach ( $this->_paths as $path )
				{
					if ( is_dir( $set_path = $path . 'packages' . DIRECTORY_SEPARATOR . $package[ 'package' ] . DIRECTORY_SEPARATOR ) )
					{
						$this->_load_sets( $package[ 'package' ], $set_path );

						// Load Package Theme
						if ( isset( $package[ 'theme' ] ) )
						{
							$filenames = array(
								'themes' . DIRECTORY_SEPARATOR . $package[ 'theme' ] . '.css',
								'themes' . DIRECTORY_SEPARATOR . $package[ 'theme' ] . DIRECTORY_SEPARATOR . $package[ 'theme' ] . '.css',
							);

							foreach ( $filenames as $filename )
							{
								if ( file_exists( $filepath = $set_path . $filename ) )
								{
									$this->link_css( $this->path_to_url( $filepath ), [ 'realpath' => $filepath ] );
									break;
								}
							}
						}

						// Load Package Plugin
						if ( isset( $package[ 'plugins' ] ) )
						{
							if ( is_array( $package[ 'plugins' ] ) )
							{
								foreach ( $package[ 'plugins' ] as $plugin )
								{
									$this->_load_sets( $plugin, $set_path . 'plugins' . DIRECTORY_SEPARATOR );
								}
							}
							else
							{
								$this->_load_sets( $package[ 'plugins' ], $set_path . 'plugins' . DIRECTORY_SEPARATOR );
							}
						}
					}
				}
			}
		}
		else
		{
			foreach ( $this->_paths as $path )
			{
				if ( is_dir( $set_path = $path . 'packages' . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR ) )
				{
					$this->_load_sets( $package, $set_path );
				}
			}
		}
	}

	final protected function _load_sets( $set, $path )
	{
		// Load CSS
		if ( file_exists( $filepath = $path . $set . '.css' ) )
		{
			$this->link_css( $this->path_to_url( $filepath ), [ 'realpath' => $filepath ] );
		}

		// Load JS
		if ( file_exists( $filepath = $path . $set . '.js' ) )
		{
			$this->link_js( $this->path_to_url( $filepath ), [ 'realpath' => $filepath ] );
		}
	}

	/**
	 * Link CSS
	 *
	 * @access  public
	 * @final   This method can't be overwritten
	 *
	 * @param   string $src  CSS Link Href
	 * @param   array  $attr CSS Link Attributes
	 */
	final public function link_css( $href, $path = NULL, array $attr = array() )
	{
		if ( empty( $attr ) )
		{
			$attr = array(
				'media' => 'screen',
				'rel'   => 'stylesheet',
				'type'  => 'text/css',
			);
		}

		$attr[ 'href' ] = $href;

		if ( is_array( $path ) )
		{
			$attr = array_merge( $attr, $path );
		}


		$this->_header[ 'links' ][ pathinfo( $href, PATHINFO_FILENAME ) ] = $attr;
	}
	// --------------------------------------------------------------------

	/**
	 * Stringify attributes for use in HTML tags.
	 *
	 * Helper function used to convert a string, array, or object
	 * of attributes to a string.
	 *
	 * @param    mixed    string, array, object
	 * @param    bool
	 *
	 * @return    string
	 */
	protected function _stringify_attributes( $attributes, $js = FALSE )
	{
		$atts = NULL;

		if ( empty( $attributes ) )
		{
			return $atts;
		}

		if ( is_string( $attributes ) )
		{
			return ' ' . $attributes;
		}

		$attributes = (array) $attributes;

		foreach ( $attributes as $key => $val )
		{
			$atts .= ( $js ) ? $key . '=' . $val . ',' : ' ' . $key . '="' . $val . '"';
		}

		return rtrim( $atts, ',' );
	}

	/**
	 * Inline CSS
	 *
	 * @access  public
	 * @final   This method can't be overwritten
	 *
	 * @param   string $inline_code CSS Source Code
	 * @param   array  $attributes  CSS Tag Attributes
	 */
	final public function inline_css( $inline_code, array $attributes = array() )
	{
		if ( ! empty( $inline_code ) )
		{
			if ( empty( $attributes ) )
			{
				$attributes = array(
					'media' => 'screen',
					'rel'   => 'stylesheet',
					'type'  => 'text/css',
				);
			}

			$this->_header[ 'inline_css' ][ $this->_stringify_attributes( $attributes ) ][] = trim( $inline_code );
		}
	}
	// --------------------------------------------------------------------

	/**
	 * Javascript Link
	 *
	 * @access  public
	 * @final   This method can't be overwritten
	 *
	 * @param   string $src      Javascript Link Href
	 * @param   array  $attr     Javascript Link Attributes
	 * @param   string $position Javascript Asset Position on HTML Source Code header|footer
	 */
	final public function link_js( $src, $path = NULL, array $attr = array(), $position = 'footer' )
	{
		if ( empty( $attr ) )
		{
			$attr = array(
				'type' => 'text/javascript',
			);
		}

		$attr[ 'src' ] = $src;

		if ( is_array( $path ) )
		{
			$attr = array_merge( $attr, $path );
		}

		$this->{'_' . $position}[ 'scripts' ][ pathinfo( $src, PATHINFO_FILENAME ) ] = $attr;

	}
	// --------------------------------------------------------------------

	/**
	 * Inline Javascript Code
	 *
	 * @access  public
	 * @final   This method can't be overwritten
	 *
	 * @param   string $inline_code Javascript Source Code
	 * @param   array  $attributes  Javascript Tag Attributes
	 */
	final public function inline_js( $inline_code, array $attributes = array(), $position = 'footer' )
	{
		if ( ! empty( $inline_code ) )
		{
			if ( empty( $attributes ) )
			{
				$attributes = array(
					'type' => 'text/javascript',
				);
			}

			$this->{'_' . $position}[ 'inline_js' ][ $this->_stringify_attributes( $attributes ) ][] = trim( $inline_code );
		}
	}

	// --------------------------------------------------------------------

	protected function _gather_assets()
	{
		// Icon Link Output
		if ( ! empty( $this->_header[ 'icons' ] ) )
		{
			foreach ( $this->_header[ 'icons' ] as $key => $attr )
			{
				unset( $attr[ 'realpath' ] );
				$this->_output->header[] = ( new Tag( 'link', $attr ) )->render();
			}
		}

		// Fonts Link Output
		if ( ! empty( $this->_header[ 'font' ] ) )
		{
			foreach ( $this->_header[ 'font' ] as $key => $attr )
			{
				unset( $attr[ 'realpath' ] );
				$this->_output->header[] = ( new Tag( 'link', $attr ) )->render();
			}
		}

		// Header Link Output
		if ( ! empty( $this->_header[ 'links' ] ) )
		{
			foreach ( $this->_header[ 'links' ] as $key => $attr )
			{
				unset( $attr[ 'realpath' ] );
				$this->_output->header[] = ( new Tag( 'link', $attr ) )->render();
			}
		}

		// Header Inline CSS
		if ( ! empty( $this->_header[ 'inline_css' ] ) )
		{
			$styles = array_unique( $this->_header[ 'inline_css' ] );
			foreach ( $styles as $attr => $style )
			{
				$inline_css = ( new Tag( 'style', implode( PHP_EOL, $style ), array( $attr ) ) )->render();
				$this->_output->header[] = $inline_css;
			}
		}

		// Footer Link Script
		if ( ! empty( $this->_footer[ 'scripts' ] ) )
		{
			foreach ( $this->_footer[ 'scripts' ] as $key => $attr )
			{
				unset( $attr[ 'realpath' ] );
				$this->_output->footer[] = ( new Tag( 'script', $attr ) )->render();
			}
		}

		// Footer Inline Script
		if ( ! empty( $this->_footer[ 'inline_js' ] ) )
		{
			$scripts = array_unique( $this->_footer[ 'inline_js' ] );

			foreach ( $scripts as $attr => $script )
			{
				$this->_output->footer[] = ( new Tag( 'script', $attr ) )->render();
			}
		}
	}

	protected function _combine_assets()
	{
		$inline_css = '';

		// Header Link Output
		if ( ! empty( $this->_header[ 'links' ] ) )
		{
			//print_out($this->_header['links']);
			foreach ( $this->_header[ 'links' ] as $key => $attr )
			{
				if ( isset( $attr[ 'realpath' ] ) )
				{
					$minifier = new Minify\CSS( $attr[ 'realpath' ] );
					$output[] = $minifier->minify();
				}
				elseif ( isset( $attr[ 'href' ] ) )
				{
					unset( $attr[ 'realpath' ] );
					$this->_output->header[] = '<link' . $this->_stringify_attributes( $attr ) . '>';
				}
			}

			$this->_output->header[] = '<style type="text/css">' . implode( PHP_EOL, $output ) . '</style>';
		}

		// Header Inline CSS
		if ( ! empty( $this->_header[ 'inline_css' ] ) )
		{
			$styles = array_unique( $this->_header[ 'inline_css' ] );
			foreach ( $styles as $attr => $style )
			{
				$inline_css = "<style" . $attr . ">\n";
				$inline_css .= implode( "\n", $style );
				$inline_css .= "\n</style>\n";

				$this->_output->header[] = $inline_css;
			}
		}

		//header fonts
		if ( ! empty( $this->_header[ 'fonts' ] ) )
		{
			foreach ( $this->_header[ 'fonts' ] as $key => $attr )
			{
				unset( $attr[ 'realpath' ] );
				$this->_output->header[] = '<link' . $this->_stringify_attributes( $attr ) . '>';
			}
		}

		//header icons
		if ( ! empty( $this->_header[ 'icons' ] ) )
		{
			foreach ( $this->_header[ 'icons' ] as $key => $attr )
			{
				unset( $attr[ 'realpath' ] );
				$this->_output->header[] = '<link' . $this->_stringify_attributes( $attr ) . '>';
			}
		}

		// Footer Link Script
		if ( ! empty( $this->_footer[ 'scripts' ] ) )
		{

			//print_out($this->_footer[ 'scripts' ]);
			foreach ( $this->_footer[ 'scripts' ] as $key => $attr )
			{
				if ( isset( $attr[ 'realpath' ] ) )
				{
					$jsminifier = new Minify\JS( $attr[ 'realpath' ] );
					$jsoutput[] = $jsminifier->minify();
				}
				elseif ( isset( $attr[ 'src' ] ) )
				{
					unset( $attr[ 'realpath' ] );
					$this->_output->footer[] = '<script' . $this->_stringify_attributes( $attr ) . '></script>';
				}
			}

			$this->_output->footer[] = "<script type='text/javascript'>" . implode( PHP_EOL, $jsoutput ) . "</script>";
		}

		// Footer Inline Script
		if ( ! empty( $this->_footer[ 'inline_js' ] ) )
		{
			$scripts = array_unique( $this->_footer[ 'inline_js' ] );

			foreach ( $scripts as $attr => $script )
			{
				$inline_js = "<script" . $attr . ">\n";
				$inline_js .= implode( "\n", $script );
				$inline_js .= "\n</script>\n";

				$this->_output->footer[] = $inline_js;
			}
		}
	}

	/**
	 * Render Assets
	 *
	 * @access  public
	 * @final   This can't be overwritten
	 *
	 * @return string
	 */
	final public function render()
	{
		$this->_config[ 'combine' ] === TRUE ? $this->_combine_assets() : $this->_gather_assets();

		// Implode all assets
		$output = new \stdClass();
		$output->header = implode( PHP_EOL, $this->_output->header );
		$output->footer = implode( PHP_EOL, $this->_output->footer );

		// Destroy Output
		$this->destroy();

		return $output;

	}
}