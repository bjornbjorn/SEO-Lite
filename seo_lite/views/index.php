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