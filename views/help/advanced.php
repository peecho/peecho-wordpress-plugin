<h2><?php _e('Advanced', $td); ?> (<em><?php _e('for developers', $td); ?></em>)</h2>

<p>
You can add constants to wp-config.php or the theme’s functions.php file to control some aspects of the plugin. Available constants to set are:
</p>

<pre><code>// Allow users with edit_posts capability access to the Peecho admin.
define('PEECHO_ALLOW_EDIT_POSTS', true);

// Disable PHP Execution in snippets, and removes the options from admin.
define('PEECHO_DISABLE_PHP', true);
</code></pre>

<p>
<?php _e('You can retrieve a Peecho directly from PHP, in a theme for instance, by using the Peecho::getSnippet() method.', Peecho::TEXT_DOMAIN); ?>
</p>

<h2><?php _e('Usage', Peecho::TEXT_DOMAIN); ?></h2>
<p>
<code>
&lt;?php $my_snippet = Peecho::getSnippet( $snippet_name, $snippet_vars ); ?&gt;
</code></p>

<h2><?php _e('Parameters', Peecho::TEXT_DOMAIN); ?></h2>
<p>
<strong>$snippet_name</strong><br/>
<?php _e('(string) (required) The name of the snippet to retrieve.', Peecho::TEXT_DOMAIN); ?>
<br/><br/>
<strong>$snippet_vars</strong><br/>
<?php _e('(string) The variables to pass to the snippet, formatted as a query string.', Peecho::TEXT_DOMAIN); ?>
</p>


<h2><?php _e('Example', Peecho::TEXT_DOMAIN); ?></h2>

<pre><code>// Use querystring for variables
$mySnippet = Peecho::getSnippet('internal-link', 'title=Awesome&url=2011/02/awesome/');
echo $mySnippet;

// Use array for variables
$mySnippet = Peecho::getSnippet('internal-link', array('title' => 'Awesome', 'url' => '2011/02/awesome/');
echo $mySnippet;</code></pre>
