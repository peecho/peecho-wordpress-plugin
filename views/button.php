<?php
	//ini_set('memory_limit', '-1');
	$dir = plugin_dir_url( __FILE__ );   
?>
<link rel="stylesheet" href="<?php echo $dir; ?>popup/bootstrap.min.css">
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="<?php echo $dir; ?>popup/dist/magnific-popup.css">
<script src="<?php echo $dir; ?>popup/dist/jquery.magnific-popup.min.js"></script>

<div class="pec-btn">
	<h3>Peecho: Buttons</h3>
	<?php
		$userId = get_option('user_script_id');
        $buttonId = get_option('peecho_button_id');
        if($userId != '' && $buttonId != ''){
	?>
		<button type="button"style="" class="" data-toggle="modal" data-target="#myModal">Add New</button>
	<?php } ?>
</div>
<?php
if(isset($_POST['add-snippets'])){  
    $snippets = get_option(Peecho::OPTION_KEY);
    if (empty($snippets)) {
        $snippets = array();
    }
	$key = $_POST['key'];
			
	if(!empty($_POST['image_url']["'".$key."'"]))
	{
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
				array_push(
					$snippets,
					array(
						'title' => trim($_POST[$key.'_title']),
						'vars' => str_replace(' ', '', trim($_POST[$key.'_vars'])),
						'description' => wp_specialchars_decode(trim(stripslashes($_POST[$key.'_description'])), ENT_NOQUOTES),
						'shortcode' => true,
						'php' => false,
						'wptexturize' => false,
						'snippet' => wp_specialchars_decode(trim(stripslashes($snippetsvalue)), ENT_NOQUOTES)
					)
				);				
			}
		}while($state == 'PROCESSING');
	}
	else{
		if(!empty($_POST['text_url']["'".$key."'"])){			
			$snippetsvalue = $_POST['text_url']["'".$key."'"];
			array_push(
				$snippets,
				array(
					'title' => trim($_POST[$key.'_title']),
					'vars' => str_replace(' ', '', trim($_POST[$key.'_vars'])),
					'description' => wp_specialchars_decode(trim(stripslashes($_POST[$key.'_description'])), ENT_NOQUOTES),
					'shortcode' => true,
					'php' => false,
					'wptexturize' => false,
					'snippet' => wp_specialchars_decode(trim(stripslashes($snippetsvalue)), ENT_NOQUOTES)
				)
			);
		}
	}
    update_option(Peecho::OPTION_KEY, $snippets);
    echo 'Snippets have been updated.';
}
			

