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
                    <li onclick="getpeechoshortcut('peecho-shortcode-<?php echo $key;
                    ?>')" ><input id="peecho-shortcode-<?php echo $key;?>-radio" type="radio"  value="title" name="title" ><a href="#ps-tabs-<?php echo $key;
                    ?>"  id="peecho-shortcode-<?php echo $key;
                    ?>"><?php echo $snippet['title'];
                    ?></a></li>
                <?php 
                } ?>
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
   jQuery('#'+ID+'-radio').attr('checked','checked');
   alert('here');
}
</script>
