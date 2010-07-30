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

        $entry_id = $this->_get_param('entry_id');
        $site_id = $this->_get_param('site_id', $this->EE->config->item('site_id'));
        $url_title = $this->_get_param('url_title');
        if($entry_id || $url_title)
        {
            $this->EE->db->select('channel_titles.entry_id, channel_titles.title as original_title, url_title, seolite_content.title as seo_title, default_keywords, default_description, keywords, description, seolite_config.template');
            $this->EE->db->from('channel_titles');
            $where = array('channel_titles.site_id' => $site_id);
            if($url_title)
            {
                $where['url_title'] = $url_title;
            }
            else
            {
                $where['entry_id'] = $entry_id;
            }
            $this->EE->db->where($where);
            $this->EE->db->join('seolite_config', 'seolite_config.site_id = channel_titles.site_id');
            $this->EE->db->join('seolite_content', 'seolite_content.entry_id = channel_titles.entry_id', 'left');
            $q = $this->EE->db->get();
            $seolite_entry = $q->row();

            $vars = array(
                'title' => ($seolite_entry->seo_title == '') ? $seolite_entry->original_title : $seolite_entry->seo_title,  // use SEO title over original if it exists
                'meta_keywords' => $seolite_entry->keywords ? $seolite_entry->keywords : $seolite_entry->default_keywords,
                'meta_description' => $seolite_entry->description ? $seolite_entry->description : $seolite_entry->default_description,
            );

            $this->return_data = $this->EE->TMPL->parse_variables_row($seolite_entry->template, $vars);
            return $this->return_data;
        }
	}


	/**
     * Helper function for getting a parameter
	 */		 
	function _get_param($key, $default_value = '')
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
	function _error_log($msg)
	{		
		$this->EE->TMPL->log_item("seo_lite ERROR: ".$msg);		
	}		
}

/* End of file mod.seo_lite.php */ 
/* Location: ./system/expressionengine/third_party/seo_lite/mod.seo_lite.php */