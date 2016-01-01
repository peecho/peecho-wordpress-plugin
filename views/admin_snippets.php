<?php
	$snippets = get_option(Peecho::OPTION_KEY);		
?>

<form method="post" action="" id="peecho-form">
    <?php wp_nonce_field('update_snippets', 'update_snippets_nonce'); ?>
    <div style="float: left; width: 58%;" > 
    <div class="count"><?php 
                if(isset($_GET['snippet'])){
                    $getedit = explode(",",urldecode($_GET['snippet']));
                    echo count($getedit);
                }else{
                    echo count($snippets); 
                }
    ?> Items</div>
    <table class="widefat " id="peecho-plugin-upload" cellspacing="0"  style=" height: auto;padding:10px;">
        <thead>
            <tr>
                <th scope="col" class="check-column"><input type="checkbox" id="checkall" /></th>
                <th scope="col" style="width: 180px;"><?php _e('Title', Peecho::TEXT_DOMAIN); ?></th>
                <th></th>
                <th scope="col"><?php _e('Print Button Code', Peecho::TEXT_DOMAIN); ?> <a style="float:right; text-decoration:underline;" target="_blank" href="https://www.peecho.com">Peecho dashboard</a></th> 
                <th></th>
            </tr>
        </thead>
        <tbody>
<?php   
$snippets = get_option(Peecho::OPTION_KEY);

if (!empty($snippets)) {	
	$getedit = explode(",",urldecode($_GET['snippet']));
    foreach ($snippets as $key => $snippet) {
    if($getedit[0] || $getedit[0]=='0') {
		if (in_array($key,$getedit)) {
?>
        <tr class='recent'>
            <th scope='row' class='check-column'><input type='checkbox'  name='checked[]' value='<?php echo $key; ?>' onclick="checkcheckbox()" /></th>
            <td class='row-title'>
                <input type='text' name='<?php echo $key; ?>_title' value='<?php echo $snippet['title']; ?>' /> 
            </td>
            <td class='name'>
                <?php
                    Peecho_Admin::checkbox(__('', Peecho::TEXT_DOMAIN), $key.'_shortcode', $snippet['shortcode']);
                ?>
            </td>
            <td class='desc'>
                <textarea name="<?php echo $key; ?>_snippet" class="large-text" style='width: 100%;' rows="5"><?php echo htmlspecialchars($snippet['snippet'], ENT_NOQUOTES); ?></textarea>
                <input type="button" name="upload-btn" id="upload-btn-<?php echo $key; ?>" onclick="fileupload('<?php echo $key; ?>')" class="button-secondary" value="Upload publication"><br/><br/>
                <a style="text-decoration:underline;" target="_blank" href="https://www.peecho.com"> edit on Peecho dashboard</a>
            </td>
            <td>
                <?php wp_enqueue_media(); ?>
                <div>
                    <input type="text" style="display:none" name="image_url['<?php echo $key; ?>']" id="image_url_<?php echo $key; ?>" class="">
                </div>
            </td>
        </tr>  
        <?php
		}
    }else{
?>
        <tr class='recent'>
            <th scope='row' class='check-column'><input type='checkbox'  name='checked[]' value='<?php echo $key; ?>' /></th>
            <td class='row-title'>
                <input type='text' name='<?php echo $key; ?>_title' value='<?php echo $snippet['title']; ?>' /> 
            </td>
            <td class='name'>
                <?php
                    Peecho_Admin::checkbox(__('', Peecho::TEXT_DOMAIN), $key.'_shortcode', $snippet['shortcode']);
                ?>
            </td>
            <td class='desc'>
                <textarea name="<?php echo $key; ?>_snippet" class="large-text" style='width: 100%;' rows="5"><?php echo htmlspecialchars($snippet['snippet'], ENT_NOQUOTES); ?></textarea>
                <input type="button" name="upload-btn" id="upload-btn-<?php echo $key; ?>" onclick="fileupload('<?php echo $key; ?>')" class="button-secondary" value="Upload publication"><br/><br/>
                <a style="text-decoration:underline;" target="_blank" href="https://www.peecho.com"> edit on Peecho dashboard</a>
            </td>
            <td>
                <?php wp_enqueue_media(); ?>
                <div>
                    <input type="text" style="display:none" name="image_url['<?php echo $key; ?>']" id="image_url_<?php echo $key; ?>" class="">
                </div>
            </td>
        </tr>
<?php 
       }
    }
} else{
        $userId = get_option('user_script_id');
		if(empty($userId)){
			echo '<tr><td colspan="3"><div style="color:red"> You haven\'t created any print buttons yet. To create a button, please first specify your Peecho Button Key under <a href="options-general.php?page='.BASENAME.'&tab=tools">Settings.</a> 
			</div></td></tr>';                    
		}else{
			echo '<tr><td colspan="3"><div style="color:red">No Peecho print buttons added yet. Click "Add Button" to create your first print button.</div> </td></tr>';
        }
    }
?>           
        </tbody>
    </table>
    <div class="pc-btn">
        <div class="submit"><input type="submit" class="button-secondary" value="Update Selected" name="update-snippets" onclick="return confirmUpdate();" disabled="disabled" id="editselect"></div>
        <div class="submit"><input type="submit" class="button-secondary" value="Delete Selected" name="delete-snippets" onclick="return confirmComplete();" disabled="disabled" id="deletedisable"></div>
    </div>
