<?php
$dir = plugin_dir_url( __FILE__ );
?>
<link rel="stylesheet" href="<?php echo $dir; ?>popup/dist/magnific-popup.css">
<script src="<?php echo $dir; ?>popup/dist/jquery.magnific-popup.min.js"></script>

<form method="post" action="" id="peecho-form">
    <?php wp_nonce_field('update_snippets', 'update_snippets_nonce'); ?>

    <table class="widefat fixed" id="peecho-plugin-upload" cellspacing="0">
        <thead>
            <tr>
                <th scope="col" class="check-column"><input type="checkbox" /></th>
                <th scope="col" style="width: 180px;"><?php _e('Title', Peecho::TEXT_DOMAIN); ?></th>
                <th>
                </th>
                <th scope="col"><?php _e('Snippet', Peecho::TEXT_DOMAIN); ?></th> 
            </tr>
        </thead>
        
        <tfoot>
        <tr>
            <th scope="col" class="check-column"><input type="checkbox" /></th>
            <th scope="col"><?php _e('Title', Peecho::TEXT_DOMAIN) ?></th>
            <th></th>
            <th scope="col"><?php _e('Snippet', Peecho::TEXT_DOMAIN) ?></th>
        </tr>
        </tfoot>
        <tbody>
<?php 
$snippets = get_option(Peecho::OPTION_KEY);
  
if (!empty($snippets)) {
    foreach ($snippets as $key => $snippet) {
        ?>
            <tr class='recent'>
            <th scope='row' class='check-column'><input type='checkbox'  name='checked[]' value='<?php echo $key;
        ?>' /></th>
            <td class='row-title'>
            <input type='text' name='<?php echo $key;
        ?>_title' value='<?php echo $snippet['title'];
        ?>' />
            </td>
            <td class='name'>
            <?php
            Peecho_Admin::checkbox(__('', Peecho::TEXT_DOMAIN), $key.'_shortcode',
                            $snippet['shortcode']);
        ?>
            </td>
            <td class='desc'>
            <textarea name="<?php echo $key;
        ?>_snippet" class="large-text" style='width: 100%;' rows="5"><?php echo htmlspecialchars($snippet['snippet'], ENT_NOQUOTES);
        ?></textarea>
        
        <?php 
			if(strlen($snippet['snippet']) > 0){
				echo 'This is your Peecho button code. By adjusting the button code you can tweak the look and feel of your button. See our <a href="http://www.peecho.com/en/documentation/print-button" target="_blank">documentation</a> for details.';
			}else{
				echo 'Click Upload publication to start uploading a file to Peecho. Peecho will analyze the file and generate the button code for you.';
			}
		?>
       </td>
       <td>
	<?php       		
		wp_enqueue_media();
    ?>
    <div>
       
        <input type="text" style="display:none" name="image_url['<?php echo $key; ?>']" id="image_url_<?php echo $key; ?>" class="">
        <input type="button" name="upload-btn" id="upload-btn-<?php echo $key; ?>" onclick="fileupload('<?php echo $key; ?>')" class="button-secondary" value="Upload publication">
    </div>
            
       </td>
       
       </tr>
            <br/>
            
        <?php
    }
}       else{
        $userId = get_option('user_script_id');
		if(empty($userId)){
			echo '<tr><td colspan="3"><div style="color:red"> You haven\'t created any print buttons yet. To create a button, please first specify your Peecho Button Key under <a href="options-general.php?page=peecho/peecho.php&&tab=tools">Settings.</a> 
			</div></td></tr>';                    
		}else{
			echo '<tr><td colspan="3"><div style="color:red">No Peecho print buttons added yet. Click "Add Button" to create your first print button.</div> </td></tr>';
        }
}
        ?>   
        </tbody>
    </table>
 <style>
	.mfp-image-holder .mfp-close, .mfp-iframe-holder .mfp-close {
		display: none !important;
	}
	img.mfp-img {
		padding: 0px !important;
	}	 
 </style>  
    
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
					src: '<?php echo $dir; ?>popup/ajax-loader.gif'
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
    
<input class="" type="hidden" value="Update Button" name="update-snippets">
<?php


        Peecho_Admin::submit('update-snippets', __('Update Button', Peecho::TEXT_DOMAIN));
        $userId = get_option('user_script_id');
        if(!empty($userId)){
            Peecho_Admin::submit('add-snippet', __('Add Button', Peecho::TEXT_DOMAIN), 'button-secondary', false);
        }else{
            Peecho_Admin::submit('add-snippet', __('Add Button', Peecho::TEXT_DOMAIN), 'button-secondary', false,true);
        }
        Peecho_Admin::submit('delete-snippets', __('Delete Selected', Peecho::TEXT_DOMAIN), 'button-secondary', false);
        echo '</form>';
		
