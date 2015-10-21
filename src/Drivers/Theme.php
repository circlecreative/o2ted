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
 * @package		O2System
 * @author		Circle Creative Dev Team
 * @copyright	Copyright (c) 2005 - 2015, PT. Lingkar Kreasi (Circle Creative).
 * @license		http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license	    http://opensource.org/licenses/MIT	MIT License
 * @link		http://circle-creative.com/products/o2system-codeigniter.html
 * @since		Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Template\Drivers;

// ------------------------------------------------------------------------

use O2System\Glob\Interfaces\Drivers;

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

    /**
     * Theme Loader
     *
     * @params $theme   string  Theme pathname
     *
     * @access public
     * @return chaining
     */
    public function load( $theme )
    {
        $theme_path = $this->packages_path . 'themes/' . $theme . '/';

        if( is_dir( $theme_path ) AND file_exists( $prop = $theme_path . 'theme.properties' ) )
        {
            $this->active = json_decode( file_get_contents( $prop ) );
            @$this->active->parameter = $theme;
            @$this->active->realpath = $theme_path;

            if( file_exists( $settings = $theme_path . 'theme.settings' ) )
            {
                $this->active->settings = json_decode( file_get_contents( $settings ), TRUE );
            }

            if( file_exists( $layout = $theme_path . 'theme.tpl' ) )
            {
                $this->active->layout = $layout;
            }

            // Read Partials
            if( is_dir( $theme_path . 'partials/' ) )
            {
                $partials = scandir( $theme_path . 'partials/' );

                foreach( $partials as $partial )
                {
                    if( is_file( $theme_path . 'partials/' . $partial ) )
                    {
                        $this->active->partials[ pathinfo( $partial, PATHINFO_FILENAME ) ] = $theme_path . 'partials/' . $partial;
                    }
                }
            }
        }

        if( empty( $this->active ) )
        {
            throw new Exception( 'Unable to load theme: ' . $theme );
        }

        return $this;
    }

    /**
     * Theme Checker
     *
     * @params $theme   string  Theme pathname
     *
     * @access public
     * @return chaining
     */
    public function exists( $theme )
    {
        $theme_path = $this->packages_path . 'themes/' . $theme . '/';

        if( is_dir( $theme_path ) AND file_exists( $theme_path . 'theme.properties' ) )
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Set Theme Layout
     *
     * @access  public
     *
     * @params  string $filename  Layout pathname, pack name or page name
     */
    public function layout( $filename = 'theme', $extension = '.tpl' )
    {
        if( is_dir( $this->active->realpath . 'layouts/' ) )
        {
            $filepath = $this->active->realpath . 'layouts/' . $filename . $extension;
        }
        else
        {
            $filepath = $this->active->realpath . $filename . $extension;
        }

        if( file_exists( $filepath ) )
        {
            $this->active->layout = $filepath;

            $path = pathinfo( $filepath, PATHINFO_DIRNAME ) . '/';
            $layout = pathinfo( $filepath, PATHINFO_FILENAME );

            if( file_exists( $settings = $path . $layout.'.settings' ) )
            {
                $this->active->settings = json_decode( file_get_contents( $settings ), TRUE );
            }

            // Read Partials
            if( is_dir( $this->active->realpath . 'partials/' . $layout . '/' ) )
            {
                $partials = scandir( $this->active->realpath . 'partials/' . $layout . '/' );
                $this->active->partials = array();

                foreach( $partials as $partial )
                {
                    if( is_file( $this->active->realpath . 'partials/' . $layout . '/' . $partial ) )
                    {
                        $this->active->partials[ pathinfo( $partial, PATHINFO_FILENAME ) ] = $this->active->realpath . 'partials/' . $layout . '/' . $partial;
                    }
                }
            }
        }
        else
        {
            throw new Exception( 'Unable to load requested layout: ' . $filename );
        }
    }
}