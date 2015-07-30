<!-- START: Peecho UI Dialog -->
<?php // Setup the dialog divs ?>
<div class="hidden">
    <div id="peecho-dialog" title="Insert Peecho print Button">
        <?php // Init the tabs div ?>
        <div id="peecho-tabs">

            <h2 id="peechonav">Choose Button</h2>
            <ol class="peechoselect">
                

           
                <?php
                // Create a tab for each available snippet
                foreach ($snippets as $key => $snippet) {
                    ?>
                    <li onclick="getpeechoshortcut('peecho-shortcode-<?php echo $key;
                    ?>')" ><a href="#ps-tabs-<?php echo $key;
                    ?>"  id="peecho-shortcode-<?php echo $key;
                    ?>"><?php echo $snippet['title'];
                    ?></a></li>
                <?php 
                } ?>
            </ol>

            <?php
            // Create a panel with form fields for each available snippet
            foreach ($snippets as $key => $snippet) {
                ?>
                <div id="ps-tabs-<?php echo $key;
                ?>">
                <?php
                // Print a snippet description is available
                if (isset($snippet['description'])) {
                    ?>
                    <p class="howto"><?php echo $snippet['description'];
                    ?></p>
                <?php 
                }

                // Get all variables defined for the snippet and output them as
                // input fields
                $var_arr = explode(',', $snippet['vars']);
                if (!empty($var_arr[0])) {
                    foreach ($var_arr as $key_2 => $var) {
                        // Default value exists?
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
                    // If no variables and no description available, output a text
                    // to inform the user that it's an insert snippet only.
                    if (empty($snippet['description'])) {
                        ?>
                        <p class="howto"><?php _e('', Peecho::TEXT_DOMAIN);
                        ?></p>
                    <?php 
                    }
                }
                ?>
                </div><!-- #ps-tabs-<?php echo $key;
                ?> -->
            <?php 
            }
        // Close the tabs and dialog divs ?>
        </div><!-- #peecho-tabs -->
    </div><!-- #peecho-dialog -->
</div><!-- .hidden -->
<!-- END: Peecho UI Dialog -->



<style>
#peechonav {
    width: 200px;
    background: #222;
    color:  #eee;
    line-height: 25px;
    font-size: 14px;
    padding: 0 10px;
    cursor: pointer;
}
ol.peechoselect {
    display: none;
}

ol.peechoselect > li {
    width: 200px;
    background: #eee;
    line-height: 25px;
    font-size: 14px;
    padding: 0 10px;
    cursor: pointer;
}

ol.peechoselect > li:hover {
    background: #aaa;
}
ol.peechoselect > li.active{
   background:  #3a7e8c;
   
}
ol.peechoselect > li.active a{
   color:  #fff !important;
   
}
ol.peechoselect > li a{
   text-decoration: none;
   
}
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
   jQuery('#'+ID).trigger('click');
}
</script>
