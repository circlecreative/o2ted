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
 * Template Navigation Driver Class
 *
 * @package        O2System
 * @subpackage     libraries/Template/drivers
 * @category       driver class
 * @author         Circle Creative Dev Team
 * @link
 */
class Breadcrumb extends Drivers
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
     * Breadcrumb Segments
     *
     * @access  protected
     * @type    array
     */
    protected $_segments = array();

    /**
     * Breadcrumb Links
     *
     * @access  protected
     * @type    array
     */
    protected $_links = array();

    /**
     * Active URL
     *
     * @access  protected
     * @type    string
     */
    protected $_active_url = NULL;

    /**
     * Driver Class Constructor
     *
     * @access  public
     * @return  void
     */
    public function initialize()
    {
        // Get Controller Instance
        $controller =& get_instance();
        $controller->load->helpers(['string', 'html']);

        $this->_segments = $controller->uri->segments;

        // Filter Breadcrumb Segments
        $filter_segments = array();

        if($controller->active->offsetExists('app'))
        {
            array_push($filter_segments, $controller->active['app']->parameter);
        }

        // Set Breadcrumb Segments
        $this->_segments = array_values( array_diff($this->_segments, $filter_segments) );

        // Set Active URL
        $this->_active_url = base_url(implode('/', $controller->uri->segments) . config_item('url_suffix'));

        // Set Breadcrumb Home Link
        $this->set_home('Home', base_url());

        // Build Breadcrumb Links
        $this->_build_links();
    }

    /**
     * Build Links
     *
     * Build breadcrumb links array
     *
     * @access  protected
     * @return  void
     */
    protected function _build_links()
    {
        if(! empty($this->_segments))
        {
            for($i = 0; $i < count($this->_segments); $i++)
            {
                $this->_links[$this->_segments[$i]] = new \stdClass();
                $this->_links[$this->_segments[$i]]->label = ucwords( str_readable($this->_segments[$i]) );
                $this->_links[$this->_segments[$i]]->url = str_replace('/'.config_item('url_suffix'), '', base_url( implode('/', array_slice($this->_segments, -count($this->_segments)-$i, $i+1)) . config_item('url_suffix')))    ;
                $this->_links[$this->_segments[$i]]->active = (bool) $this->_is_active($this->_links[$this->_segments[$i]]->url);

                if($this->_links[$this->_segments[$i]]->active)
                {
                    if($label = $this->_parent->metadata->page_title)
                    {
                        $this->_links[$this->_segments[$i]]->label = $this->_parent->metadata->page_title;
                    }
                }
            }
        }
    }

    /**
     * Set Home
     *
     * Set Home Links
     *
     * @param   string  $label  Home Link Label
     * @param   string  $url    Home Link URL
     *
     * @access  public
     * @return  void
     */
    public function set_home($label, $url = NULL)
    {
        $url = empty($url) ? base_url() : $url;

        if( config_item('url_suffix') !== '' AND strpos($url, config_item('url_suffix')) === FALSE)
        {
            $url = $url . config_item('url_suffix');
        }

        $this->_links['home'] = new \stdClass();
        $this->_links['home']->label = $label;
        $this->_links['home']->url = str_replace('/'.config_item('url_suffix'), '', $url);

        $this->_links['home']->active = (bool) $this->_is_active($url);
    }

    /**
     * Is Active
     *
     * Check if the url is active
     *
     * @param   string  $url    String of URL
     *
     * @access  protected
     * @return  bool
     */
    protected function _is_active($url)
    {
        if($url === $this->_active_url)
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Render Breadcrumb
     *
     * @access  public
     * @return  string
     */
    public function render()
    {
        $html = html('ul', ['class' => 'breadcrumb']);
        foreach($this->_links as $link)
        {
            if($link->active)
            {
                $html.= html('li', ['class' => 'active']);
            }
            else
            {
                $html.= html('li');
            }

            $html.= html('a', ['href' => $link->url], $link->label);

            $html.= html('/li');
        }
        $html.= html('/ul');

        return $html;
    }
}