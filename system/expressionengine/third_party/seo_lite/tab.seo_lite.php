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

class Seo_lite_tab {

    public function __construct()
    {
        $this->EE =& get_instance();
        $this->EE->lang->loadfile('seo_lite');
        // don't load config if we have this config item. This is used for overriding from a master config
        if(!$this->EE->config->item('seolite_show_keywords_field')) {
            $this->EE->load->config('seolite');
        }

        $tab_title = $this->EE->config->item('seolite_tab_title') ? $this->EE->config->item('seolite_tab_title') : $this->EE->config->item('seo_lite_tab_title');   // config item was renamed but we support the old seo_lite_tab_title as well
        if($tab_title) {
            $this->EE->lang->language['seo_lite'] = $tab_title;
        }
    }

    public function publish_tabs($channel_id, $entry_id = '')
    {
        $settings = array();

        $title = $keywords = $description = '';
        if($entry_id)
        {
            $where = array(
                 'entry_id' => $entry_id,
                 'site_id' => $this->EE->config->item('site_id')
             );

            if (isset($this->EE->publisher_lib))
            {
                $where['publisher_lang_id'] = $this->EE->publisher_lib->lang_id;
                $where['publisher_status']  = $this->EE->publisher_lib->status;
            }

            $q = $this->EE->db->get_where('seolite_content', $where);

            if($q->num_rows())
            {
                $title = $q->row('title');
                $keywords = $q->row('keywords');
                $description = $q->row('description');
            }
        }

        $settings[] = array(
           'field_id' => 'seo_lite_title',
           'field_label' => lang('seotitle'),
           'field_required' => 'n',
           'field_data' => $title,
           'field_list_items' => '',
           'field_fmt' => '',
           'field_instructions' => lang('title_instructions'),
           'field_show_fmt' => 'n',
           'field_fmt_options' => array(),
           'field_pre_populate' => 'n',
           'field_text_direction' => 'ltr',
           'field_type' => 'text',
           'field_maxl' => '1024'
       );

        if($this->EE->config->item('seolite_show_keywords_field') != 'n') {

            $settings[] = array(
               'field_id' => 'seo_lite_keywords',
               'field_label' => lang('seokeywords'),
               'field_required' => 'n',
               'field_data' => $keywords,
               'field_list_items' => '',
               'field_fmt' => '',
               'field_instructions' => lang('keywords_instructions'),
               'field_show_fmt' => 'n',
               'field_fmt_options' => array(),
               'field_pre_populate' => 'n',
               'field_text_direction' => 'ltr',
                'field_type' => 'textarea',
                'field_ta_rows'		   => 5,
           );
        }

        $settings[] = array(
           'field_id' => 'seo_lite_description',
           'field_label' => lang('seodescription'),
           'field_required' => 'n',
           'field_data' => $description,
           'field_list_items' => '',
           'field_fmt' => '',
           'field_instructions' => lang('description_instructions'),
           'field_show_fmt' => 'n',
           'field_fmt_options' => array(),
           'field_pre_populate' => 'n',
           'field_text_direction' => 'ltr',
           'field_type' => 'textarea',
           'field_ta_rows'		   => 5,

       );

        return $settings;
    }

    function validate_publish($params)
    {
        return TRUE;
    }

    /**
     * Save the data to the db
     *
     * @param  $params
     * @return void
     */
    function publish_data_db($params)
    {
        $seo_lite_data = $params['mod_data'];
        $site_id = $params['meta']['site_id'];
        $entry_id = $params['entry_id'];

        $content = array(
            'site_id' => $site_id,
            'entry_id' => $entry_id,
            'title' => $seo_lite_data['seo_lite_title'],
            'keywords' => isset($seo_lite_data['seo_lite_keywords']) ? $seo_lite_data['seo_lite_keywords'] : '',
            'description' => $seo_lite_data['seo_lite_description'],
        );

        $where = array(
             'entry_id' => $entry_id,
             'site_id' => $site_id
        );

        if (isset($this->EE->publisher_lib))
        {
            $content['publisher_lang_id'] = $this->EE->publisher_lib->lang_id;
            $content['publisher_status']  = $this->EE->publisher_lib->status;

            $where['publisher_lang_id'] = $this->EE->publisher_lib->lang_id;
            $where['publisher_status']  = $this->EE->publisher_lib->status;
        }

        $q = $this->EE->db->get_where('seolite_content', $where);

        if($q->num_rows())
        {
            $this->EE->db->where($where);
            $this->EE->db->update('seolite_content', $content);
        }
        else
        {
            $this->EE->db->insert('seolite_content', $content);
        }
    }

    /**
     * Delete seo data if entry is deleted
     *
     * @param  $params
     * @return void
     */
    function publish_data_delete_db($params)
    {
        foreach($params['entry_ids'] as $i => $entry_id)
        {
            $this->EE->db->where('entry_id', $entry_id);
            $this->EE->db->delete('seolite_content');
        }
    }

}
