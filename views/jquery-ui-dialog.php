<?php // Setup the dialog divs ?>
       
<div class="hidden">
    <div id="peecho-dialog" title="Insert Peecho Print Button">

        <?php // Init the tabs div ?>
        <div id="peecho-tabs">

            <h2 id="peechonav">Choose Button</h2>
            <ol class="peechoselect">
                <?php
                foreach ($snippets as $key => $snippet) {
                    ?>
                    <li onclick="jQuery('#peecho-shortcode-<?php echo $key;
                    ?>-radio').attr('checked','checked')" ><input id="peecho-shortcode-<?php echo $key;?>-radio" type="radio"  value="title" name="title" onclick="jQuery('#peecho-shortcode-<?php echo $key;
                    ?>').trigger('click');"><a href="#ps-tabs-<?php echo $key;
                    ?>"  id="peecho-shortcode-<?php echo $key;
                    ?>" onclick="jQuery('#peecho-shortcode-<?php echo $key;
                    ?>-radio').attr('checked','checked')">
					<?php echo ucwords($snippet['title']); ?></a></li>
                <?php 
                } ?>

                 <?php
                 $userId = get_option('user_script_id');
                if(empty($userId)){
                   echo '<div style="color:red"> You haven\'t created any print buttons yet. To create a button, go to the plugin  <a href="options-general.php?page=peecho/peecho.php&&tab=tools">Settings</a>.
                    </div>';
                 }else{
                    if(count($snippets) == 0){
                        echo '<div style="color:red"> You haven\'t created any print buttons yet. To create a button, go to the plugin  <a href="options-general.php?page=peecho/peecho.php&&tab=tools">Settings</a>.';
                    }

                }?>

            </ol>
             
            <?php
            foreach ($snippets as $key => $snippet) {
                ?>
                <div id="ps-tabs-<?php echo $key;
                ?>">
                <?php
                if (isset($snippet['description'])) {
                    ?>
                    <p class="howto"><?php echo $snippet['description'];
                    ?></p>
                <?php 
                }
                $var_arr = explode(',', $snippet['vars']);
                if (!empty($var_arr[0])) {
                    foreach ($var_arr as $key_2 => $var) {
                        $def_pos = strpos($var, '=');
                        if ($def_pos !== false) {
                            $split = explode('=', $var);
                            $var = $split[0];
                            $def = $split[1];
                        } else {
                            $def = '';
                        }
                        ?>
                        <label for="var_<?php echo $key.'_'.$key_2;
                        ?>"><?php echo $var;
                        ?>:</label>
                        <input type="text" id="var_<?php echo $key.'_'.$key_2;
                        ?>" name="var_<?php echo $key.'_'.$key_2;
                        ?>" value="<?php echo $def;
                        ?>" style="width: 190px" />
                        <br/>
                    <?php 
                    }
                } else {
                    if (empty($snippet['description'])) {
                        ?>
                        <p class="howto"><?php _e('', Peecho::TEXT_DOMAIN);
                        ?></p>
                    <?php 
                    }
                }
                ?>
                </div>
            <?php 
            } ?>
    

                </div>
</div>
<style>
#peechonav {
    width: 200px;
    background: #fff;
    color:  #222;
    line-height: 25px;
    font-size: 14px;
    padding: 0 10px;
    cursor: pointer;
}

.peechoselect li a{
	text-decoration:none !important;
	outline:none !important;
}
.peechoselect li{
	border:none !important;
	outline:none !important;
}
.peechoselect li:focus{outline: 0 !important;}
.peechoselect li a:focus{outline: 0 !important;}
.peechoselect li{
	border:none !important;
	outline:none !important;
}
#peechonav ol li{outline:0 !important;}
#peechonav ol:hover{outline:0 !important;}

</style>
<script>
var nav = jQuery('#peechonav');
var selection = jQuery('.peechoselect');
var select = selection.find('li');
nav.click(function(event) {
    if (nav.hasClass('active')) {
        nav.removeClass('active');
        selection.stop().slideUp(200);
    } else {
        nav.addClass('active');
        selection.stop().slideDown(200);
    }
    event.preventDefault();
});
select.click(function(event) {
    // updated code to select the current language
    select.removeClass('active');
    jQuery(this).addClass('active');
});
function getpeechoshortcut(ID){
   //jQuery('#'+ID).trigger('click');
   //jQuery('#'+ID+'-radio').attr('checked','checked');
   //alert('here');
}

</script>
