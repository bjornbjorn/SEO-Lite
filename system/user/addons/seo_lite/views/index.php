<style type="text/css">
    #instructions {
        display:none;
    }
</style>

        <div class="has-tabs">
            <div class="tab-wrap">
                <ul class="tabs">
                    <li><a class="act" href="" rel="t-settings">Settings</a></li>
                    <li><a href="" rel="t-instructions">Instructions</a></li>
                </ul>

                <!-- settings tab content -->
                <div class="tab t-settings tab-open">

                    <h1>Settings</h1>

                    <?php echo form_open($save_settings_url, array('class' => 'settings'))?>
                    <?php echo ee('CP/Alert')->get('seolite-settings-saved')?>

                    <fieldset class="col-group">

                        <div class="required">

                            <div class="setting-txt col w-6">
                                <h3><?php echo lang('template'); ?></h3>
                            </div>

                            <div class="setting-field col w-10 last">
                                <?php echo form_textarea('seolite_template', set_value('seolite_template', $template), 'id="seolite_template"'); ?>
                            </div>

                        </div>
                    </fieldset>

                    <fieldset class="col-group">

                        <div class="setting-txt col w-6">
                            <h3><?php echo lang('default_keywords'); ?></h3>
                        </div>

                        <div class="setting-field col w-10 last">
                            <?php echo form_input('seolite_default_keywords', set_value('seolite_default_keywords', $default_keywords), 'id="seolite_default_keywords"'); ?>
                        </div>

                    </fieldset>

                    <fieldset class="col-group">

                        <div class="setting-txt col w-6">
                            <h3><?php echo lang('default_description'); ?></h3>
                        </div>

                        <div class="setting-field col w-10 last">
                            <?php echo form_textarea('seolite_default_description', set_value('seolite_default_description', $default_description), 'id="seolite_default_description"'); ?>
                        </div>

                    </fieldset>

                    <fieldset class="col-group">

                        <div class="setting-txt col w-6">
                            <h3><?php echo lang('default_title_postfix'); ?></h3>
                        </div>

                        <div class="setting-field col w-10 last">
                            <?php echo form_input('seolite_default_title_postfix', set_value('seolite_default_title_postfix', $default_title_postfix), 'id="seolite_default_title_postfix"'); ?>

                        </div>

                    </fieldset>

                    <fieldset class="col-group">

                        <div class="setting-txt col w-6">
                            <h3><?php echo lang('include_pagination_in_canonical_description'); ?></h3>
                        </div>

                        <div class="setting-field col w-10 last">
                            <label class="choice mr <?php if($include_pagination_in_canonical == 'y') echo "chosen";?> yes"><input type="radio" value="y" name="seolite_include_pagination_in_canonical" <?php if($include_pagination_in_canonical == 'y') echo 'checked="checked"';?>> <?php echo lang('include_pagination_in_canonical_description_y');?></label>
                            <label class="choice no <?php if($include_pagination_in_canonical == 'n') echo "chosen";?>"><input type="radio" value="n" name="seolite_include_pagination_in_canonical"<?php if($include_pagination_in_canonical == 'n') echo 'checked="checked"';?>> <?php echo lang('include_pagination_in_canonical_description_n');?></label>
                        </div>

                    </fieldset>

                    <fieldset class="form-ctrls">
                        <input class="btn" type="submit" value="<?php echo lang('update'); ?>">
                    </fieldset>


                    <?php echo form_close()?>

                </div>

                <!-- instructions tab content -->
                <div class="tab t-instructions">

                    <h1>Instructions</h1>

                    <div class="col-group">
                        <div class="col w-16">
                                <div class="txt-wrap">
                                    <p>Put one of these tags in your template:</p>

                                    <ul class="checklist">
                                        <li>
                                            <strong>Intelligent mode</strong> aka Use-Last-Segment-Mode: <em>{exp:seo_lite use_last_segment="yes"}</em>
                                        </li>
                                        <li>
                                            By <strong>segment</strong>: <em>{exp:seo_lite url_title="{segment_3}"}</em>
                                        </li>
                                        <li>
                                            By <strong>entry_id</strong>: <em>{exp:seo_lite entry_id="{entry_id}"}</em>
                                        </li>
                                        <li>
                                            <strong>Static mode</strong> aka I-Will-Provide-Values-In-Template: (this will output "About Us" for the title tag but still use the default keywords/description for the site): <em>{exp:seo_lite default_title="About us"}</em>
                                        </li>
                                        <li>
                                            <strong>Static mode</strong> with everything overridden: <em>{exp:seo_lite default_title="About us" default_keywords="new, keywords" default_description="This description is unique for this page"}</em>
                                        </li>
                                    </ul>

                                    <p>Either of these tags will output the template specified in the SEO Lite settings with the title/keywords/description specific for the content. The SEO Lite template is parsed as a normal EE template, so you can use any EE global variables and conditionals etc. <a href="http://wedoaddons.com/addon/seo-lite/documentation" target="_blank">More instructions available here.</a></p>

                                </div>


                        </div>

                    </div>


                </div>
            </div>
        </div>






<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/seo_lite/views/index.php */