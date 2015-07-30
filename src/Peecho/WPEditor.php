<?php
class Peecho_WPEditor
{
    const TINYMCE_PLUGIN_NAME = 'peecho';

    public function __construct()
    {
        add_action('init', array(&$this, 'addTinymceButton'));
        add_action(
            'admin_print_footer_scripts',
            array(&$this, 'addQuicktagButton'),
            100
        );

        add_action('admin_head', array(&$this, 'jqueryUiDialog'));
        add_action('admin_footer', array(&$this, 'addJqueryUiDialog'));
        add_action('admin_init', array(&$this, 'enqueueAssets'));
    }
    public function addTinymceButton()
    {
        // Don't bother doing this stuff if the current user lacks permissions
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }
        if (get_user_option('rich_editing') == 'true') {
            add_filter(
                'mce_external_plugins',
                array(&$this, 'registerTinymcePlugin')
            );
            add_filter(
                'mce_buttons',
                array(&$this, 'registerTinymceButton')
            );
        }
    }
    public function registerTinymceButton($buttons)
    {
        array_push($buttons, 'separator', self::TINYMCE_PLUGIN_NAME);
        return $buttons;
    }
    public function registerTinymcePlugin($plugins)
    {
        // Load the TinyMCE plugin, editor_plugin.js, into the array
        $plugins[self::TINYMCE_PLUGIN_NAME] =
            plugins_url('/tinymce/editor_plugin.js?ver=1.9', Peecho::FILE);

        return $plugins;
    }
    public function addQuicktagButton()
    {
        echo "\n<!-- START: Add QuickTag button for Peecho -->\n";
        ?>
        <script type="text/javascript" charset="utf-8">
            if (typeof QTags != 'undefined') {
                function qt_peecho() {
                    peecho_caller = 'html';
                    jQuery("#peecho-dialog").dialog("open");
                }
                QTags.addButton('peecho_id', 'Peecho', qt_peecho);
            }
        </script>
        <?php
        echo "\n<!-- END: Add QuickTag button for Peecho -->\n";
    }
    public function enqueueAssets()
    {
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_style('wp-jquery-ui-dialog');
        $style_url = plugins_url('/assets/peecho.css', Peecho::FILE);
        wp_register_style('peecho', $style_url, false, '2.0');
        wp_enqueue_style('peecho');
    }

    public function jqueryUiDialog()
    {
        echo "\n<!-- START: Peecho jQuery UI and related functions -->\n";
        echo "<script type='text/javascript'>\n";
        $snippets = get_option(Peecho::OPTION_KEY, array());
        $snippets = apply_filters('peecho_snippets_list', $snippets);

        foreach ($snippets as $key => $snippet) {
            if ($snippet['shortcode']) {
                $var_arr = explode(",", $snippet['vars']);
                $variables = '';
                if (!empty($var_arr[0])) {
                    foreach ($var_arr as $var) {
                        $var = $this->stripDefaultVal($var);

                        $variables .= ' ' . $var . '="{' . $var . '}"';
                    }
                }
                $shortcode = $snippet['title'] . $variables;
                echo "var postsnippet_{$key} = '[" . $shortcode . "]';\n";
            } else {
                $snippet = $snippet['snippet'];
                $snippet = str_replace('<', '\x3C', str_replace('>', '\x3E', $snippet));
                $snippet = str_replace('"', '\"', $snippet);
                $snippet = str_replace(chr(13), '', str_replace(chr(10), '\n', $snippet));
                echo "var postsnippet_{$key} = \"" . $snippet . "\";\n";
            }
        }
        ?>
        jQuery(document).ready(function($){
        <?php
        # Create js variables for all form fields
        foreach ($snippets as $key => $snippet) {
            $var_arr = explode(",", $snippet['vars']);
            if (!empty($var_arr[0])) {
                foreach ($var_arr as $key_2 => $var) {
                    $varname = "var_" . $key . "_" . $key_2;
                    echo "var {$varname} = $( \"#{$varname}\" );\n";
                }
            }
        }
        ?>

            var tabs = $('#peecho-tabs').tabs();

            
            $(function() {
                $( "#peecho-dialog" ).dialog({
                    autoOpen: false,
                    modal: true,
                    dialogClass: 'wp-dialog',
                    buttons: {
                        Cancel: function() {
                            $( this ).dialog( "close" );
                          
                        },
                        "Insert": function() {
                            $(this).dialog("close");
                           
                        <?php
                        global $wp_version;
        if (version_compare($wp_version, '3.5', '<')) {
            ?>
                            var selected = tabs.tabs('option', 'selected');
        <?php

            } else {
        ?>
                            var selected = tabs.tabs('option', 'active');
                        <?php

        }
        ?>
                        <?php
        foreach ($snippets as $key => $snippet) {
            ?>
                if (selected == <?php echo $key;
            ?>) {
                                    insert_snippet = postsnippet_<?php echo $key;
            ?>;
                                    <?php
                                    $var_arr = explode(",", $snippet['vars']);
            if (!empty($var_arr[0])) {
                foreach ($var_arr as $key_2 => $var) {
                    $varname = "var_" . $key . "_" . $key_2;
                    ?>
                                            insert_snippet = insert_snippet.replace(/\{<?php
                                            echo $this->stripDefaultVal($var);
                    ?>\}/g, <?php echo $varname;
                    ?>.val());
            <?php
                    echo "\n";
                }
            }
            ?>
                                }
        <?php

        }
        ?>
                            if (peecho_caller == 'html') {
                                // HTML editor in WordPress 3.3 and greater
                                QTags.insertContent(insert_snippet);
                                
                            } else {
                                peecho_canvas.execCommand('mceInsertContent', false, insert_snippet);
                                
                            }

                        }
                    },
                    width: 500,
                });
            });
        });
        var peecho_canvas;
        var peecho_caller = '';

        <?php
        echo "</script>\n";
        echo "\n<!-- END: Peecho jQuery UI and related functions -->\n";
    }
    public function addJqueryUiDialog()
    {
        $snippets = get_option(Peecho::OPTION_KEY, array());
        $snippets = apply_filters('peecho_snippets_list', $snippets);
        $data = array('snippets' => $snippets);

        echo Peecho_View::render('jquery-ui-dialog', $data);
    }
    public function stripDefaultVal($variable)
    {
        $def_pos = strpos($variable, '=');

        if ($def_pos !== false) {
            $split = str_split($variable, $def_pos);
            $variable = $split[0];
        }
        return $variable;
    }
}
