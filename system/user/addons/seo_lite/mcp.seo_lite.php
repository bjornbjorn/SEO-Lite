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

	function __construct( $switch = TRUE )
	{
        // uncomment this if you want navigation buttons at the top
		ee()->cp->set_right_nav(array(
				'settings'			=> $this->base,
				'docs'	=> 'http://ee.bybjorn.com/seo_lite',
			));


		//  Onward!
		ee()->load->library('table');
		ee()->load->library('javascript');
		ee()->load->helper('form');
		ee()->lang->loadfile('seo_lite');
	}

	function index() 
	{
		$vars = array();

        $site_id = ee()->config->item('site_id');
        $config = ee()->db->get_where('seolite_config', array('site_id' => $site_id));

        if($config->num_rows() == 0) // we did not find any config for this site id, so just load any other
        {
            $config = ee()->db->get_where('seolite_config');
        }

		$vars['template'] = $config->row('template');
        $vars['default_description'] = $config->row('default_description');
        $vars['default_keywords'] = $config->row('default_keywords');
        $vars['default_title_postfix'] = $config->row('default_title_postfix');
        $vars['include_pagination_in_canonical'] = $config->row('include_pagination_in_canonical');
        $vars['save_settings_url'] =  ee('CP/URL', 'addons/settings/seo_lite/save_settings');

        $view = ee('View')->make('seo_lite:index');
        return $view->render($vars);
	}
	
	function save_settings()
	{
		$template = ee()->input->post('seolite_template');
        $default_keywords = ee()->input->post('seolite_default_keywords');
        $default_description = ee()->input->post('seolite_default_description');
        $default_title_postfix = ee()->input->post('seolite_default_title_postfix');
        $include_pagination_in_canonical = ee()->input->post('seolite_include_pagination_in_canonical');

        $site_id = ee()->config->item('site_id');
        $config = ee()->db->get_where('seolite_config', array('site_id' => $site_id));

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
            ee()->db->insert('seolite_config', $data_arr);
        }
        else
        {
            ee()->db->where('site_id', $site_id);
            ee()->db->update('seolite_config', $data_arr);
        }

        ee('CP/Alert')->makeStandard('seolite-settings-saved')
            ->asSuccess()
            ->withTitle(lang('seolite_settings_saved_title'))
            ->addToBody(lang('seolite_settings_saved'))
            ->defer();

		ee()->functions->redirect(ee('CP/URL', 'addons/settings/seo_lite'));
	}

}

/* End of file mcp.seo_lite.php */ 
/* Location: ./system/expressionengine/third_party/seo_lite/mcp.seo_lite.php */ 