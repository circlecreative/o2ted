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

use O2System\Glob\Interfaces\Drivers;

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
        'bootstrap'
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
        $this->_output = new \stdClass();
    }
    // --------------------------------------------------------------------

    /**
     * @param $paths
     *
     * @return $this
     */
    final public function add_paths( $paths )
    {
        if( is_array( $paths ) )
        {
            foreach( $paths as $path )
            {
                $this->add_paths( $path );
            }
        }
        elseif( is_dir( $paths = $paths . 'assets/' ) )
        {
            $this->_paths[ ] = $paths;
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

        // Load jQuery-UI Package
        $this->_load_packages( [ 'package' => 'jquery-ui', 'theme' => 'no-theme' ] );

        // Load Bootstrap
        $this->_load_packages( [ 'package' => 'bootstrap', 'theme' => 'no-theme' ] );

        if( count( $settings ) == 0 ) return;

        foreach( $settings as $extension => $assets )
        {
            if( method_exists( $this, $method = '_load_' . $extension ) )
            {
                if( is_array( $assets ) )
                {
                    if( $extension === 'packages' )
                    {
                        foreach( $assets as $asset )
                        {
                            $this->{$method}( $asset );
                        }
                    }
                    else
                    {
                        foreach( $assets as $asset )
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

    final protected function _path_to_url( $path )
    {
        $path = str_replace( DIRECTORY_SEPARATOR, '/', $path );
        $script_path = str_replace( DIRECTORY_SEPARATOR, '/', dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) );
        $base_url = $_SERVER[ 'REQUEST_SCHEME' ] . '://' . $_SERVER[ 'SERVER_NAME' ] . dirname( $_SERVER[ 'SCRIPT_NAME' ] ) . '/';

        return $base_url . trim( str_replace( $script_path, '', $path ), '/' );
    }

    final protected function _load_css( $css )
    {
        if( is_array( $css ) AND isset( $css[ 'src' ] ) )
        {
            foreach( $this->_paths as $path )
            {
                if( file_exists( $filepath = $path . 'css/' . $css[ 'src' ] . '.css' ) )
                {
                    $this->link_css( $this->_path_to_url( $filepath ), $css );
                    break;
                }
            }
        }
        elseif( strpos( $css, '://' ) !== FALSE )
        {
            $this->link_css( $css );
        }
        else
        {
            foreach( $this->_paths as $path )
            {
                if( file_exists( $filepath = $path . 'css/' . $css . '.css' ) )
                {
                    $this->link_css( $this->_path_to_url( $filepath ) );
                    break;
                }
            }
        }
    }

    final protected function _load_js( $js )
    {
        if( is_array( $js ) AND isset( $js[ 'src' ] ) )
        {
            foreach( $this->_paths as $path )
            {
                if( file_exists( $filepath = $path . 'js/' . $js[ 'src' ] . '.js' ) )
                {
                    $this->link_js( $this->_path_to_url( $filepath ), $js );
                    break;
                }
            }
        }
        elseif( strpos( $js, '://' ) !== FALSE )
        {
            $this->link_js( $js );
        }
        else
        {
            foreach( $this->_paths as $path )
            {
                if( file_exists( $filepath = $path . 'js/' . $js . '.js' ) )
                {
                    $this->link_js( $this->_path_to_url( $filepath ) );
                    break;
                }
            }
        }
    }

    final protected function _load_icons( $icons )
    {
        if( is_array( $icons ) AND isset( $icons[ 'src' ] ) )
        {
            foreach( $this->_paths as $path )
            {
                if( file_exists( $filepath = $path . 'images/icons/' . $icons[ 'src' ] ) )
                {
                    $this->link_css( $this->_path_to_url( $filepath ), $icons );
                    break;
                }
            }
        }
        elseif( strpos( $icons, '://' ) !== FALSE )
        {
            $this->link_css( $icons );
        }
        else
        {
            foreach( $this->_paths as $path )
            {
                if( file_exists( $filepath = $path . 'images/icons/' . $icons ) )
                {
                    $this->link_css( $this->_path_to_url( $filepath ) );
                    break;
                }
            }
        }
    }

    final protected function _load_fonts( $fonts )
    {
        if( is_array( $fonts ) AND isset( $fonts[ 'src' ] ) )
        {
            foreach( $this->_paths as $path )
            {
                if( file_exists( $filepath = $path . 'fonts/' . $fonts[ 'src' ] . '/' . $fonts[ 'src' ] . '.css' ) )
                {
                    $this->link_css( $this->_path_to_url( $this->_path_to_url( $filepath ), $fonts ) );
                    break;
                }
            }
        }
        elseif( strpos( $fonts, '://' ) !== FALSE )
        {
            $this->link_css( $fonts );
        }
        else
        {
            foreach( $this->_paths as $path )
            {
                if( file_exists( $filepath = $path . 'fonts/' . $fonts . '/' . $fonts . '.css' ) )
                {
                    $this->link_css( $this->_path_to_url( $filepath ) );
                    break;
                }
            }
        }
    }

    final protected function _load_packages( $package )
    {
        if( is_array( $package ) )
        {
            if( isset( $package[ 'package' ] ) )
            {
                foreach( $this->_paths as $path )
                {
                    if( is_dir( $set_path = $path . 'packages/' . $package[ 'package' ] . '/' ) )
                    {
                        $this->_load_sets( $package[ 'package' ], $set_path );

                        // Load Package Theme
                        if( isset( $package[ 'theme' ] ) )
                        {
                            $filenames = array(
                                'themes/' . $package[ 'theme' ] . '.css',
                                'themes/' . $package[ 'theme' ] . '/' . $package[ 'theme' ] . '.css'
                            );

                            foreach( $filenames as $filename )
                            {
                                if( file_exists( $filepath = $set_path . $filename ) )
                                {
                                    $this->link_css( $this->_path_to_url( $filepath ) );
                                    break;
                                }
                            }
                        }

                        // Load Package Plugin
                        if( isset( $package[ 'plugins' ] ) )
                        {
                            if( is_array( $package[ 'plugins' ] ) )
                            {
                                foreach( $package[ 'plugins' ] as $plugin )
                                {
                                    $this->_load_sets( $plugin, $set_path . 'plugins/' );
                                }
                            }
                            else
                            {
                                $this->_load_sets( $package[ 'plugins' ], $set_path . 'plugins/' );
                            }
                        }
                    }
                }
            }
        }
        else
        {
            foreach( $this->_paths as $path )
            {
                if( is_dir( $set_path = $path . 'packages/' . $package . '/' ) )
                {
                    $this->_load_sets( $package, $set_path );
                }
            }
        }
    }

    final protected function _load_sets( $set, $path )
    {
        // Load CSS
        if( file_exists( $filepath = $path . $set . '.css' ) )
        {
            $this->link_css( $this->_path_to_url( $filepath ) );
        }

        // Load JS
        if( file_exists( $filepath = $path . $set . '.js' ) )
        {
            $this->link_js( $this->_path_to_url( $filepath ) );
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
    final public function link_css( $src, array $attr = array() )
    {
        if( empty( $attr ) )
        {
            $attr = array(
                'media' => 'screen',
                'rel'   => 'stylesheet',
                'type'  => 'text/css'
            );
        }

        $attr[ 'href' ] = $src;

        $this->_header[ 'links' ][ pathinfo( $src, PATHINFO_FILENAME ) ] = $attr;
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

        if( empty( $attributes ) )
        {
            return $atts;
        }

        if( is_string( $attributes ) )
        {
            return ' ' . $attributes;
        }

        $attributes = (array)$attributes;

        foreach( $attributes as $key => $val )
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
        if( ! empty( $inline_code ) )
        {
            if( empty( $attributes ) )
            {
                $attributes = array(
                    'media' => 'screen',
                    'rel'   => 'stylesheet',
                    'type'  => 'text/css'
                );
            }

            $this->_header[ 'inline_css' ][ $this->_stringify_attributes( $attributes ) ][ ] = trim( $inline_code );
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
    final public function link_js( $src, array $attr = array(), $position = 'footer' )
    {
        if( empty( $attr ) )
        {
            $attr = array(
                'type' => 'text/javascript'
            );
        }

        $attr[ 'src' ] = $src;

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
        if( ! empty( $inline_code ) )
        {
            if( empty( $attributes ) )
            {
                $attributes = array(
                    'type' => 'text/javascript'
                );
            }

            $this->{'_' . $position}[ 'inline_js' ][ $this->_stringify_attributes( $attributes ) ][ ] = trim( $inline_code );
        }
    }

    // --------------------------------------------------------------------

    protected function _minify_js( $source )
    {

    }

    protected function _minify_css( $source )
    {
        $patterns = array(
            '!/\*[^*]*\*+([^/][^*]*\*+)*/!' => '',        // Remove /* block comments */
            '#\n?//[^\n]*#'                 => '',        // Remove // line comments
            '#\s*([^\w.\#%])\s*#U'          => '$1',
            // Remove spaces following and preceeding non-word characters, excluding dots, hashes and the percent sign
            '#\s{2,}#'                      => ' '
            // Reduce the remaining multiple space characters to a single space
        );

        return preg_replace( array_keys( $patterns ), array_values( $patterns ), $source );
    }

    /**
     * Minify JavaScript and CSS code
     *
     * Strips comments and excessive whitespace characters
     *
     * @param    string $source
     * @param    string $type 'js' or 'css'
     * @param    bool   $tags Whether $this->_output contains the 'script' or 'style' tag
     *
     * @return    string
     */
    protected function _minify_js_css( $source, $type, $tags = FALSE )
    {
        if( $tags === TRUE )
        {
            $tags = array( 'close' => strrchr( $source, '<' ) );

            $open_length = strpos( $source, '>' ) + 1;
            $tags[ 'open' ] = substr( $source, 0, $open_length );

            $source = substr( $source, $open_length, -strlen( $tags[ 'close' ] ) );

            // Strip spaces from the tags
            $tags = preg_replace( '#\s{2,}#', ' ', $tags );
        }

        $source = trim( $source );

        if( $type === 'js' )
        {
            // Catch all string literals and comment blocks
            if( preg_match_all( '#((?:((?<!\\\)\'|")|(/\*)|(//)).*(?(2)(?<!\\\)\2|(?(3)\*/|\n)))#msuUS', $source, $match, PREG_OFFSET_CAPTURE ) )
            {
                $js_literals = $js_code = array();
                for( $match = $match[ 0 ], $c = count( $match ), $i = $pos = $offset = 0; $i < $c; $i++ )
                {
                    $js_code[ $pos++ ] = trim( substr( $source, $offset, $match[ $i ][ 1 ] - $offset ) );
                    $offset = $match[ $i ][ 1 ] + strlen( $match[ $i ][ 0 ] );

                    // Save only if we haven't matched a comment block
                    if( $match[ $i ][ 0 ][ 0 ] !== '/' )
                    {
                        $js_literals[ $pos++ ] = array_shift( $match[ $i ] );
                    }
                }
                $js_code[ $pos ] = substr( $source, $offset );

                // $match might be quite large, so free it up together with other vars that we no longer need
                unset( $match, $offset, $pos );
            }
            else
            {
                $js_code = array( $source );
                $js_literals = array();
            }

            $varname = 'js_code';
        }
        else
        {
            $varname = 'output';
        }

        // Standartize new lines
        $$varname = str_replace( array( "\r\n", "\r" ), "\n", $$varname );

        if( $type === 'js' )
        {
            $patterns = array(
                '#\s*([!\#%&()*+,\-./:;<=>?@\[\]^`{|}~])\s*#' => '$1',
                // Remove spaces following and preceeding JS-wise non-special & non-word characters
                '#\s{2,}#'                                    => ' '
                // Reduce the remaining multiple whitespace characters to a single space
            );
        }
        else
        {
            $patterns = array(
                '#/\*.*(?=\*/)\*/#s'   => '',        // Remove /* block comments */
                '#\n?//[^\n]*#'        => '',        // Remove // line comments
                '#\s*([^\w.\#%])\s*#U' => '$1',
                // Remove spaces following and preceeding non-word characters, excluding dots, hashes and the percent sign
                '#\s{2,}#'             => ' '        // Reduce the remaining multiple space characters to a single space
            );
        }

        $$varname = preg_replace( array_keys( $patterns ), array_values( $patterns ), $$varname );

        // Glue back JS quoted strings
        if( $type === 'js' )
        {
            $js_code += $js_literals;
            ksort( $js_code );
            $source = implode( $js_code );
            unset( $js_code, $js_literals, $varname, $patterns );
        }

        return is_array( $tags )
            ? $tags[ 'open' ] . $source . $tags[ 'close' ]
            : $source;
    }

    protected function _gather_assets()
    {
        // Header Link Output
        if( ! empty( $this->_header[ 'links' ] ) )
        {
            foreach( $this->_header[ 'links' ] as $key => $attr )
            {
                $this->_output->header[ ] = '<link' . $this->_stringify_attributes( $attr ) . '>';
            }
        }

        // Header Inline CSS
        if( ! empty( $this->_header[ 'inline_css' ] ) )
        {
            $styles = array_unique( $this->_header[ 'inline_css' ] );
            foreach( $styles as $attr => $style )
            {
                $inline_css = "<style" . $attr . ">\n";
                $inline_css .= implode( "\n", $style );
                $inline_css .= "\n</style>\n";

                $this->_output->header[ ] = $inline_css;
            }
        }

        // Footer Link Script
        if( ! empty( $this->_footer[ 'scripts' ] ) )
        {
            foreach( $this->_footer[ 'scripts' ] as $key => $attr )
            {
                $this->_output->footer[ ] = '<script' . $this->_stringify_attributes( $attr ) . '></script>';
            }
        }

        // Footer Inline Script
        if( ! empty( $this->_footer[ 'inline_js' ] ) )
        {
            $scripts = array_unique( $this->_footer[ 'inline_js' ] );

            foreach( $scripts as $attr => $script )
            {
                $inline_js = "<script" . $attr . ">\n";
                $inline_js .= implode( "\n", $script );
                $inline_js .= "\n</script>\n";

                $this->_output->footer[ ] = $inline_js;
            }
        }

        $this->_output->header = implode( PHP_EOL, $this->_output->header );
        $this->_output->footer = implode( PHP_EOL, $this->_output->footer );
    }

    protected function _combine_assets()
    {
        $inline_css = '';

        // Header Link Output
        if( ! empty( $this->_header[ 'links' ] ) )
        {
            foreach( $this->_header[ 'links' ] as $key => $attr )
            {
                if( isset( $attr[ 'href' ] ) )
                {
                    if( $this->_config[ 'minify' ] === TRUE )
                    {
                        $inline_css .= $this->_minify_css( @file_get_contents( $attr[ 'href' ] ) );
                    }
                    else
                    {
                        $inline_css .= file_get_contents( $attr[ 'href' ] );
                    }
                }
            }
        }

        // Header Inline CSS
        if( ! empty( $this->_header[ 'inline_css' ] ) )
        {
            $styles = array_unique( $this->_header[ 'inline_css' ] );
            foreach( $styles as $attr => $style )
            {
                $inline_css = "<style" . $attr . ">\n";
                $inline_css .= implode( "\n", $style );
                $inline_css .= "\n</style>\n";

                $this->_output->header[ ] = $inline_css;
            }
        }

        // Footer Link Script
        if( ! empty( $this->_footer[ 'scripts' ] ) )
        {
            foreach( $this->_footer[ 'scripts' ] as $key => $attr )
            {
                $this->_output->footer[ ] = '<script' . $this->_stringify_attributes( $attr ) . '></script>';
            }
        }

        // Footer Inline Script
        if( ! empty( $this->_footer[ 'inline_js' ] ) )
        {
            $scripts = array_unique( $this->_footer[ 'inline_js' ] );

            foreach( $scripts as $attr => $script )
            {
                $inline_js = "<script" . $attr . ">\n";
                $inline_js .= implode( "\n", $script );
                $inline_js .= "\n</script>\n";

                $this->_output->footer[ ] = $inline_js;
            }
        }

        $this->_output->header = implode( PHP_EOL, $this->_output->header );
        $this->_output->footer = implode( PHP_EOL, $this->_output->footer );
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
        if( $this->_config[ 'combine' ] === TRUE )
        {
            $this->_combine_assets();
        }
        else
        {
            $this->_gather_assets();
        }

        return $this->_output;
    }
}