if(isset($_POST['checked'][0])){
    $snippets = get_option(Peecho::OPTION_KEY);
    if (empty($snippets) || !isset($_POST['checked'])) {
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
}
			
?>

<!-- button listing -->
<form method="post" action="" id="">
	<?php $snippets = get_option(Peecho::OPTION_KEY); ?>
	<?php //echo "<pre>"; print_r($snippets); echo "</pre>"; ?>
	<div style="">
		<div class="text-center" style="margin-right:17%;"></div>
		<table class="widefat fixed" id="peecho-plugin-upload" cellspacing="0" style="width: auto; margin-bottom:20px; margin-top:20px" >		
		    <thead>
		        <tr>
		            <th scope="col" class="check-column"><input id="checkall"   type="checkbox" /></th>
		            <th scope="col" style="width: 180px;"><?php _e('Name', Peecho::TEXT_DOMAIN); ?></th>
		            <th> </th>
		            <th scope="col"><?php _e('Preview', Peecho::TEXT_DOMAIN); ?></th>
		            <th scope="col"><?php echo count($snippets); ?> Items </th>
		        </tr>
		    </thead>
		    <tbody>
		        <?php   
					if (!empty($snippets)) {
						$totalkey = 1;
		    			foreach ($snippets as $key => $snippet) {
		        ?>
		        <tr class='recent'>
		            <th scope='row' class='check-column'><input type='checkbox' class="messageCheckbox" name='checked[]' value='<?php echo $key; ?>' /></th>
		            <td class='row-title'><?php echo $snippet['title']; ?></td>
		            <td class='name'>
		            	<?php
		              		Peecho_Admin::checkbox(__('', Peecho::TEXT_DOMAIN), $key.'_shortcode',
		                            $snippet['shortcode']);
		           		?>
		       		</td>
		            <td class='desc'><a href=""> <?php echo $snippet['snippet'];?> </a></td>
		            <td><?php       		
						wp_enqueue_media();
		    		?></td>
		        </tr>
		        <?php
						$totalkey++;
		    		}	
				}      
		        ?>
		    </tbody>
		</table>
	  	<button type="button" onclick="checkedurl()" style="text-decoration:none;" disabled="disabled" class="button-secondary" id="editselect"><span> Edit Selected </span></button>
	  	<button class="button-secondary deletedisable" type="button" data-toggle="modal" data-target="#confirmDelete" data-title="Delete Button" data-message="Are you sure you want to delete this button ?" name="delete-snippets">Delete Selected</button>
		<?php 
			//echo Peecho_Admin::submit('delete-snippets', __('Delete Selected', Peecho::TEXT_DOMAIN), 'button-secondary deletedisable', false);
			$dir = plugin_dir_url(__FILE__); 
			$x   = plugin_basename( __FILE__ );
			$plugin_dir_path = ABSPATH . 'wp-content/plugins/'.$x
			
		?>
	</div>
	<div style="height: auto; width: 30%; padding:3px;margin-top:50px;">
        <div class="pc-why">
            <div class="ax_paragraph" id="u70">
        		<p> 
                    <img src="<?php echo plugins_url( 'image/peecho.png', $x ) ?>" class="img " id="u70_img">
                   <span style="font-weight:bold; font-size: 21px">Quick Tip</span>
                </p>
                <div class="pc-ax">
    		        <p><span style=" font-size: 13px;;font-weight:normal;">To add your button code to any post or page, use the short code tool located in the post editor.</span></p>
                </div>
            </div>
        </div>
        
	</div>
</form>
<!-- Modal Dialog -->
<div class="modal fade" id="confirmDelete" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
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
      	<button type="button" class="btn btn-primary" id="confirm">Delete</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>        
      </div>
    </div>
  </div>
</div>

<?php 

   $apiid = get_option('peecho_button_id'); 
	$dir = plugin_dir_url( __FILE__ ); 
	$x = dirname(dirname(__FILE__));
	
  
 ?>
<!-- Dialog show event handler -->
<script type="text/javascript">
  jQuery('#confirmDelete').on('show.bs.modal', function (e) {
      $message = jQuery(e.relatedTarget).attr('data-message');
      jQuery(this).find('.modal-body p').text($message);
      $title = jQuery(e.relatedTarget).attr('data-title');
      jQuery(this).find('.modal-title').text($title);
      // Pass form reference to modal for submission on yes/ok
      var form = jQuery(e.relatedTarget).closest('form');
      jQuery(this).find('.modal-footer #confirm').data('form', form);
  });

  <!-- Form confirm (yes/ok) handler, submits form -->
  jQuery('#confirmDelete').find('.modal-footer #confirm').on('click', function(){
      jQuery(this).data('form').submit();
  });
</script>

<script language="javascript" type="text/javascript">
	jQuery(document).ready(function(){
		setTimeout(function(){jQuery('.deletedisable').attr('disabled','disabled');},500)
		jQuery('.messageCheckbox').click(function(){
			checkcheckbox();
		})
		jQuery('.checkhead').click(function(){
			checkcheckbox();	
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
	});


	function checkcheckbox(){
		var checkedNum = jQuery('input[name="checked[]"]:checked').length;
		if (checkedNum > 0){
			jQuery('.deletedisable').prop('disabled',false);
			jQuery('#editselect').prop('disabled',false);
		}else{
			jQuery('.deletedisable').prop('disabled',true);
			jQuery('#editselect').prop('disabled',true);
		}
	}
	
</script>


<script type="text/javascript">
	var p=document.createElement("script");p.type="text/javascript";p.async=true;
	var h=("https:"==document.location.protocol?"https://":"http://");
	p.src=h+"d3aln0nj58oevo.cloudfront.net/button/script/<?php echo $apiid; ?>.js";
	var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(p,s);
</script>



<script type="text/javascript">
	function checkedurl(){
		var checkedValue = new Array(); 
		var checkedval = '';
		var inputElements = document.getElementsByClassName('messageCheckbox');
		for(var i=0; inputElements[i]; ++i){
			if(inputElements[i].checked){
				checkedValue.push(inputElements[i].value);
			}
		}	
		//console.log(checkedValue); 
		var checkval = checkedValue.join();
		window.location = "<?php echo home_url(); ?>/wp-admin/admin.php?page=<?php echo $x; ?>/peecho.php&tab=snippets&snippet="+checkval;
	}   
	function fileupload(id){
		var fileName, fileExtension;
		var image = wp.media({ 
			title: 'Upload Image',
			//mutiple: true if you want to upload multiple files at once
			multiple: false
		}).open().on('select', function(e){
			var uploaded_image = image.state().get('selection').first();			
			var image_url = uploaded_image.toJSON().url;
			fileExtension = image_url.replace(/^.*\./, '');			
			switch (fileExtension) {
	            case 'png': case 'jpeg': case 'jpg':
	            	jQuery('#divFiles').text('');
	                jQuery('#image_url_'+id).val(image_url);
					setTimeout(function(){jQuery('#uploadfilename').html(uploaded_image.toJSON().filename);},500);
	                break;
	            case 'pdf':
	            	jQuery('#divFiles').text('');
	                jQuery('#image_url_'+id).val(image_url);
					setTimeout(function(){jQuery('#uploadfilename').html(uploaded_image.toJSON().filename);},500);
	                break;
	            default:
	                jQuery('#divFiles').text('Please Upload Image or Pdf File.');
	                jQuery('#image_url_'+id).val('');
					setTimeout(function(){jQuery('#uploadfilename').html(uploaded_image.toJSON().filename);},500);
        	}			
		});
	}
	
	jQuery(document).ready(function(){
		jQuery('#peecho-form').submit(function(){
			// Setup form validation on the #register-form element
			var title = jQuery("#title").val();
			var tarea = jQuery("#second-text-area").val();
			var img = new String($(".image-url").attr("id"));
			var imgurl = jQuery('#'+img).val();
			if(!title)
			{
				alert('Please enter the title...');
				return false;
			}else
			{
				if(!imgurl && !tarea){
					alert('Please upload a file...');
					return false;
				}else{				
		        	jQuery('#myModal').modal('hide');
					jQuery.magnificPopup.open({
						items: {
							src: '<?php echo $dir; ?>popup/ajax-loader.gif'
						},
						closeOnBgClick : false,
						type: 'image'
			
					  // You may add options here, they're exactly the same as for $.fn.magnificPopup call
					  // Note that some settings that rely on click event (like disableOn or midClick) will not work here
					}, 0);	
					return true;
				}
			}
		 });
	});
	
</script>

<div class="container">
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h3 class="modal-title">
                        Add New Peecho Button
                    </h3>
                </div>
                <div class="modal-body">
                    <?php $totalbutton = $totalkey+1 ; ?>
                    <form method="post" action="" id="peecho-form" novalidate="novalidate">
                    	<div style="margin-left:20px">
								<?php _e('<b>Button Name</b>', Peecho::TEXT_DOMAIN) ?>
                        </div>
						<div  style="margin:20px; margin-top:10px;">
                                <input style="width:520px" type='text' name='<?php echo $totalbutton; ?>_title' value='' id="title"/>
                                <input type="hidden" style="display:none" name="image_url['<?php echo $totalbutton; ?>']" id="image_url_<?php echo $totalbutton; ?>" class="image-url">
                        </div>
                                                
                        <div style="display:none;margin-left:20px" id="next-part">
	                        <h3> Print button code</h3>
	                        <p>You can find your button code under Publications > Details > Print Button in the Peecho <a href="https://www.peecho.com" target="_blank">dashboard</a></p>
	                        <textarea style="width:530px" id="second-text-area" name="text_url['<?php echo $totalbutton; ?>']"></textarea><br>
	                        <a id="ancher-show-again" style="text-decoration:none"><span style="color:black">or skip and</span> <u>upload publication</u></a>
                        </div>                        
                        <div style="margin:20px" id="upload-button">
                        		<?php _e('<b>Publication</b>', Peecho::TEXT_DOMAIN) ?><br/>
                                <input type="button" name="upload-btn" id="upload-btn-<?php echo $totalbutton; ?>" onclick="fileupload('<?php echo $totalbutton; ?>')" class="button-secondary" value="Upload publication">
                                <span id="uploadfilename" style="color:#600;margin-left:10px;"></span> 
                                <a id="ancher-show"><span style="color:black">or skip and</span> <u>paste button code directly</u></a>                                
                                <input class="" type="hidden" value="Add Button" name="add-snippets">
                                <input class="" type="hidden" value="<?php echo $totalbutton; ?>" name="key">
                         </div>
                         <div style="margin:20px">
                                <?php Peecho_Admin::submit('add-snippet', __('Add', Peecho::TEXT_DOMAIN), 'button-secondary button-add', false); ?>                            
                                <button style="border:none" data-dismiss="modal" class="btn button-secondary button-cancel" type="button">Cancel</button>
                                <div style="margin-top:10px;color:red;display:block;" id="divFiles"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('#myModal').on('hidden.bs.modal', function (e) {
	  	jQuery(this)
	    .find("#title,textarea")
	       .val('')
	       .end()
	    .find("#uploadfilename")
	       .html('')
	       .end();
	});

	jQuery('#ancher-show').click(function(){
			jQuery('#next-part').show();
			jQuery('#upload-button').hide();
			jQuery('#uploadfilename').text('');
		})	
});
jQuery(document).ready(function(){
	jQuery('#ancher-show-again').click(function(){
			jQuery('#next-part').hide();
			jQuery('#upload-button').show();
			jQuery('#second-text-area').val('');
		})	
});
</script>