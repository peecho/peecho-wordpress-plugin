<?php
$dir = plugin_dir_url( __FILE__ );
?>
<link rel="stylesheet" href="<?php echo $dir; ?>popup/dist/magnific-popup.css">
<script src="<?php echo $dir; ?>popup/dist/jquery.magnific-popup.min.js"></script>

<form method="post" action="" id="peecho-button" ">
    <?php wp_nonce_field('update_snippets', 'update_snippets_nonce'); ?>

    <table class="widefat fixed" id="peecho-plugin-upload" cellspacing="0">
        <thead>
            <tr>
                <th scope="col" class="check-column"><input type="checkbox" /></th>
                <th scope="col" style="width: 180px;"><?php _e('Name', Peecho::TEXT_DOMAIN); ?></th>
                <th>
                </th>
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
    foreach ($snippets as $key => $snippet) {
        ?>
            <tr class='recent'>
            <th scope='row' class='check-column'><input type='checkbox'  name='checked[]' value='<?php echo $key;
        ?>' /></th>
            <td class='row-title'>
        <a href=""><?php echo $snippet['title'];
        ?></a>
            </td>
            <td class='name'>
            <?php
            Peecho_Admin::checkbox(__('', Peecho::TEXT_DOMAIN), $key.'_shortcode',
                            $snippet['shortcode']);
        ?>
            </td>
            <td class='desc'>
     <a href="">     <?php echo $snippet['snippet'];?> </a>
          <script type="text/javascript">
   var p=document.createElement("script");p.type="text/javascript";p.async=true;
   var h=("https:"==document.location.protocol?"https://":"http://");
   p.src=h+"d3aln0nj58oevo.cloudfront.net/button/script/13032875623450.js";
   var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(p,s);
</script>
       </td>
       <td>
       	<?php       		
		wp_enqueue_media();
    ?>
       </td>
       </tr>
            <br/>
            
        <?php
    }
}      
        ?>   
        </tbody>
    </table></br>
<div>
<a href="?page=peecho%2Fpeecho.php&tab=snippets"> <span style="background-color: rgb(224, 224, 224); padding: 4px 23px;  color: #000;  border-radius: 5px;">Edit Selected </span></a>
<a href=""> <span style="background-color: rgb(224, 224, 224); width: 11%; padding-top: 4px; padding-bottom: 4px; padding-left: 18px;  color: #000;   border-radius: 5px;margin-left:35px;padding-right: 17px;">Deleted Selected </span>
</div></a>

<?php
        echo '</form>';
		?>
     
     
     
    

 
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<div class="container">
<div style="margin-top: -446px; margin-left: 257px;">
  <button type="button"style="border: 0px none; background: rgb(224, 224, 224) none repeat scroll 0% 0%; padding: 6px 15px 7px; border-radius: 3px;" class="" data-toggle="modal" data-target="#myModal">Add New</button></div>
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add New Peecho Button</h4>
        </div>
        <div class="modal-body">
          <p>Button Name</p>
          <input type="text" />
         <p>Publication</p>
         <p> <input type="text" style="display:none" name="image_url['<?php echo $key; ?>']" id="image_url_<?php echo $key; ?>" class="">
        <input type="button" name="upload-btn" id="upload-btn-<?php echo $key; ?>" onclick="fileupload('<?php echo $key; ?>')" class="button-secondary" value="Upload publication">
     
or skip and <a href="">paste button code directly</a></p>
<button type="button" style="border-radius: 5px; color: rgb(255, 255, 255);width:22%; background-color: -moz-html-cellhighlight; padding: 4px 39px;">Add</button> <button type="button" style="background-color: rgb(224, 224, 224);width:22%; padding: 4px 36px; color: #000;  border-radius: 5px;">Cancel</button>
    </div>
  </div>
</div>
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
