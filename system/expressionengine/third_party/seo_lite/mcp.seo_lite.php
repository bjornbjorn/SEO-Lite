<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * One calorie SEO module, no sugar added!
 *
 * @package		Seo_lite
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		Bjørn Børresen
 * @link		http://wedoaddons.com/addons/seo-lite
 */
class Seo_lite_mcp 
{
	var $base;			// the base url for this module			
	var $form_base;		// base url for forms
	var $module_name = "seo_lite";	

	function Seo_lite_mcp( $switch = TRUE )
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance(); 
		$this->base	 	 = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;
		$this->form_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;

        // uncomment this if you want navigation buttons at the top
		$this->EE->cp->set_right_nav(array(
				'settings'			=> $this->base,
				'docs'	=> 'http://ee.bybjorn.com/seo_lite',
			));


		//  Onward!
		$this->EE->load->library('table');
		$this->EE->load->library('javascript');
		$this->EE->load->helper('form');
		$this->EE->lang->loadfile('seo_lite');
	}

	function index() 
	{
		$vars = array();

        $site_id = $this->EE->config->item('site_id');
        $config = $this->EE->db->get_where('seolite_config', array('site_id' => $site_id));

        if($config->num_rows() == 0) // we did not find any config for this site id, so just load any other
        {
            $config = $this->EE->db->get_where('seolite_config');
        }

		$vars['template'] = $config->row('template');
        $vars['default_description'] = $config->row('default_description');
        $vars['default_keywords'] = $config->row('default_keywords');
        $vars['default_title_postfix'] = $config->row('default_title_postfix');
        $vars['include_pagination_in_canonical'] = $config->row('include_pagination_in_canonical');

		return $this->content_wrapper('index', 'seo_lite_welcome', $vars);
	}
	
	function save_settings()
	{
		$template = $this->EE->input->post('seolite_template');
        $default_keywords = $this->EE->input->post('seolite_default_keywords');
        $default_description = $this->EE->input->post('seolite_default_description');
        $default_title_postfix = $this->EE->input->post('seolite_default_title_postfix');
        $include_pagination_in_canonical = $this->EE->input->post('seolite_include_pagination_in_canonical');

        $site_id = $this->EE->config->item('site_id');
        $config = $this->EE->db->get_where('seolite_config', array('site_id' => $site_id));

        $data_arr = array(
                'template' => $template,
                'default_keywords' => $default_keywords,
                'default_description' => $default_description,
                'default_title_postfix' => $default_title_postfix,
                'include_pagination_in_canonical' => $include_pagination_in_canonical,
            );

        if($config->num_rows() == 0)
        {
            $data_arr['site_id'] = $site_id;
            $this->EE->db->insert('seolite_config', $data_arr);
        }
        else
        {
            $this->EE->db->where('site_id', $site_id);
            $this->EE->db->update('seolite_config', $data_arr);
        }

		$this->EE->session->set_flashdata('message_success', lang('settings_saved'));
		$this->EE->functions->redirect($this->base);
	}

	
	function content_wrapper($content_view, $lang_key, $vars = array())
	{
		$vars['content_view'] = $content_view;
		$vars['_base'] = $this->base;
		$vars['_form_base'] = $this->form_base;

        // $this->EE->cp->set_variable was deprecated in 2.6
        if (version_compare(APP_VER, '2.6', '>=')) {
            $this->EE->view->cp_page_title = lang($lang_key);
        } else {
            $this->EE->cp->set_variable('cp_page_title', lang($lang_key));
        }

		$this->EE->cp->set_breadcrumb($this->base, lang('seo_lite_module_name'));
		return $this->EE->load->view('_wrapper', $vars, TRUE);
	}
	
}

/* End of file mcp.seo_lite.php */ 
/* Location: ./system/expressionengine/third_party/seo_lite/mcp.seo_lite.php */ 