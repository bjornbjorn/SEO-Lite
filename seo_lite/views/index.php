
    <h2>Instructions:</h2>

    <p>Put one of these tags in your template:</p>

    <p>By <strong>segment</strong>: <input type='text' value='{exp:seo_lite url_title="{segment_3}"}'/> </p>
    <p>By <strong>entry_id</strong>: <input type='text' value='{exp:seo_lite entry_id="{entry_id}"}'/></p>
    <p><strong>Intelligent mode</strong> aka Use-Last-Segment-Mode: <input type='text' value='{exp:seo_lite use_last_segment="yes"}'/></p>
    <p><strong>Static mode</strong> aka I-Will-Provide-Values-In-Template: (this will output "About Us" for the title tag but still use the default keywords/description for the site) <input type='text' value='{exp:seo_lite default_title="About us"}'/></p></p>
    <p><strong>Static mode</strong> with everything overridden: <input type='text' value='{exp:seo_lite default_title="About us" default_keywords="new, keywords" default_description="This description is unique for this page"}'/></p></p>

    <p>&nbsp;</p>
    <p><em>Either of these tags will output the template below with the title/keywords/description specific for the content. The template below is parsed as a normal EE template, so you can use any EE global variavbles and conditionals etc.</em> <a href="http://ee.bybjorn.com/seo_lite">More instructions available here.</a></p>

    <p>&nbsp;</p>


    <h2>Settings</h2>
    <p>&nbsp;</p>

<?php
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(array(
			array('data' => lang('setting'), 'width' => '50%'),
			lang('current_value')
		)
	);
?>

<?=form_open($_form_base.'&method=save_settings')?>

	<?php 

        $this->table->add_row(array(
                lang('template', 'seolite_template'),
                form_error('seolite_template').
                form_textarea('seolite_template', set_value('seolite_template', $template), 'id="seolite_template"')
            )
        );

		$this->table->add_row(array(
				lang('default_keywords', 'seolite_default_keywords'),
				form_error('seolite_default_keywords').
				form_textarea('seolite_default_keywords', set_value('seolite_default_keywords', $default_keywords), 'id="seolite_default_keywords"')
			)
		);
		
		$this->table->add_row(array(
				lang('default_description', 'seolite_default_description'),
				form_error('seolite_default_description').
				form_textarea('seolite_default_description', set_value('seolite_default_description', $default_description), 'id="seolite_default_description"')
			)
		);
		
		echo $this->table->generate();
	?>
	<p>
		<?=form_submit(array('name' => 'submit', 'value' => lang('update'), 'class' => 'submit'))?>
	</p>

<?=form_close()?>

<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/seo_lite/views/index.php */