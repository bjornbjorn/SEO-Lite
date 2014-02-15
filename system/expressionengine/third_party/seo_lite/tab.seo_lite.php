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
            $table_name = 'seolite_content';
            $where = array(
                 'entry_id' => $entry_id,
                 'site_id' => $this->EE->config->item('site_id')
             );


            // -------------------------------------------
            // Allows one to modify the SEO Lite pulled up in the tab (ie. for translation addons)
            //
            // Params sent in:
            // - $where - an array of where (activerecord) to check for .. already contains 'entry_id' and 'site_id'
            // - $table_name - the name of the table to pull data from (without db prefix, defaults to 'seolite_content')
            //
            // Return value:
            // Please return nothing at all or an array which contains 'where' and/or 'table_name' to replace the existing
            // where array and table name to pull data from. This will be used to ->get(where, table_name) the data so
            // you can basically pull whatever from any table.
            //
            // But remember the results must contain 'title', 'keywords', 'description' which SEO Lite rely on for the
            // tab content.
            //
            // -------------------------------------------
            if ($this->EE->extensions->active_hook('seo_lite_tab_content') === TRUE)
            {
                $hook_result = $this->return_data = $this->EE->extensions->call('seo_lite_tab_content', $where, $table_name);
                if($hook_result && isset($hook_result['where'])) {
                    $where = $hook_result['where'];
                }
                if($hook_result && isset($hook_result['table_name'])) {
                    $table_name = $hook_result['table_name'];
                }

                if ($this->EE->extensions->end_script === TRUE) return;
            }

            $q = $this->EE->db->get_where($table_name, $where);

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

        $table_name = 'seolite_content';
        $where = array(
             'entry_id' => $entry_id,
             'site_id' => $site_id
        );

        $default_where = $where;
        $default_content = $content;
        $default_table_name = $table_name;

        // -------------------------------------------
        // Allows one to modify the SEO Lite saved in the tab (ie. for translation addons)
        //
        // Params sent in:
        // - $where - an array of where (activerecord) on UPDATE .. already contains 'entry_id' and 'site_id'
        // - $table_name - the name of the table to pull data from (without db prefix, defaults to 'seolite_content')
        // - $content - the current content saved (an array of site_id, entry_id, title, keywords, description)
        //
        // Return value:
        // Please return nothing at all or an array which contains 'where' and/or 'table_name' and/or 'content' to
        // replace any of these.
        //
        // But remember the content must contain 'site_id', 'entry_id', 'title', 'keywords', 'description'
        //
        // -------------------------------------------
        if ($this->EE->extensions->active_hook('seo_lite_tab_content_save') === TRUE) {

            $hook_result = $this->return_data = $this->EE->extensions->call('seo_lite_tab_content_save', $where, $table_name, $content);
            if($hook_result && isset($hook_result['where'])) {
                $where = $hook_result['where'];
            }
            if($hook_result && isset($hook_result['table_name'])) {
                $table_name = $hook_result['table_name'];
            }
            if($hook_result && isset($hook_result['content'])) {
                $content = $hook_result['content'];
            }

            if ($this->EE->extensions->end_script === TRUE) return;
        }

        $q = $this->EE->db->get_where($table_name, $where);

        if($q->num_rows())
        {
            $this->EE->db->where($where);
            $this->EE->db->update($table_name, $content);
        }
        else
        {
            $this->EE->db->insert($table_name, $content);
        }

        /**
         * If the data was stored to another table (ie if a third party addon took control over this, we still just
         * store the content in case that third_party addon is uninstalled later. Note that this may cause problems
         * with addons that store multiple versions for the same entry_id (ie. Publisher). If so SEO Lite will end
         * up with the latest stored version (which could be in language 1 or language 2 etc.) .. but in cases like
         * these a lot of data won't make sense anyway so .. in other cases, where the addon uses a different entry_id
         * for each type of content everything should work just fine if uninstalling that addon.
         */
        if($table_name != $default_table_name) {
            $q = $this->EE->db->get_where($default_table_name, $default_where);

            if($q->num_rows())
            {
                $this->EE->db->where($default_where);
                $this->EE->db->update($default_table_name, $default_content);
            }
            else
            {
                $this->EE->db->insert($default_table_name, $default_content);
            }
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
