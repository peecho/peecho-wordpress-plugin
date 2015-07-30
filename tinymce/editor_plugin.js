// Docu : http://www.tinymce.com/wiki.php/API3:tinymce.api.3.x

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('peecho');
	
	tinymce.create('tinymce.plugins.peecho', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {

			// Register the command so that it can be invoked from the button
			ed.addCommand('mce_peecho', function() {
				peecho_canvas = ed;
				peecho_caller = 'visual';
				jQuery( "#peecho-dialog" ).dialog( "open" );
			});

			// Register example button
			ed.addButton('peecho', {
				title : 'Insert Peecho Print Button',
				cmd : 'mce_peecho',
				image : url + '/peecho.gif'
			});
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
					longname  : 'Peecho',
					author 	  : 'Peecho',
					authorurl : 'http://peecho.com/',
					infourl   : 'http://wpstorm.net/wordpress-plugins/',
					version   : '1.0'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('peecho', tinymce.plugins.peecho);
})();


