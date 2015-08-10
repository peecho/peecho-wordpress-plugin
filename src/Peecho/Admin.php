<?php

class Peecho_Admin
{
    public function __construct()
    {
        add_filter('plugin_action_links', array(&$this, 'actionLinks'), 10, 2);
        add_action('admin_menu', array(&$this, 'menu'));
        add_action('current_screen', array(&$this, 'addHeaderXss'));
        add_action( 'wp_head', array(&$this ,'scriptFunction') );
    }

    public function actionLinks($links, $file)
    {
        $pluginFile = plugin_basename(dirname(Peecho::FILE));
        $pluginFile .= '/peecho.php';

        if ($file == $pluginFile) {
            $url = 'options-general.php?page=peecho/peecho.php';
            $link = "<a href='{$url}'>";
            $link .= __('Settings', Peecho::TEXT_DOMAIN).'</a>';
            $links[] = $link;
        }
        return $links;
    }
    public function menu()
    {
        $capability = 'manage_options';
        if (defined('PEECHO_ALLOW_EDIT_POSTS')
            and current_user_can('edit_posts')
        ) {
            $allowed = true;
            $capability = 'edit_posts';
        }

        if (current_user_can('manage_options') or isset($allowed)) {
            $optionPage = add_options_page(
                'Peecho Options',
                'Peecho',
                $capability,
                Peecho::FILE,
                array(&$this, 'optionsPage')
            );
        } else {
            $option_page = add_options_page(
                'Peecho',
                'Peecho',
                'edit_posts',
                Peecho::FILE,
                array(&$this, 'overviewPage')
            );
        }
    }
    public function addHeaderXss($current_screen)
    {
        if ($current_screen->base == 'settings_page_peecho/peecho') {
            header('X-XSS-Protection: 0');
        }
    }
    private function add()
    {
        if (isset($_POST['add-snippet'])
            && isset($_POST['update_snippets_nonce'])
            && wp_verify_nonce($_POST['update_snippets_nonce'], 'update_snippets')
        ) {
            $snippets = get_option(Peecho::OPTION_KEY);
            if (empty($snippets)) {
                $snippets = array();
            }

            array_push(
                $snippets,
                array(
                    'title' => 'Untitled',
                    'vars' => '',
                    'description' => '',
                    'shortcode' => false,
                    'php' => false,
                    'wptexturize' => false,
                    'snippet' => ''
                )
            );

            update_option(Peecho::OPTION_KEY, $snippets);
            $this->message(
                __(
                    'A snippet named Untitled has been added.',
                    Peecho::TEXT_DOMAIN
                )
            );
        }
    }

    /**
     * Delete Snippet/s.
     */
    private function delete()
    {
        if (isset($_POST['delete-snippets'])
            && isset($_POST['update_snippets_nonce'])
            && wp_verify_nonce($_POST['update_snippets_nonce'], 'update_snippets')
        ) {
            $snippets = get_option(Peecho::OPTION_KEY);

            if (empty($snippets) || !isset($_POST['checked'])) {
                $this->message(
                    __('Nothing selected to delete.', Peecho::TEXT_DOMAIN)
                );
                return;
            }

            $delete = $_POST['checked'];

            $newsnippets = array();
            foreach ($snippets as $key => $snippet) {
                if (in_array($key, $delete) == false) {
                    array_push($newsnippets, $snippet);
                }
            }

            update_option(Peecho::OPTION_KEY, $newsnippets);
            $this->message(
                __(
                    'Selected snippets have been deleted.',
                    Peecho::TEXT_DOMAIN
                )
            );
        }
    }

