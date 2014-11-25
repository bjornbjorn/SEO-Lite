<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * One calorie SEO module, no sugar added!
 *
 * @package		Seo_lite
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		bjorn
 * @link		http://ee.bybjorn.com/seo_lite
 */
class Seo_lite {

	var $return_data;
	private $tag_prefix;
    
	public function Seo_lite() // backwards compatible __construct() won't work
	{
        return $this->perform();
	}

    /**
     * When using SEO Lite as a tag pair, e.g.:
     *
     * {exp:seo_lite:pair entry_id="{entry_id}"}
     *      <h3>Meta description for entry_id={entry_id}:</h3>
     *      {meta_description}
     * {/exp:seo_lite:pair}
     *
     */
    public function pair()
    {
        return $this->perform();
    }

    private function perform()
    {
        $this->EE =& get_instance(); // Make a local reference to the ExpressionEngine super object

        $entry_id = $this->get_param('entry_id');
        $site_id = $this->get_param('site_id', $this->EE->config->item('site_id'));
        $channel = $this->EE->TMPL->fetch_param('channel');

        $use_last_segment = ($this->get_param('use_last_segment') == 'yes' || $this->get_param('use_last_segment') == 'y');
        $this->tag_prefix = $this->get_param('tag_prefix');
        $url_title = $this->get_param('url_title');
        $default_title = $this->get_param('default_title');    // override default title
        $default_keywords = $this->get_param('default_keywords');
        $default_description = $this->get_param('default_description');
        $title_prefix = $this->get_param('title_prefix');
        $title_postfix = $this->get_param('title_postfix');
        $title_separator = $this->get_param('title_separator');
        $title_override = $this->get_param('title_override');
        $friendly_segments = ($this->get_param('friendly_segments') == 'yes' || $this->get_param('friendly_segments') == 'y');
        $ignore_last_segments = $this->get_param('ignore_last_segments', FALSE);
        $category_url_title = $this->get_param('category_url_title');

        $canonical_url = $this->get_param('canonical',$this->get_canonical_url($ignore_last_segments));

        if($use_last_segment)
        {
            $url_title = $this->get_url_title_from_segment($ignore_last_segments);
        }

        $got_values = FALSE;

        if($category_url_title)
        {
            $this->EE->db->select('cat_name, cat_description, default_keywords, default_description, default_title_postfix, template')->from('categories')->where(array('cat_url_title' => $category_url_title, 'categories.site_id' => $site_id));
            $this->EE->db->join('seolite_config', 'seolite_config.site_id = categories.site_id');
            $q = $this->EE->db->get();
            if($q->num_rows() > 0)
            {
                $seolite_entry = $q->row();
                $tagdata = $this->get_tagdata($seolite_entry->template);
                $tagdata = $this->clearExtraTags($tagdata); // no {extra} values for categories for now ..
                $vars = array(
                    $this->tag_prefix.'title' => htmlspecialchars($this->get_preferred_value($seolite_entry->cat_name, $default_title), ENT_QUOTES), // use SEO title over original if it exists, then original, then default_title from parameter
                    $this->tag_prefix.'meta_keywords' => htmlspecialchars($this->get_preferred_value($seolite_entry->default_keywords, $default_keywords), ENT_QUOTES),
                    $this->tag_prefix.'meta_description' => htmlspecialchars($this->get_preferred_value($seolite_entry->cat_description, $seolite_entry->default_description, $default_description), ENT_QUOTES),
                );

                $got_values = TRUE;
            }
        }
        else if($entry_id || $url_title)
        {
            if($url_title && !$entry_id)    // if we're retrieving by url_title and not entry_id
            {
                $pages = $this->EE->config->item('site_pages');
                if(isset($pages[$site_id]) && isset($pages[$site_id]['uris']))
                {
                    $current_uri_string = $this->EE->uri->uri_string();
                    if($current_uri_string != '')
                    {
                        foreach($pages[$site_id]['uris'] as $page_entry_id => $page_uri)
                        {
                            if(trim($page_uri,'/') == $current_uri_string)
                            {
                                $entry_id = $page_entry_id;
                                $url_title = FALSE; // pages will override - found entry_id so ignore url_title from now
                                $canonical_url = $this->get_canonical_url($ignore_last_segments, $page_uri);
                            }
                        }
                    }
                }
            }

            $table_name = 'seolite_content';
            $where = array('t.site_id' => $site_id);
            if($url_title)
            {
                $where['url_title'] = $url_title;
            }
            else
            {
                $where['t.entry_id'] = $entry_id;
            }
            // -------------------------------------------
            // Allows one to pull from another table
            //
            // Params sent in:
            // - The table name
            //
            // Return data
            //
            // May be an array containing 'table_name' (new name of table to pull from)
            // -------------------------------------------
            if ($this->EE->extensions->active_hook('seo_lite_fetch_data') === TRUE)
            {
                $hook_result = $this->return_data = $this->EE->extensions->call('seo_lite_fetch_data', $where, $table_name);
                if($hook_result && isset($hook_result['table_name'])) {
                    $table_name = $hook_result['table_name'];
                }
                if($hook_result && isset($hook_result['where'])) {
                    $where = $hook_result['where'];
                }

                if ($this->EE->extensions->end_script === TRUE) return;
            }

            $select_str = 't.entry_id, t.title as original_title, url_title, '.$table_name.'.title as seo_title, default_keywords, default_description, default_title_postfix, keywords, description, seolite_config.template';
            if($this->EE->config->item('seolite_extra')) {
                $select_str .= ',d.*';
                $this->EE->db->select($select_str);
                $this->EE->db->from('channel_titles t, channel_data d');
                $this->EE->db->where('t.entry_id', 'd.entry_id', FALSE);
            } else {
                $this->EE->db->select($select_str);
                $this->EE->db->from('channel_titles t');
            }

            $this->EE->db->where($where);
            $this->EE->db->join('seolite_config', 'seolite_config.site_id = t.site_id');
            $this->EE->db->join($table_name, $table_name.'.entry_id = t.entry_id', 'left');

            if ($channel !== FALSE)
            {
                  $this->EE->db
                    ->join('channels', 't.channel_id = channels.channel_id')
                    ->where('channels.channel_name', $channel);
            }

            $q = $this->EE->db->get();

            if($q->num_rows() > 0)
            {
                $seolite_entry = $q->row();
                $entry_id = $seolite_entry->entry_id;

                $tagdata = $this->get_tagdata($seolite_entry->template);

                $vars = array(
                    $this->tag_prefix.'title' => htmlspecialchars($this->get_preferred_value($seolite_entry->seo_title, $seolite_entry->original_title, $default_title), ENT_QUOTES), // use SEO title over original if it exists, then original, then default_title from parameter
                    $this->tag_prefix.'meta_keywords' => htmlspecialchars($this->get_preferred_value($seolite_entry->keywords, $seolite_entry->default_keywords, $default_keywords), ENT_QUOTES),
                    $this->tag_prefix.'meta_description' => htmlspecialchars($this->get_preferred_value($seolite_entry->description, $seolite_entry->default_description, $default_description), ENT_QUOTES),
                );

                if($this->EE->config->item('seolite_extra')) {
                    $seolite_extra_config = $this->EE->config->item('seolite_extra');
                    $channel_id = $q->row('channel_id');

                    if($channel_id && isset($seolite_extra_config[$channel_id])) {

                        foreach($seolite_extra_config[$channel_id] as $extra_field_name => $field_info) {
                            $field_value_key = 'field_id_'.$field_info['field_id'];
                            $field_value = $seolite_entry->$field_value_key;

                            if(isset($field_info['field_type'])) {
                                switch($field_info['field_type']) {
                                    case 'text':
                                        $field_value = trim(strip_tags($field_value));

                                        if(isset($field_info['max_length'])) {
                                            $field_value = substr($field_value, 0, $field_info['max_length']) . ' ...';
                                        }
                                        $field_value = htmlentities($field_value);
                                        break;


                                    case 'photo_frame':
                                        $photo_frame_id = $field_value; // photo_frame stores exp_photo_frame.entry_id as field_value
                                        $q = $this->EE->db->from('photo_frame')->where('entry_id', $photo_frame_id)->get();
                                        if($q->num_rows() > 0) {
                                            $field_value = $q->row('file');
                                            $field_value = $this->get_url_from_filedir_id($field_value);
                                        } else {
                                            $field_value = '';
                                        }

                                        break;

                                    case 'assets':
                                        /**
                                         * Older versions of Assets will store {filedir_1} etc. in the field_value field,
                                         * if we have this we don't need to look up the assets selection so just fall back
                                         * to regular file.
                                         */
                                        if(strpos($field_value, '{filedir_') === FALSE) {
                                            $this->EE->db->select('f.filedir_id, f.file_name, fo.full_path');
                                            $this->EE->db->from('assets_files f');
                                            $this->EE->db->join('assets_selections s', 's.file_id = f.file_id');
                                            $this->EE->db->join('assets_folders fo', 'fo.folder_id = f.folder_id', 'LEFT');
                                            $this->EE->db->where('s.field_id', $field_info['field_id']);
                                            $this->EE->db->where('s.entry_id', $entry_id);
                                            $this->EE->db->where('s.sort_order', 0);
                                            $q = $this->EE->db->get();

                                            if($q->num_rows() === 0)
                                            {
                                                break;
                                            }

                                            $filedir_id = $q->row('filedir_id');
                                            $file_name = $q->row('file_name');
                                            $assets_subfolder = $q->row('full_path');
                                            $field_value = '{filedir_'.$filedir_id.'}'.$assets_subfolder.$file_name;
                                        }

                                        // Fall through
                                    case 'file':

                                        /**
                                         * If string contains a {filedir_x} reference we replace it with the correct url
                                         */
                                        $field_value = $this->get_url_from_filedir_id($field_value);
                                        break;
                                }

                            }
                            $vars[$this->tag_prefix.'extra:'.$extra_field_name] = $field_value;
                        }
                    } else {
                        $tagdata = $this->clearExtraTags($tagdata);// extra array specified but no {extra} values for this channel so clear out those
                    }
                }

                $got_values = TRUE;
            }
        }

        if(!$got_values)
        {
            // no specific entry lookup, but we still want the config
            $q = $this->EE->db->get_where('seolite_config', array('seolite_config.site_id' => $site_id));
            $seolite_entry = $q->row();
            $tagdata = $this->get_tagdata($seolite_entry->template);
            $tagdata = $this->clearExtraTags($tagdata);

            $vars = array(
                $this->tag_prefix.'title' => htmlspecialchars($default_title, ENT_QUOTES),
                $this->tag_prefix.'meta_keywords' => htmlspecialchars($this->get_preferred_value($default_keywords ,$seolite_entry->default_keywords), ENT_QUOTES) ,
                $this->tag_prefix.'meta_description' => htmlspecialchars($this->get_preferred_value($default_description, $seolite_entry->default_description), ENT_QUOTES),
            );
        }

        if($vars[$this->tag_prefix.'title'] != '')
        {
          if ( $this->EE->TMPL->fetch_param('title_postfix', FALSE) === FALSE)
          {
            $title_postfix = str_replace("&nbsp;"," ",$seolite_entry->default_title_postfix);
          }
        }

        $vars[$this->tag_prefix.'entry_title'] = $vars[$this->tag_prefix.'title'];
        $vars[$this->tag_prefix.'title'] = $title_prefix.$vars[$this->tag_prefix.'title'].$title_postfix.($title_separator?' '.$title_separator.' ':'');
        $vars[$this->tag_prefix.'canonical_url'] = $canonical_url;

        // special case for soft-hypen - we strip it entirely, if we were to use html_entity_decode
        // on it as well it would display in the browser title
        $vars[$this->tag_prefix.'title'] = str_replace("&amp;shy;", "", $vars[$this->tag_prefix.'title']);
        $vars[$this->tag_prefix.'title'] = html_entity_decode($vars[$this->tag_prefix.'title']);

        // segment variables are not parsed yet, so we do it ourselves if they are in use in the seo lite template
        if(preg_match_all('/\{segment_(\d)\}/i', $tagdata, $matches))
        {
            $word_separator_replace = ($this->EE->config->item('word_separator') == 'underscore' ? '_' : '-');
            $tags = $matches[0];
            $segment_numbers = $matches[1];
            for($i=0; $i < count($tags); $i++)
            {
                $tag = $tags[$i];
                $segment_value = $friendly_segments ? ucfirst(str_replace($word_separator_replace, ' ', $this->EE->uri->segment($segment_numbers[$i]))) : $this->EE->uri->segment($segment_numbers[$i]);
                $tagdata = str_replace($tag, $segment_value, $tagdata);
            }
        }

        /**
         * Hard override
         */
        if($title_override)
        {
            $tagdata = preg_replace("~<title>([^<]*)</title>~",'<title>'.$title_override.'</title>', $tagdata );
        }

        $this->return_data = $this->EE->TMPL->parse_variables_row($tagdata, $vars);

        // -------------------------------------------
        // Allows one to modify the returned SEO Lite header template
        //
        // Params sent in:
        // - Parsed tagdata (the template)
        // - Array: The SEO Lite / Entry variables collected ( [tag_prefix:title] etc.)
        // - The tag prefix used (needed to look up the var array reliably, but is often empty)
        // - Array: The SEO Lite tag parameters used (any kind of params can be added to SEO Lite, even ones SEO Lite don't recognize)
        // - A reference to the Seo_lite class (mod.seo_lite.php)
        //
        // The returned html will replace the data returned by the {exp:seo_lite} tag.
        //
        // Remember the last_call variable in case other add ons than yours use this hook: return $html.$this->EE->extensions->last_call;
        // -------------------------------------------
        if ($this->EE->extensions->active_hook('seo_lite_template') === TRUE)
        {
            $this->return_data = $this->EE->extensions->call('seo_lite_template', $this->return_data, $vars, $this->tag_prefix, $this->EE->TMPL->tagparams, $this);
            if ($this->EE->extensions->end_script === TRUE) return;
        }

        return $this->return_data;
    }

