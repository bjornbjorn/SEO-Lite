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
	
	function Seo_lite()
	{		
        $this->EE =& get_instance(); // Make a local reference to the ExpressionEngine super object

        $entry_id = $this->get_param('entry_id');
        $site_id = $this->get_param('site_id', $this->EE->config->item('site_id'));

        $use_last_segment = ($this->get_param('use_last_segment') == 'yes' || $this->get_param('use_last_segment') == 'y');
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

        $canonical_url = $this->get_canonical_url($ignore_last_segments);

        if($use_last_segment)
        {
            $url_title = $this->get_url_title_from_segment($ignore_last_segments);
        }

        $got_values = FALSE;
        if($entry_id || $url_title)
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
                            }
                        }
                    }
                }
            }

            $this->EE->db->select('channel_titles.entry_id, channel_titles.title as original_title, url_title, seolite_content.title as seo_title, default_keywords, default_description, default_title_postfix, keywords, description, seolite_config.template');
            $this->EE->db->from('channel_titles');
            $where = array('channel_titles.site_id' => $site_id);
            if($url_title)
            {
                $where['url_title'] = $url_title;
            }
            else
            {
                $where['channel_titles.entry_id'] = $entry_id;
            }
            $this->EE->db->where($where);
            $this->EE->db->join('seolite_config', 'seolite_config.site_id = channel_titles.site_id');
            $this->EE->db->join('seolite_content', 'seolite_content.entry_id = channel_titles.entry_id', 'left');

            $q = $this->EE->db->get();

            if($q->num_rows() > 0)
            {
                $seolite_entry = $q->row();

                $vars = array(
                    'title' => htmlspecialchars($this->get_preferred_value($seolite_entry->seo_title, $seolite_entry->original_title, $default_title), ENT_QUOTES), // use SEO title over original if it exists, then original, then default_title from parameter
                    'meta_keywords' => htmlspecialchars($this->get_preferred_value($seolite_entry->keywords, $seolite_entry->default_keywords, $default_keywords), ENT_QUOTES),
                    'meta_description' => htmlspecialchars($this->get_preferred_value($seolite_entry->description, $seolite_entry->default_description, $default_description), ENT_QUOTES),
                );
                $got_values = TRUE;
            }
        }

        if(!$got_values)
        {           
            // no specific entry lookup, but we still want the config
            $q = $this->EE->db->get_where('seolite_config', array('seolite_config.site_id' => $site_id));
            $seolite_entry = $q->row();

            $vars = array(
                'title' => htmlspecialchars($default_title, ENT_QUOTES),
                'meta_keywords' => htmlspecialchars($this->get_preferred_value($default_keywords ,$seolite_entry->default_keywords), ENT_QUOTES) ,
                'meta_description' => htmlspecialchars($this->get_preferred_value($default_description, $seolite_entry->default_description), ENT_QUOTES),
            );
        }

        if($vars['title'] != '')
        {
            $title_postfix .= $seolite_entry->default_title_postfix;
        }

        $vars['title'] = $title_prefix.$vars['title'].$title_postfix.($title_separator?' '.$title_separator.' ':'');
        $vars['canonical_url'] = $canonical_url;

        $tagdata = $seolite_entry->template;

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
        return $this->return_data;
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


    private function get_canonical_url($ignore_last_segments)
    {
        if(!$ignore_last_segments)
        {
            $canonical_url = $this->EE->functions->fetch_current_uri();

            if($this->is_last_segment_pagination_segment())
            {
                $canonical_url = substr($canonical_url,0, strrpos($canonical_url,'/P'));
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