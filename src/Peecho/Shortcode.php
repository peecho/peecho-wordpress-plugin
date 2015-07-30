<?php
class Peecho_Shortcode
{
    public function __construct()
    {
        $this->create();
    }
    public function create()
    {
        $snippets = get_option(Peecho::OPTION_KEY);
        if (!empty($snippets)) {
            foreach ($snippets as $snippet) {
                // If shortcode is enabled for the snippet, and a snippet has been entered, register it as a shortcode.
                if ($snippet['shortcode'] && !empty($snippet['snippet'])) {
                    $vars = explode(",", $snippet['vars']);
                    $vars_str = "";
                    foreach ($vars as $var) {
                        $attribute = explode('=', $var);
                        $default_value = (count($attribute) > 1) ? $attribute[1] : '';
                        $vars_str .= "\"{$attribute[0]}\" => \"{$default_value}\",";
                    }
                    $texturize = isset($snippet["wptexturize"]) ? $snippet["wptexturize"] : false;

                    add_shortcode(
                        $snippet['title'],
                        create_function(
                            '$atts,$content=null',
                            '$shortcode_symbols = array('.$vars_str.');
                            extract(shortcode_atts($shortcode_symbols, $atts));
                            $attributes = compact( array_keys($shortcode_symbols) );
                            if ( $content != null )
                                $attributes["content"] = $content;
                            $snippet = \''. addslashes($snippet["snippet"]) .'\';
                            foreach ($attributes as $key => $val) {
                                $snippet = str_replace("{".$key."}", $val, $snippet);
                            }
                            $php = "'. $snippet["php"] .'";
                            if ($php == true) {
                                $snippet = Peecho_Shortcode::phpEval( $snippet );
                            }
                            $snippet = do_shortcode(stripslashes($snippet));
                            $texturize = "'. $texturize .'";
                            if ($texturize == true) {
                                $snippet = wptexturize( $snippet );
                            }
                            return $snippet;'
                        )
                    );
                }
            }
        }
        
    }
    public static function phpEval($content)
    {
        if (defined('POST_SNIPPETS_DISABLE_PHP')) {
            return $content;
        }

        $content = stripslashes($content);

        ob_start();
        eval($content);
        $content = ob_get_clean();

        return addslashes($content);
    }
}
