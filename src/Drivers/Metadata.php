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
// ------------------------------------------------------------------------

class Metadata extends Drivers
{
	/**
	 * Metadata Config
	 *
	 * @access  public
	 *
	 * @type    array
	 */
	protected $_config = array(
		'charset'   => 'UTF-8',
		'separator' => '-',
		'valid'     => array(
			'tags'       => array(
				'abstract',
				'author',
				'category',
				'classification',
				'copyright',
				'coverage',
				'description',
				'distribution',
				'doc-class',
				'doc-rights',
				'doc-type',
				'downloadoptions',
				'expires',
				'designer',
				'directory',
				'generator',
				'googlebot',
				'identifier-url',
				'keywords',
				'language',
				'mssmarttagspreventparsing',
				'name',
				'owner',
				'progid',
				'rating',
				'refresh',
				'reply-to',
				'resource-type',
				'revisit-after',
				'robots',
				'summary',
				'title',
				'topic',
				'url',
			),
			'http_equiv' => array(
				'cache-control',
				'content-language',
				'content-type',
				'date',
				'expires',
				'last-modified',
				'location',
				'refresh',
				'set-cookie',
				'window-target',
				'pragma',
				'page-enter',
				'page-exit',
				'x-ua-compatible',
			),
		),
	);

	/**
	 * Metadata Variables
	 *
	 * @access protected
	 *
	 * @type    array
	 */
	protected $_vars = array();

	/**
	 * Browser Title
	 *
	 * @access  protected
	 *
	 * @access  public
	 *
	 * @type    string
	 */
	protected $_browser_title = array();

	/**
	 * Page Title
	 *
	 * @access  protected
	 *
	 * @type    string
	 */
	protected $_page_title = array();

	// ------------------------------------------------------------------------

	/**
	 * Reset Metadata
	 *
	 * @access  public
	 */
	final public function reset()
	{
		$this->_vars = array();
	}
	// ------------------------------------------------------------------------

	/**
	 * Set Metadata Variables
	 *
	 * @access public
	 *
	 * @param   array $tags     List of Metadata Tags Variables
	 * @param   bool  $override Replace previous metadata tag
	 */
	public function set_tags( $tags = array(), $override = FALSE )
	{
		foreach ( $tags as $tag => $content )
		{
			if ( in_array( $tag, $this->_config[ 'valid' ][ 'tags' ] ) )
			{
				if ( $override === TRUE OR empty ( $this->_vars[ $tag ] ) )
				{
					$this->_vars[ $tag ] = $content;
				}
				elseif ( $override === FALSE )
				{
					$separator = ( $tag === 'title' ? ' ' . $this->_config[ 'separator' ] . ' ' : ', ' );
					$this->_vars[ $tag ] = implode( $separator, array( $this->_vars[ $tag ], $content ) );
				}
			}
		}
	}
	// ------------------------------------------------------------------------


	/**
	 * Parse Metadata Settings
	 *
	 * @access  public
	 *
	 * @param array $settings Parse Metadata Settings
	 */
	public function parse_settings( array $settings = array() )
	{
		foreach ( $settings as $key => $value )
		{
			$this->set_tags( array( $key => $value ) );
		}
	}

	/**
	 * Set Metadata HTTP Equiv
	 *
	 * @access public
	 *
	 * @param   array $tags     List of HTTP Equiv Variables
	 * @param   bool  $override Override previous page title
	 */
	public function http_equiv( $tags = array(), $override = FALSE )
	{
		foreach ( $tags as $tag => $content )
		{
			if ( in_array( $tag, $this->_config[ 'valid' ][ 'http_equiv' ] ) )
			{
				if ( $override === TRUE OR ! isset ( $this->_vars[ $tag ] ) )
				{
					$this->_vars[ $tag ] = $content;
				}
				else if ( $override === 'implode' )
				{
					$this->_vars[ $tag ] = implode( ', ', array( $this->_vars[ $tag ], $content ) );
				}
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Browser and Page Title
	 *
	 * @access public
	 *
	 * @param   string $title    Set Page and Browser Title
	 * @param   bool   $override Override previous page title
	 */
	public function set_title( $title, $override = FALSE )
	{
		if ( $title == '' ) return;

		$this->set_page_title( $title, $override );
		$this->set_browser_title( $title, $override );
		$this->set_tags( array( 'title' => $title ), $override );
	}

	/**
	 * Set Page Title
	 *
	 * @access public
	 *
	 * @param   string $title    Page Header Title
	 * @param   bool   $override Override previous page title
	 */
	public function set_page_title( $title, $override = FALSE )
	{
		if ( $override === TRUE )
		{
			$this->_page_title = array();
		}

		if ( is_array( $title ) )
		{
			$this->_page_title = array_merge( $this->_page_title, $title );
		}
		else
		{
			array_push( $this->_page_title, $title );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Set Browser Title
	 *
	 * @access public
	 *
	 * @param   string $title    Page Browser Title
	 * @param   bool   $override Override previous page title
	 */
	public function set_browser_title( $title, $override = FALSE )
	{
		if ( $override === TRUE )
		{
			$this->_browser_title = array();
		}

		if ( is_array( $title ) )
		{
			$this->_browser_title = array_merge( $this->_browser_title, $title );
		}
		else
		{
			array_push( $this->_browser_title, $title );
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Render Metadata
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function render()
	{
		$separator = ' ' . $this->_config[ 'separator' ] . ' ';

		$output[ 'page_title' ] = implode( $separator, $this->_page_title );
		$output[ 'browser_title' ] = implode( $separator, $this->_browser_title );

		$this->_vars[ 'title' ] = $output[ 'browser_title' ];

		$output[ 'tags' ][] = '<meta charset="' . $this->_config[ 'charset' ] . '">';

		if ( count( $this->_vars ) > 0 )
		{
			foreach ( $this->_vars as $tag => $content )
			{
				$content = ( is_array( $content ) ? implode( ',', $content ) : $content );
				$output[ 'tags' ][] = '<meta name="' . $tag . '" content="' . $content . '">';
			}
		}

		$output[ 'tags' ] = implode( "\n", $output[ 'tags' ] );

		return new \ArrayObject( $output, \ArrayObject::ARRAY_AS_PROPS );
	}
}