<?php
	$dir = plugin_dir_url( __FILE__ );   
?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="<?php echo $dir; ?>popup/dist/magnific-popup.css">
<script src="<?php echo $dir; ?>popup/dist/jquery.magnific-popup.min.js"></script>

<button type="button"style="" class="" data-toggle="modal" data-target="#myModal">Add New</button>

<?php

	if(isset($_POST['add-snippets'])){  
            $snippets = get_option(Peecho::OPTION_KEY);
            if (empty($snippets)) {
                $snippets = array();
            }
			       $key = $_POST['key'];
			
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
								
								$snippetsvalue = '<a title="Peecho" href="http://www.peecho.com/" class="peecho-print-button" data-filetype="'.$type.'" data-width="'.$width.'" data-height="'.$height.'" data-pages="'.$noofpage.'" data-publication="'.$publicationid.'">Print</a>
	';							
								array_push(
									$snippets,
									array(
										'title' => trim($_POST[$key.'_title']),
										'vars' => str_replace(' ', '', trim($_POST[$key.'_vars'])),
										'description' => wp_specialchars_decode(trim(stripslashes($_POST[$key.'_description'])), ENT_NOQUOTES),
										'shortcode' => false,
										'php' => false,
										'wptexturize' => false,
										'snippet' => wp_specialchars_decode(trim(stripslashes($snippetsvalue)), ENT_NOQUOTES)
									)
								);
								
							}
						}while($state == 'PROCESSING');
						
					}
					
                update_option(Peecho::OPTION_KEY, $snippets);
                echo 'Snippets have been updated.';
			}
			
			
        if(isset($_POST['delete-snippets'])){
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

<table class="widefat fixed" id="peecho-plugin-upload" cellspacing="0" style="width: auto; margin-bottom:20px; margin-top:20px" >
    <thead>
        <tr>
            <th scope="col" class="check-column"><input type="checkbox" /></th>
            <th scope="col" style="width: 180px;"><?php _e('Name', Peecho::TEXT_DOMAIN); ?></th>
            <th> </th>
            <th scope="col"><?php _e('Preview', Peecho::TEXT_DOMAIN); ?></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th scope="col" class="check-column"><input type="checkbox" /></th>
            <th scope="col"><?php _e('Peecho', Peecho::TEXT_DOMAIN) ?></th>
            <th></th>
            <th scope="col"><?php _e('Preview', Peecho::TEXT_DOMAIN) ?></th>
        </tr>
    </tfoot>
    <tbody>
        <?php  
			$snippets = get_option(Peecho::OPTION_KEY);
  
if (!empty($snippets)) {
	$totalkey = 1;
    foreach ($snippets as $key => $snippet) {
        ?>
        <tr class='recent'>
            <th scope='row' class='check-column'><input type='checkbox' class="messageCheckbox" name='checked[]' value='<?php echo $key;
        ?>' /></th>
            <td class='row-title'><?php echo $snippet['title']; ?></td>
            <td class='name'><?php
              Peecho_Admin::checkbox(__('', Peecho::TEXT_DOMAIN), $key.'_shortcode',
                            $snippet['shortcode']);
           ?></td>
            <td class='desc'><a href=""> <?php echo $snippet['snippet'];?> </a> 
            <?php $apiid = get_option('peecho_button_id'); ?>
            </td>
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
<!--<div>-->  


  	<a onclick="checkedurl()"  style="text-decoration:none; " class="button-secondary"> Edit Selected </span></a>
    
   	
<?php 
	echo Peecho_Admin::submit('delete-snippets', __('Delete Selected', Peecho::TEXT_DOMAIN), 'button-secondary', false);
?>
</form>

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
		console.log(checkedValue); 
		var checkval = checkedValue.join();
		window.location = "?page=peecho%2Fpeecho.php&tab=snippets&snippet="+checkval;
	}   
	function fileupload(id){
		var image = wp.media({ 
			title: 'Upload Image',
			//mutiple: true if you want to upload multiple files at once
			multiple: false
		}).open().on('select', function(e){
			var uploaded_image = image.state().get('selection').first();
			var image_url = uploaded_image.toJSON().url;
			jQuery('#image_url_'+id).val(image_url);
			setTimeout(function(){jQuery('#uploadfilename').html(uploaded_image.toJSON().filename);},500);
		});
	}
	
	jQuery(document).ready(function(){
		jQuery('#peecho-form').submit(function(){
			    $('#myModal').modal('hide');
				jQuery.magnificPopup.open({
					items: {
						src: '<?php echo $dir; ?>popup/ajax-loader.gif'
					},
					closeOnBgClick : false,
					type: 'image'
		
				  // You may add options here, they're exactly the same as for $.fn.magnificPopup call
				  // Note that some settings that rely on click event (like disableOn or midClick) will not work here
				}, 0);		 
		 });
		
	
	})
	
</script>

<div class="container">
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        Add New Peecho Button
                    </h4>
                </div>
                <div class="modal-body">
                    <?php $totalbutton = $totalkey+1 ; ?>
                    <form method="post" action="" id="peecho-form">
                    	<div style="margin-left:20px">
								<?php _e('Button Name', Peecho::TEXT_DOMAIN) ?>
                        </div>
						<div  style="margin:20px; margin-top:10px;">
                                <input style="width:520px" type='text' name='<?php echo $totalbutton; ?>_title' value='' />
                                <input type="text" style="display:none" name="image_url['<?php echo $totalbutton; ?>']" id="image_url_<?php echo $totalbutton; ?>" class="">
                        </div>
                        <div style="margin:20px">
                                <input type="button" name="upload-btn" id="upload-btn-<?php echo $totalbutton; ?>" onclick="fileupload('<?php echo $totalbutton; ?>')" class="button-secondary" value="Upload publication"> 
                                <span id="uploadfilename" style="color:#600"></span>
                                <input class="" type="hidden" value="Add Button" name="add-snippets">
                                <input class="" type="hidden" value="<?php echo $totalbutton; ?>" name="key">
                         </div>
                         <div style="margin:20px">
                                <?php Peecho_Admin::submit('add-snippet', __('Add Button', Peecho::TEXT_DOMAIN), 'button-secondary', false); ?>
                               
                                 <button data-dismiss="modal" class="btn button-secondary" type="button">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>