</div>
<?php
    echo'<div style="float: left; height: auto; width: 38%;padding:18px;">';
        echo '<div class="pc-why">
            <div class="ax_paragraph" id="u70">
        		<p> 
                    <img src="'. plugins_url( 'image/peecho.png', Peecho::FILE ).'" class="img " id="u70_img">
                   <span style="font: bold; font-size: 21px">Customize your button</span>
                </p>
                <div class="pc-ax">
                    <p>
                        <span style=" font-size: 14px;font-weight:700;">Managing your pricing and other button settings</span>
                        <span style="font-size: 13px;;font-weight:400;"></span>
                    </p>
                    <p>
                        <span style="font-size: 13px;;font-weight:400;">
                        If you want to change your default pricing to earn profit or update your publication, you will first need to make changes on the Peecho<a class="cust-anchor" href="https://www.peecho.com" target="_blank"> dashboard</a> before these are reflected on WordPress.</span>
                    </p>
    		        <p><span style=" font-size: 14px;;font-weight:700;">Button color and styling</span></p>
    		        <p><span style="font-size: 13px;;font-weight:400;">The button is green by default. However, we also offer the possibility of changing the color to blue. To change it, simply adjust the "data-theme" to blue in the Javascript code:<br/><br/>
                        <span class="data-text">data-theme=blue</span><br/><br/>
                        You can also customize the CSS to style the button differently. To do this, follow the steps described on<a href="https://www.peecho.com" target="_blank" class="cust-anchor"> this page</a> under "Use your own styling"</span>
                    </p>
                    <p><span style=" font-size: 14px;;font-weight:700;">Translation and localization</span></span></p>
    		        <p><span style="font-size: 13px;;font-weight:400;">The button text displays in English by default, but can be easily translated. You simply need to change the <span class="data-text">data-locale </span>variable. You can also set a matching currency for your language by modifying the <span class="data-text">data-country</span> or <span class="data-text">data-currency</span> variable. <a  href="https://www.peecho.com" target="_blank" class="cust-anchor">Read more</a></span></p>
                </div>
            </div>
        </div>';
        echo '<div class="pc-hlp">
                <span>Looking for help?</span>
                <p>
                  Маке sure to look at the <a href="https://www.peecho.com/en/publishers/publishers-print-button "> Peecho Documentation,</br> 
                  FAQ </a> and contact<a href="https://www.peecho.com/en/publishers/publishers-print-button"> support@peecho.com </a>if you have any questions.
                </p>';
        echo '</div>';
	echo '</div>';	
?>	    
    <input class="" type="hidden" value="Update Button" name="update-snippets">
    <input type="hidden" name="action" value="" id="prm_action">
</form>

<div class="modal fade" id="confirmDelete11" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Delete Parmanently</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure about this ?</p>
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-primary" id="confirmnew">Delete</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>        
      </div>
    </div>
  </div>
</div>



<script type="text/javascript">
	function confirmComplete(){
		jQuery('#confirmDelete11').modal();
		return false;
	}
	jQuery(document).ready(function(){
		jQuery('#confirmnew').click(function(){
			jQuery('#prm_action').val('delete');
			jQuery('#peecho-form').submit();
			return true;
		})
		
		jQuery('#checkall').click(function(event) {  //on click
			if(this.checked) { // check select status
				jQuery('.messageCheckbox').each(function() { //loop through each checkbox
					this.checked = true;  //select all checkboxes with class "checkbox1"
					checkcheckbox();              
				});
			}else{
				jQuery('.messageCheckbox').each(function() { //loop through each checkbox
					this.checked = false; //deselect all checkboxes with class "checkbox1"  
					checkcheckbox();                    
				});        
			}
		 });
		
	
	})
	function confirmUpdate(){
		jQuery('#prm_action').val('update');
		return true;
	}
	
	function checkcheckbox(){
		var checkedNum = jQuery('input[name="checked[]"]:checked').length;
		if (checkedNum > 0){
			jQuery('#deletedisable').prop('disabled',false);
			jQuery('#editselect').prop('disabled',false);
		}else{
			jQuery('#deletedisable').prop('disabled',true);
			jQuery('#editselect').prop('disabled',true);
		}
	}
	
</script>
<script type="text/javascript">
    function fileupload(id){
		var image = wp.media({ 
			title: 'Upload Image',
			//mutiple: true if you want to upload multiple files at once
			multiple: false
		}).open().on('select', function(e){
			var uploaded_image = image.state().get('selection').first();
			var image_url = uploaded_image.toJSON().url;
			jQuery('#image_url_'+id).val(image_url);
			jQuery.magnificPopup.open({
				items: {
					src: '<?php echo PLUGINURL; ?>popup/ajax-loader.gif'
				},
				closeOnBgClick : false,
				type: 'image'
	
			  // You may add options here, they're exactly the same as for $.fn.magnificPopup call
			  // Note that some settings that rely on click event (like disableOn or midClick) will not work here
			}, 0);
				
			setTimeout(function(){jQuery('#peecho-form').submit();},1000);
		});
    }
</script>  