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

namespace O2System;

use O2System\Glob\Interfaces\Libraries;
use O2System\Glob\Exception;

class Template extends Libraries
{
    /**
     * Template Parser Resource
     *
     * @access  public
     * @type    Parser
     */
    public $parser;

    /**
     * Template Class Constructor
     *
     * @param Parser $parser
     * @param Cache  $cache
     *
     * @access  public
     */
    public function __construct( $config = array() )
    {
        // Define Template Glob Exception
        $exception = new Exception();
        $exception->register_path( __DIR__ . '/Views/' );
        $exception->register_handler();

        parent::__construct( $config );

        if( isset( $this->_config[ 'parser' ] ) )
        {
            $this->parser = new Parser( $this->_config[ 'parser' ] );
        }
        else
        {
            $this->parser = new Parser();
        }
    }

    public function render( $view, array $vars = array(), $return = FALSE )
    {
        // Load Template Theme
        $template_vars[ 'theme' ] = $this->theme->active;

        // Load Template Metadata
        if( isset( $this->theme->active->settings[ 'metadata' ] ) )
        {
            $this->metadata->parse_settings( $this->theme->active->settings[ 'metadata' ] );
        }
        $template_vars[ 'metadata' ] = $this->metadata->render();

        // Load Template Assets
        $this->assets->add_paths( $this->theme->active->realpath );

        if( isset( $this->theme->active->settings[ 'assets' ] ) )
        {
            $this->assets->parse_settings( $this->theme->active->settings[ 'assets' ] );
        }

        // Load Template Navigations
        $template_vars[ 'navigations' ] = $this->navigation->render();

        // Load Template Breadcrumb
        $template_vars[ 'breadcrumb' ] = $this->breadcrumb->render();

        // Merge Vars Data
        $template_vars = array_merge( $template_vars, $vars );

        $partials = $this->theme->active->partials;

        if( strpos( $view, 'views' ) )
        {
            if( file_exists( $view ) )
            {
                $partials[ 'content' ] = $view;
            }

            $x_view = explode( 'views/', $view );

            if( file_exists( $this->theme->active->realpath . 'views/' . end( $x_view ) ) )
            {
                $partials[ 'content' ] = $this->theme->active->realpath . 'views/' . end( $x_view );
            }
        }
        elseif( file_exists( $view ) )
        {
            $partials[ 'content' ] = $view;
        }

        if( ! isset( $partials[ 'content' ] ) )
        {
            throw new \RuntimeException( 'Unable to load the requested view file: ' . $view );
        }

        $template_vars[ 'partials' ] = new \stdClass();

        foreach( $partials as $partial => $filepath )
        {
            if( file_exists( $filepath ) )
            {
                $partial_content = $this->parser->parse_source_code(  file_get_contents( $filepath ), $template_vars );

                $DOM = new \DOMDocument();
                @$DOM->loadHTML($partial_content);
                $DOM->preserveWhiteSpace = FALSE;

                $inline_js = $DOM->getElementsByTagName('script');
                $inline_style = $DOM->getElementsByTagName('style');

                // Fetch Inline JS
                if(! empty($inline_js))
                {
                    foreach($inline_js as $item)
                    {
                        $this->assets->inline_js($item->nodeValue);
                    }

                    $partial_content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $partial_content);
                }

                // Fetch Inline Style
                if(! empty($inline_style))
                {
                    foreach($inline_style as $item)
                    {
                        $this->assets->inline_css($item->nodeValue);
                    }

                    $partial_content = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $partial_content);
                }

                $template_vars[ 'partials' ]->{$partial} = $partial_content;
            }
        }

        $template_vars[ 'assets' ] = $this->assets->render();

        if( file_exists( $this->theme->active->layout ) )
        {
            $output = $this->parser->parse_source_code( file_get_contents( $this->theme->active->layout ), $template_vars );

            if( $return === TRUE )
            {
                return $output;
            }
            else
            {
                $this->output->set_content_type( 'text/html' );
                $this->output->set_output( $output );
            }
        }
        else
        {
            $layout = empty( $this->theme->active->layout ) ? 'theme.tpl' : $this->theme->active->layout;
            throw new \RuntimeException( 'Unable to load the requested template file: ' . $layout );
        }
    }
}