    /**
     * Update Snippet/s.
     */
    private function update()
    {
        if (isset($_POST['update-snippets'])
            && isset($_POST['update_snippets_nonce'])
            && wp_verify_nonce($_POST['update_snippets_nonce'], 'update_snippets')
        ) {
            $snippets = get_option(Peecho::OPTION_KEY);
            if (!empty($snippets)) {
                foreach ($snippets as $key => $value) {
                    $new_snippets[$key]['title'] = trim($_POST[$key.'_title']);
                    $new_snippets[$key]['vars'] = str_replace(' ', '', trim($_POST[$key.'_vars']));
                    $new_snippets[$key]['shortcode'] = isset($_POST[$key.'_shortcode']) ? true : false;

                    if (!defined('POST_SNIPPETS_DISABLE_PHP')) {
                        $new_snippets[$key]['php'] = isset($_POST[$key.'_php']) ? true : false;
                    } else {
                        $new_snippets[$key]['php'] = isset($snippets[$key]['php']) ? $snippets[$key]['php'] : false;
                    }

                    $new_snippets[$key]['wptexturize'] = isset($_POST[$key.'_wptexturize']) ? true : false;

                    $new_snippets[$key]['snippet'] = wp_specialchars_decode(trim(stripslashes($_POST[$key.'_snippet'])), ENT_NOQUOTES);
                    $new_snippets[$key]['description'] = wp_specialchars_decode(trim(stripslashes($_POST[$key.'_description'])), ENT_NOQUOTES);
                }
                update_option(Peecho::OPTION_KEY, $new_snippets);
                $this->message(__('Snippets have been updated.', Peecho::TEXT_DOMAIN));
            }
        }
    }