    /**
     * Will clear all {extra:blabla} tags from the tagdata
     *
     * @param $tagdata
     */
    private function clearExtraTags($tagdata)
    {
        return preg_replace("~\{".$this->tag_prefix."extra:[^\}]*\}~",'', $tagdata );
    }
    
    /**
     * This function will get the tagdata if SEO lite is used as a tag pair, or return back
     * the template shipped to it
     *
     * @param $default_template default seo lite template
     * @return html/tagdata
     */
    private function get_tagdata($default_template) {
        $tagdata = $this->EE->TMPL->tagdata;
        if( empty($tagdata))
        {
            $tagdata = $default_template;
        }

        return $tagdata;
    }


    /**
     * Get full url to a file from {filedir_id}/blabla/file.jpeg string
     *
     * @param $str
     */
    private function get_url_from_filedir_id($str)
    {
        if (preg_match('/^{filedir_(\d+)}/', $str, $matches))
        {
            $filedir_id = $matches[1];
            $this->EE->load->model('file_upload_preferences_model');
            $upload_dest_info = $this->EE->file_upload_preferences_model->get_file_upload_preferences(FALSE, $filedir_id);
            $str = str_replace('{filedir_'.$filedir_id.'}', $upload_dest_info['url'], $str);
        }

        return $str;
    }

