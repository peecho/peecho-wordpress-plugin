<?php
class Peecho_Admin
{
    public function __construct()
    {
        add_filter('plugin_action_links', array(&$this, 'actionLinks'), 10, 2);
        add_action('admin_menu', array(&$this, 'menu'));        
        add_action('current_screen', array(&$this, 'addHeaderXss'));
        add_action( 'wp_footer', array(&$this ,'scriptFunction') );        
        add_action( 'admin_enqueue_scripts', array(&$this ,'load_wp_media_files'));
		 		
	}

	public function actionLinks($links, $file)
    {
        $pluginFile = plugin_basename(dirname(Peecho::FILE));
        $pluginFile .= '/peecho.php';

        if ($file == $pluginFile) {
            $url = 'admin.php?page='.BASENAME.'&tab=tools';
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
            $optionPage = add_submenu_page(
                'customteam',
                'Peecho Options',
                '',
                $capability,
                Peecho::FILE,
                array(&$this, 'optionsPage')
            );
			
        } else {
            $option_page = add_submenu_page(
                'customteam',
                'Peecho',
                '',
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
                    'You have created a new Untitled button, you can now add your button title and code.',
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
        if ($_POST['action'] == 'delete'){  		             
            $snippets = get_option(Peecho::OPTION_KEY);
            if (empty($snippets) || !isset($_POST['checked'])) {
                $this->message(
                    __('Nothing selected to delete.', Peecho::TEXT_DOMAIN)
                );
                return;
            }
            update_option(Peecho::OPTION_KEY, '');

            $delete = $_POST['checked'];           
            $new_snippets = array();
			
            foreach ($snippets as $key => $snippet) {
                if (in_array($key, $delete) == false) {
					array_push($new_snippets, $snippet);
				}
            }

            update_option(Peecho::OPTION_KEY, $new_snippets);
			
		?>
              <script>
			  	window.location = 'admin.php?page=customteam';
			  </script>

        <?php
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
            && $_POST['action'] == 'update'
        ) {            
            if($_GET['snippet']){
			    $updatekeys = $_GET['snippet'];
                $updatek    = explode(',',$updatekeys);
            }else{
                $updatek = $_POST['checked'];
            }
            $snippets = get_option(Peecho::OPTION_KEY);
            if (!empty($snippets)) {
                foreach ($snippets as $key => $value) {
					 if (in_array($key, $updatek) == true) {
							
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
							
							$userId = get_option('user_script_id');
				
							if(!empty($_POST['image_url']["'".$key."'"])){
								
								$imgurl =  $_POST['image_url']["'".$key."'"];
								$apikey =  get_option('user_script_id');
								$url = "http://www.peecho.com/rest/storage/createPublicationFromUpload";
								$postvars = "sourceUrl=" . $imgurl . "&applicationApiKey=".$apikey ;
		
								$temp = curl_init($url);
								curl_setopt($temp, CURLOPT_POST, 1);
								curl_setopt($temp, CURLOPT_POSTFIELDS, $postvars);
								curl_setopt($temp, CURLOPT_FOLLOWLOCATION, 1);
								curl_setopt($temp, CURLOPT_HEADER, 0);
								curl_setopt($temp, CURLOPT_RETURNTRANSFER, 1);						
								curl_setopt($temp, CURLOPT_URL,$url);
								$resultnew = curl_exec($temp);
								
								curl_close($temp);
								$finalresult = json_decode($resultnew);
								$publicationid = $finalresult->publicationId;
								
								$state = 'PROCESSING';
								do{
									$ch = curl_init();
									$url = "http://www.peecho.com/rest/storage/details?publicationId=".$publicationid;
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
									curl_setopt($ch, CURLOPT_URL,$url);
									$mesagestatus = curl_exec($ch);
									curl_close($ch);						
									$message = json_decode($mesagestatus);
									$state = $message->state;
									if($state == 'DONE'){										
										$type = $message->filetype;
										$width = $message->width;
										$height = $message->height;
										$noofpage = $message->numberofpages;										
										$snippetsvalue = '<a title="Peecho" href="http://www.peecho.com/" class="peecho-print-button" data-filetype="'.$type.'" data-width="'.$width.'" data-height="'.$height.'" data-pages="'.$noofpage.'" data-publication="'.$publicationid.'">Print</a>';
										$new_snippets[$key]['snippet'] = wp_specialchars_decode(trim(stripslashes($snippetsvalue)), ENT_NOQUOTES);								
									}
								}while($state == 'PROCESSING');								
							}							
						}
				}
								
				$allsnippets = get_option(Peecho::OPTION_KEY);
				foreach($allsnippets as $key => $value){
					if (in_array($key, $updatek) == false) {
						$new_snippets[$key]['snippet'] = $value['snippet'];
						$new_snippets[$key]['title'] = $value['title'];
						$new_snippets[$key]['vars'] = $value['vars'];
						$new_snippets[$key]['shortcode'] = $value['shortcode'];
						$new_snippets[$key]['php'] = $value['php'];
						$new_snippets[$key]['wptexturize'] = $value['wptexturize'];
						$new_snippets[$key]['description'] = $value['description'];						
					}
				}
				
                update_option(Peecho::OPTION_KEY, $new_snippets);
                //$this->message(__('Snippets have been updated.', Peecho::TEXT_DOMAIN));
				?>
                <script>
					window.location = 'admin.php?page=customteam';
				</script>
                <?php
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
        ){
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
		
		//$_GET[ 'tab' ] = 'tools';
        echo '<!-- Create a header in the default WordPress \'wrap\' container -->
        <div class="wrap">';
		if($_GET[ 'tab' ] == 'snippets'){	
			echo '<div id="icon-plugins" class="icon32"></div>
			<div class="header-div">
            <h1 class="header-h">Peecho : Edit Button</h1> <a class="header-a" href="admin.php?page=customteam"><button class="" style="" type="button">Add New</button></a></div>';
		}else if($_GET[ 'tab' ] == 'tools'){
			echo '<div id="icon-plugins" class="icon32"></div>
            <h1>Peecho : Settings</h1>';
		}else{
			echo '<div id="icon-plugins" class="icon32"></div>
			<div class="header-div">
            <h1 class="header-h">Peecho : Edit Button</h1> <a class="header-a" href="admin.php?page=customteam"><button class="" style="" type="button">Add New</button></a></div>';
		}
		

        $active_tab = isset($_GET[ 'tab' ]) ? $_GET[ 'tab' ] : 'snippets';
        $base_url = '?page='.BASENAME.'&amp;tab=';
        $tabs = array('snippets' => __('Peecho Buttons', Peecho::TEXT_DOMAIN), 'tools' => __('Settings', Peecho::TEXT_DOMAIN));
        echo '<h2 class="nav-tab-wrapper" style="display:none;">';
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
		$this->saveSetting();
        echo '<div class="min-stng">';
        printf("<h3 class='p-stng'>%s</h3>", __('Peecho Setting', Peecho::TEXT_DOMAIN));		
		$userId = get_option('user_script_id');
        $buttonId = get_option('peecho_button_id');
        if(($userId) && ($buttonId))
        {           
	        echo '<div id="u85" class="cnt">Connected</div>'; 
        }else{
		      echo '<div id="u84" class="nt-cnt">&nbspNot Connected</div>';    
        }
		echo '</div>';

        echo '<form method="post" action="">';
            echo'<div style="float: right; height: auto; width: 30%; padding:3px;">';
            echo '<div class="pc-why">
                    <div class="ax_paragraph" id="u70">
                		<p>
                            <img src="'.plugins_url( 'image/peecho.png', BASENAME ).'" class="img " id="u70_img">
                           <span style="font: bold; font-size: 21px">Why Peecho</span>
                        </p>
                        <div class="pc-ax">
                            <p>
                                <span style=" font-size: 14px;font-weight:700;">The highest quality</span>
                                <span style="font-size: 13px;;font-weight:400;"></span>
                            </p>
                            <p>
                                <span style="font-size: 13px;;font-weight:400;">Our print facility network consists of only the best facilities in the world.</span>
                            </p>
            		        <p><span style=" font-size: 14px;;font-weight:700;">Super simple integration</span></p>
            		        <p><span style="font-size: 13px;;font-weight:400;">Our software is really easy to integrate, embedding our print button just takes one line of code!</span>
                            </p>
                            <p><span style=" font-size: 14px;;font-weight:700;">One stop shop</span></span></p>
            		        <p><span style="font-size: 13px;;font-weight:400;">We take care of checkout, payment, production and shipping. And we take care of customer service. All free.</span></p>
                        </div>
                    </div>
                </div>';
            echo '<div class="pc-hlp">
                    <span>Looking for help?</span>
                    <p>
                      Маке sure to look at the <a href="https://www.peecho.com/en/publishers/publishers-print-button"> Peecho Documentation,</br> 
                      FAQ </a> and contact<a href="https://www.peecho.com/en/publishers/publishers-print-button"> support@peecho.com </a>if you have any questions.
                    </p>';
            echo '</div>';
		echo '</div>';		
		
        if(isset($_POST['user_id'])){
            $userId = $_POST['user_id'];
            if(empty($_POST['user_id'])){
				 //echo '<div style="color:red">Application API key shouldn\'t empty</div>';
                //echo '<div id="u84" class="text" style=" color: #fff; margin-left: 149px;  margin-top: -35px;padding: 2px 14px;width: 9%; background-color:#999; ">&nbspNot Connected</div>'; 
            }         
        }else{
            $userId = get_option('user_script_id');
        }

        if(isset($_POST['peecho_button_id'])){
            $buttonId = $_POST['peecho_button_id'];
            if(empty($_POST['peecho_button_id'])){
                //echo '<div style="color:red">Peecho button key shouldn\'t empty</div>';
            }         
        }else{
            $buttonId = get_option('peecho_button_id');
        }
		
        $snippets = get_option(Peecho::OPTION_KEY);
        
        echo "<p></p>"; 
        echo '<div style="background-color:#FFF; padding:20px; width:65%; min-height:200px;" >';           
        echo '<div class="pky"> <label>Peecho Api Key : </label>';
        echo '<input type="text" name="user_id" value="'.$userId.'" placeholder="Enter your Peecho API here"> </div>';
		echo '<div style="margin-top: 10px; margin-bottom: 15px; margin-left: 158px;"><a href="http://www.peecho.com/dashboard">Get your API Key here</a></div>';
        echo '<div class="pky"> <label>Peecho Button Key :</label>';
        echo '<input type="text" name="peecho_button_id" value="'.$buttonId.'" placeholder="Enter your Button Key here"></div>';
		printf("<div><input style='float:left' type='submit'  class='button sting' name='setting' value='%s' />", __('Save Changes', Peecho::TEXT_DOMAIN));
		echo '<p class="pcdc"><a href="https://www.peecho.com/en/publishers/publishers-print-button"> Peecho documentation page </a> </p></div>';
		echo '</div>';
		echo '</form>';
		//$this->saveSetting();
    }

    private function saveSetting()
    {
        if(isset($_POST['setting']))
        {		   
			if(!get_option( 'peecho_button_id' ) ) {
				add_option( 'peecho_button_id', '255', '', 'yes' );
				update_option( 'peecho_button_id', $_POST['peecho_button_id'] );
			}else {
				update_option( 'peecho_button_id', $_POST['peecho_button_id'] );
			}
			
            update_option('user_script_id', $_POST['user_id']);
            if(!empty($_POST['user_id'])){
                $this->message(
                    __('API Key and Button Key added. You can edit your buttons <a href="'.home_url().'/wp-admin/admin.php?page=customteam" >here</a>',
                        Peecho::TEXT_DOMAIN
                    )
                );				
            }
            if(empty($_POST['user_id'])){
                $_POST['user_id'] = '';
            }else{
								
                /*  echo '<script>
                   window.location = "?page=peecho%2Fpeecho.php&tab=tools";
                </script>';*/
                
            }
        }
		
    }
    
    public function overviewPage()
    {
        // Header
        echo '<div class="wrap">';
        echo '<h2>Peecho : Settings</h2>';
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
        $userId = get_option('peecho_button_id'); //get_option('user_script_id');
        echo '<script type="text/javascript">
           var p=document.createElement("script");p.type="text/javascript";p.async=true;
           var h=("https:"==document.location.protocol?"https://":"http://");
           p.src=h+"d3aln0nj58oevo.cloudfront.net/button/script/'.$userId.'.js";
           var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(p,s);
        </script>';
        
    }

    public function load_wp_media_files() {
        wp_enqueue_media();
    }
}
?>