    /**
     * Update User Option.
     *
     * Sets the per user option for the read-only overview page.
     *
     * @since   Post Snippets 1.9.7
     */
    private function setUserOptions()
    {
        if (isset($_POST['post_snippets_user_nonce'])
            && wp_verify_nonce($_POST['post_snippets_user_nonce'], 'post_snippets_user_options')
        ) {
            $id = get_current_user_id();
            $render = isset($_POST['render']) ? true : false;
            update_user_meta($id, Peecho::USER_META_KEY, $render);
        }
    }
    private function getUserOptions()
    {
        $id = get_current_user_id();
        $options = get_user_meta($id, Peecho::USER_META_KEY, true);
        return $options;
    }
    private function message($message)
    {
        if ($message) {
            echo "<div class='updated'><p><strong>{$message}</strong></p></div>";
        }
    }
    public function optionsPage()
    {
        // Handle Form Submits
        $this->add();
        $this->delete();
        $this->update();

        // Header
        echo '
        <!-- Create a header in the default WordPress \'wrap\' container -->
        <div class="wrap">
            <div id="icon-plugins" class="icon32"></div>
            <h2>Peecho</h2>';
        $active_tab = isset($_GET[ 'tab' ]) ? $_GET[ 'tab' ] : 'snippets';
        $base_url = '?page=peecho/peecho.php&amp;tab=';
        $tabs = array('snippets' => __('Peecho Buttons', Peecho::TEXT_DOMAIN), 'tools' => __('Settings', Peecho::TEXT_DOMAIN));
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $title) {
            $active = ($active_tab == $tab) ? ' nav-tab-active' : '';
            echo "<a href='{$base_url}{$tab}' class='nav-tab {$active}'>{$title}</a>";
        }
        echo '</h2>';
        echo '<p class="description">';
       // _e('Use the help dropdown button for additional information.', Peecho::TEXT_DOMAIN);
        echo '</p>';
        if ($active_tab == 'snippets') {
            $this->tabSnippets();
        } else {
            $this->tabSetting();
        }
        echo '</div>';
    }
    private function tabSnippets()
    {
        $data = array();
        echo Peecho_View::render('admin_snippets', $data);
    }
    private function tabTools()
    {
        $ie = new Peecho_ImportExport();
        printf("<h3>%s</h3>", __('Import/Export', Peecho::TEXT_DOMAIN));
        printf("<h4>%s</h4>", __('Export', Peecho::TEXT_DOMAIN));
        echo '<form method="post">';
        echo '<p>';
        _e('Export your snippets for backup or to import them on another site.', Peecho::TEXT_DOMAIN);
        echo '</p>';
        printf("<input type='submit' class='button' name='Peecho_export' value='%s' />", __('Export Snippets', Peecho::TEXT_DOMAIN));
        echo '</form>';
        $ie->exportSnippets();
        echo $ie->importSnippets();
    }

    private function tabSetting()
    {
        $userId = get_option('user_script_id');
        printf("<h3>%s</h3>", __('Setting Option', Peecho::TEXT_DOMAIN));
        echo '<form method="post" action="">';
        echo '<p>';
        echo'Enter your Peecho button key here to create Peecho print buttons. You can find your Peecho button key on , 

<a href="http://www.peecho.com/dashboard">under Settings Print API</a>
        ';
        echo '</p>';
         echo '<p>';

         $snippets = get_option(Peecho::OPTION_KEY);
         print_r($snippet);
         if (empty($snippets)) {
           echo 'No Peecho print buttons added yet. Click 
<a href="options-general.php?page=peecho/peecho.php">"Add Button"</a>';
         }
        
        echo '</p>';

        echo '<table>';

            echo '<tr>';
                echo '<td> User ID : </td>';
                echo '<td><input type="text" name="user_id" value="'.$userId.'"></td>';
                  
            echo '</tr>';
        echo '</table>';

        printf("<input type='submit' class='button' name='setting' value='%s' />", __('Save Setting', Peecho::TEXT_DOMAIN));
        echo '</form>';
        $this->saveSetting();
    }

    private function saveSetting()
    {
        if(isset($_POST['setting']))
        {
            if(!empty($_POST['user_id']))
            {
                update_option('user_script_id', $_POST['user_id']);
                $this->message(
                    __(
                        'A Script ID has been added.',
                        Peecho::TEXT_DOMAIN
                    )
                );
           }
        }
    }
    
    public function overviewPage()
    {
        // Header
        echo '<div class="wrap">';
        echo '<h2>Peecho</h2>';
        echo '<p>';
        _e('.', Peecho::TEXT_DOMAIN);
        echo '</p>';

        // Form
        $this->setUserOptions();
        $render = $this->getUserOptions();

        echo '<form method="post" action="">';
        wp_nonce_field('post_snippets_user_options', 'post_snippets_user_nonce');

        $this->checkbox(__('Display rendered snippets', Peecho::TEXT_DOMAIN), 'render', $render);
        $this->submit('update-peecho-user', __('Update', Peecho::TEXT_DOMAIN));
        echo '</form>';
        $snippets = get_option(Peecho::OPTION_KEY);
        if (!empty($snippets)) {
            foreach ($snippets as $key => $snippet) {
                echo "<hr style='border: none;border-top:1px dashed #aaa; margin:24px 0;' />";

                echo "<h3>{$snippet['title']}";
                if ($snippet['description']) {
                    echo "<span class='description'> {$snippet['description']}</span>";
                }
                echo "</h3>";
                if ($snippet['vars']) {
                    printf("<strong>%s:</strong> {$snippet['vars']}<br/>", __('Variables', Peecho::TEXT_DOMAIN));
                }
                $options = array();
                if ($snippet['shortcode']) {
                    array_push($options, 'Shortcode');
                }
                if ($snippet['php']) {
                    array_push($options, 'PHP');
                }
                if ($snippet['wptexturize']) {
                    array_push($options, 'wptexturize');
                }
                if ($options) {
                    printf("<strong>%s:</strong> %s<br/>", __('Options', Peecho::TEXT_DOMAIN), implode(', ', $options));
                }

                printf("<br/><strong>%s:</strong><br/>", __('Snippet', Peecho::TEXT_DOMAIN));
                if ($render) {
                    echo do_shortcode($snippet['snippet']);
                } else {
                    echo "<code>";
                    echo nl2br(htmlspecialchars($snippet['snippet'], ENT_NOQUOTES));
                    echo "</code>";
                }
            }
        }
        // Close
        echo '</div>';
    }

    public static function checkbox($label, $name, $checked)
    {
        echo "<label for=\"{$name}\">";
        printf('<input type="checkbox" style="Display:none;" name="%1$s" id="%1$s" value="true"', $name);
        
        //if ($checked) {
            echo ' checked';
      // }
        echo ' />';
        echo " {$label}</label><br/>";
    }

       public static function submit($name, $label, $class = 'button-primary', $wrap = true, $disable = false)
    {   
        if($disable == true){
            $buttondisble = 'disabled';
        }else{
            $buttondisble = '';
        }
        $btn = sprintf('<input type="submit" name="%s" value="%s" class="%s" '.$buttondisble.' />', $name, $label, $class);

        if ($wrap) {
            $btn = "<div class=\"submit\">{$btn}</div>";
        }

        echo $btn;
    }

    public function scriptFunction(){

        $userId = get_option('user_script_id');
        echo '<script type="text/javascript">
           var p=document.createElement("script");p.type="text/javascript";p.async=true;
           var h=("https:"==document.location.protocol?"https://":"http://");
           p.src=h+"d3aln0nj58oevo.cloudfront.net/button/script/'.$userId.'.js";
           var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(p,s);
        </script>';
        
    }
}
