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
class Navigation extends Drivers
{
    /**
     * Class Config
     *
     * @access  public
     *
     * @type    $this->config
     */
    public $config;
    
    /**
     * Cached Flag
     *
     * @access  protected
     * @type    bool
     */
    protected $_is_cached = FALSE;

    /**
     * Cached Filename
     *
     * @access  protected
     * @type    string
     */
    protected $_cached_filename = NULL;

    /**
     * Render Navigation Cache
     *
     * @param   bool  $active
     *
     * @access  public
     * @return  Navigations
     */
    public function set_cache($active = FALSE)
    {
        global $system;

        $this->_is_cached = $active;
        $this->_cached_filename = 'navigations-'.$system->active['language'];

        if($app = @$system->active['app'])
        {
            $this->_cached_filename = $app->parameter.'-'.$this->_cached_filename;
        }

        return $this;
    }

    /**
     * Render Navigation
     *
     * @access  public
     * @return  string
     */
    public function render()
    {
        if($this->_is_cached === TRUE)
        {
            if($cache = $this->_system->cache->file->get($this->_cached_filename) !== FALSE)
            {
                return $cache;
            }

            if($cache = $this->_render_structure())
            {
                $this->_system->cache->file->save($this->_cached_filename, $cache);
            }

            return $cache;
        }
        else
        {
            return $this->_render_structure();
        }
    }

    /**
     * Render Navigation Structure
     *
     * @access  protected
     * @return  string
     */
    protected function _render_structure()
    {
        \O2System::Loader()->helper('html');

        $positions = \O2System::$registry->navigations->get_positions();

        if($positions)
        {
            foreach($positions as $position)
            {
                $structure[$position] = $this->_render_position($position);
            }

            return $structure;
        }

        return FALSE;
    }

    /**
     * Render Navigation Position
     *
     * @param   string  $position
     * @param   int     $id_parent
     *
     * @access  protected
     * @return  string
     */
    protected function _render_position($position, $id_parent = 0)
    {
         $navigations = \O2System::$registry->navigations->get_rows(['position' => $position, 'id_parent' => $id_parent]);

         if(count($navigations) == 0)
         {
            return NULL;
         }

         $html = '';

         if($id_parent === 0)
         {
            $html.= html('ul', ['id' => $position.'-menu', 'class' => $position.'-menu']);
         }
         else
         {
            $html.= html('ul');
         }

         foreach($navigations as $navigation)
         {
            $html.= html('li', ['id' => $position.'-menu-'.$navigation->id, 'class' => $navigation->css_class, 'style' => $navigation->css_style]);
                $html.= html('a', ['href' => $navigation->url_link, 'title' => $navigation->description]);

                    if(! empty($navigation->css_icon))
                    {
                        $html.= html('i', ['class' => $navigation->css_icon], '/i');
                    }
                    elseif(! empty($navigation->image))
                    {
                        $html.= img($navigation->image);
                    }

                    $html.= html('span', ['class' => 'title'], $navigation->label);

                $html.= html('/a');

                if($navigation->show_childrens === 'SHOW')
                {
                    $html.= $this->_render_position($position, $navigation->id);
                }

            $html.= html('/li');
         }

         $html.= html('/ul');

         return $html;
    }
}