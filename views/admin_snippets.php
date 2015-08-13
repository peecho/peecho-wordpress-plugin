
<form method="post" action="">
    <?php wp_nonce_field('update_snippets', 'update_snippets_nonce'); ?>

    <table class="widefat fixed" cellspacing="0">
        <thead>
        <tr>
            <th scope="col" class="check-column"><input type="checkbox" /></th>
            <th scope="col" style="width: 180px;"><?php _e('Title', Peecho::TEXT_DOMAIN); ?></th>
            <th></th>
            <th scope="col"><?php _e('Snippet', Peecho::TEXT_DOMAIN); ?></th> 
        </tr>
        </thead>
        <?php
        ?>
             
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
       <div >Enter your Peecho button code here. You can find your Peecho button key on <a href="http://www.peecho.com/dashboard"  target="_blank">http://www.peecho.com/dashboard </a>, under Publications > Details > Print button</div> </td></tr>
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
