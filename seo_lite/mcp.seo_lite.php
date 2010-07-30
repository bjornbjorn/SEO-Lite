<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * One calorie SEO module, no sugar added!
 *
 * @package		Seo_lite
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		bjorn
 * @link		http://ee.bybjorn.com/
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
		
		//  Onward!
		$this->EE->load->library('table');
		$this->EE->load->library('javascript');
		$this->EE->load->helper('form');
		
	}

	function index() 
	{
		$vars = array();

        $config = $this->EE->db->get('seolite_config');

		$vars['template'] = $config->row('template');
        $vars['default_description'] = $config->row('default_description');
        $vars['default_keywords'] = $config->row('default_keywords');

		return $this->content_wrapper('index', 'welcome', $vars);
	}
	
	function save_settings()
	{
		$template = $this->EE->input->post('seolite_template');
        $default_keywords = $this->EE->input->post('seolite_default_keywords');
        $default_description = $this->EE->input->post('seolite_default_description');

        $this->EE->db->update('seolite_config', array(
            'template' => $template,
            'default_keywords' => $default_keywords,
            'default_description' => $default_description,
        ));


		$this->EE->session->set_flashdata('message_success', lang('settings_saved'));
		$this->EE->functions->redirect($this->base);
	}

	
	function content_wrapper($content_view, $lang_key, $vars = array())
	{
		$vars['content_view'] = $content_view;
		$vars['_base'] = $this->base;
		$vars['_form_base'] = $this->form_base;
		$this->EE->cp->set_variable('cp_page_title', lang($lang_key));
		$this->EE->cp->set_breadcrumb($this->base, lang('seo_lite_module_name'));

		return $this->EE->load->view('_wrapper', $vars, TRUE);
	}
	
}

/* End of file mcp.seo_lite.php */ 
/* Location: ./system/expressionengine/third_party/seo_lite/mcp.seo_lite.php */ 