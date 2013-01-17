<style type="text/css">
    .fullwidth {
        width:100%;
    }

    #instructions {
        display:none;
    }
</style>

<script type="text/javascript">

    $(document).ready(
            function() {

                $('#view_instructions').click(function(){
                    if($('#instructions').is(':visible')) {
                        $('#instructions').hide('fast');
                        $('#view_instructions').html('Show instructions');
                    }
                    else
                    {
                        $('#instructions').show('fast');
                        $('#view_instructions').html('Hide instructions');
                    }
                });
                
            });

</script>

<h3><a href="#" id="view_instructions">View instructions</a></h3>

    <div id="instructions">
        <p>These entries will override any other segment settings if the provided URI matches.</p>
        <p>
        	To make SEO Lite completely ignore static URLS, provide set ignore_static to "yes":<br />
        	<input type='text' class="fullwidth" value='{exp:seo_lite ignore_static="yes"}' readonly/>
        </p>
        <p>
        	
        To remove a static URI, just empty the URI field and press Update.	
        </p>

        <p>&nbsp;</p>
    </div>

<?php
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(array(
			lang('uri'),
			lang('seotitle'),
			lang('seokeywords'),
			lang('seodescription'),
		)
	);
?>

<?=form_open($_form_base.'&method=save_statics')?>

	<?php 		
	
		if( $statics->num_rows() > 0)
		{
			foreach( $statics->result() as $row)
			{
		        $this->table->add_row(array(
		        	form_input('seolite_static_url['.$row->seolite_static_id.']', set_value('seolite_static_url['.$row->seolite_static_id.']', $row->static_url), 'placeholder="/example/uri" id="seolite_default_keywords"'),
		        	form_input('seolite_static_title['.$row->seolite_static_id.']', set_value('seolite_static_title['.$row->seolite_static_id.']', $row->title)),
		        	form_input('seolite_static_keywords['.$row->seolite_static_id.']', set_value('seolite_static_keywords['.$row->seolite_static_id.']', $row->keywords)),
		        	form_input('seolite_static_description['.$row->seolite_static_id.']', set_value('seolite_static_description['.$row->seolite_static_id.']', $row->description)),
		            )
		        );
	        }		
		} 
		
        $this->table->add_row(array(
        	form_input('seolite_static_url[new]', set_value('seolite_static_url[new]', ''), 'placeholder="/example/uri" id="seolite_default_keywords"'),
        	form_input('seolite_static_title[new]', set_value('seolite_static_title[new]', '')),
        	form_input('seolite_static_keywords[new]', set_value('seolite_static_keywords[new]', '')),
        	form_input('seolite_static_description[new]', set_value('seolite_static_description[new]', '')),
            )
        );
	
	
		/*
        $this->table->add_row(array(
                lang('template', 'seolite_template'),
                form_error('seolite_template').
                form_textarea('seolite_template', set_value('seolite_template', ''), 'id="seolite_template"')
            )
        );

		$this->table->add_row(array(
				lang('default_keywords', 'seolite_default_keywords'),
				form_error('seolite_default_keywords').
				form_input('seolite_default_keywords', set_value('seolite_default_keywords', ''), 'id="seolite_default_keywords"')
			)
		);
		
        $this->table->add_row(array(
                lang('default_description', 'seolite_default_description'),
                form_error('seolite_default_description').
                form_textarea('seolite_default_description', set_value('seolite_default_description', ''), 'id="seolite_default_description"')
            )
        );


        $this->table->add_row(array(
            lang('default_title_postfix', 'seolite_default_title_postfix'),
            form_error('seolite_default_title_postfix').
            form_input('seolite_default_title_postfix', set_value('seolite_default_title_postfix', ''), 'id="seolite_default_title_postfix"')
            )
        );
		*/
		echo $this->table->generate();
	?>
	<p>
		<?=form_submit(array('name' => 'submit', 'value' => lang('update'), 'class' => 'submit'))?>
	</p>

<?=form_close()?>

<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/seo_lite/views/index.php */