    /**
     * Get the last segment from the URL (ignore pagination in url)
     *
     * @return last segment
     */
    private function get_url_title_from_segment($ignore_segments=FALSE)
    {
        $segment_count = $this->EE->uri->total_segments();
        if(!$ignore_segments)
        {
            $last_segment_absolute = $this->EE->uri->segment($segment_count);
            $last_segment = $last_segment_absolute;
        }
        else
        {
            $fetch_segment = $segment_count - $ignore_segments;
            if($segment_count<1)
            {
                $segment_count = 1;
            }
            $last_segment = $this->EE->uri->segment($fetch_segment);
        }

        if($this->is_last_segment_pagination_segment())
        {
            $last_segment_id = $segment_count-1;
            $last_segment = $this->EE->uri->segment($last_segment_id);
        }


        return $last_segment;
    }

    /**
     * @return void
     */
    private function is_last_segment_pagination_segment()
    {
        $segment_count = $this->EE->uri->total_segments();
        $last_segment = $this->EE->uri->segment($segment_count);
        if(substr($last_segment,0,1) == 'P') // might be a pagination page indicator
        {
            $end = substr($last_segment, 1, strlen($last_segment));
            return ((preg_match( '/^\d*$/', $end) == 1));
        }

        return FALSE;
    }

	private function get_request_uri() 
	{
		if(!isset($_SERVER['REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
			if($_SERVER['QUERY_STRING']) {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
		return $_SERVER['REQUEST_URI'];
	}


    private function get_canonical_url($ignore_last_segments, $page_uri = FALSE)
    {
        // Check if we're wanting to strip out the pagination segment from the URL
        $site_id = $this->get_param('site_id', $this->EE->config->item('site_id'));
        $q = $this->EE->db->get_where('seolite_config', array('seolite_config.site_id' => $site_id));
        $seolite_entry = $q->row();
        $include_pagination_in_canonical = $seolite_entry->include_pagination_in_canonical;
        
        if(!$ignore_last_segments)
        {
            $segments = explode('/', $this->get_request_uri());
            $segment_count = count($segments);

            $append_to_url = FALSE;

            if($segment_count > 0)
            {
                $last_segment = $segments[$segment_count-1];
                if(substr($last_segment,0,1) == 'P') // might be a pagination page indicator
                {
                    $end = substr($last_segment, 1, strlen($last_segment));
                    if((preg_match( '/^\d*$/', $end)) && $end > 0)  // if it's a pagination segment and the page is > 0 we append the page number
                    {
                        $append_to_url = $last_segment;
                    }
                }
            }

            $canonical_url = '';

            // if we got a page_uri, we use that as the blueprint

            if($page_uri)
            {
                $canonical_url = $this->EE->functions->create_url($page_uri) . (substr($page_uri, strlen($page_uri)-1) == '/' ? '/' : '');

                if($append_to_url)
                {
                    $canonical_url = $canonical_url . (substr($canonical_url, strlen($canonical_url)-1) == '/' ? $append_to_url : '/' . $append_to_url);
                }
            }
            else
            {
                $canonical_url = $this->EE->functions->fetch_current_uri();
            }
        }
        else
        {
            $segs = $this->EE->uri->segment_array();
            $canonical_url_segments = '';
            $total_segments = count($segs);
            for($i=1; $i<$total_segments && $i < ($total_segments-$ignore_last_segments); $i++)
            {
                $canonical_url_segments .= $segs[$i];
            }

            $canonical_url = $this->EE->functions->create_url($canonical_url_segments);
        }
        
        if ($include_pagination_in_canonical == "n") {
            $canonical_url = preg_replace("/P(\d+)$/", "", $canonical_url);
        }
        
        return $canonical_url;
    }

    /**
     * Get a value by priority
     *
     * @param  $val1 want this the most
     * @param  $val2 then this
     * @param  $val3 finally if none of the two others are available choose this
     * @return the first available value
     */
    private function get_preferred_value($val1, $val2, $val3='')
    {
        if(!empty($val1))
        {
            return $val1;
        }
        if(!empty($val2))
        {
            return $val2;
        }
        return $val3;
    }


	/**
     * Helper function for getting a parameter
	 */		 
	private function get_param($key, $default_value = '')
	{
		$val = $this->EE->TMPL->fetch_param($key);

        // since EE will remove space at the beginning of a parameter people are using &nbsp; or &#32;
        // we replace these with a standard space here
        $val = str_replace(array('&nbsp;','&#32;'), array(' ',' '), $val);

		if($val == '') {
			return $default_value;
		}
		return $val;
	}

	/**
	 * Helper funciton for template logging
	 */
	private function error_log($msg)
	{		
		$this->EE->TMPL->log_item("seo_lite ERROR: ".$msg);		
	}		
}

/* End of file mod.seo_lite.php */ 
/* Location: ./system/expressionengine/third_party/seo_lite/mod.seo_lite.php */
