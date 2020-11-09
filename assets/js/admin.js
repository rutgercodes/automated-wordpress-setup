console.log("Settings init");

(function($) { // Make $ available for jQuery

	$installBtn = $("#install");
	$installBtn.prop('disabled', false);
	
	$installBtn.click(async function(e) {
		e.preventDefault();

		try {

			$installBtn.prop('disabled', true);
			console.log("begin install");

			const themes = {
				main: $("#theme").val(),
				child: $("#child-theme").val()
			}
			const plugins = getTexareaAsArray("#plugins");
	
			await installProcess(themes, plugins, "results");
			return $installBtn.prop('disabled', false)

		} catch(e) {
			console.log("error", e)
		}

	});

	function getTexareaAsArray( selector ) {

		var lines = $(selector).val().split(/\n/);
		var text = [];
		for (var i=0; i < lines.length; i++) {
			// only push this line if it contains a non whitespace character.
			if (/\S/.test(lines[i])) {
				text.push($.trim(lines[i]));
			}
		}

		return text

	}

	async function installProcess( themes, plugins, logId = false ) {

		console.log("Starting process...")

		try {

			for (var i = 0, len = plugins.length; i < len; i++) {
	
				logMessage(logId, `Installing ${plugins[i]}`);
	
				const installResult = await installPlugin(plugins[i], logId);
				console.log( "results", installResult );
				
				logMessage(logId, installResult.message )
	
			}

			for (const [type, theme] of Object.entries(themes)) {

				if( !theme ) return

				logMessage(logId, `Installing ${theme}`);

				const activate = themes.child ? type == 'child' : type == 'main'
				const installResult = await installTheme(theme, activate, logId);
				
				logMessage(logId, installResult.message )

			}

			console.log("End process")

		} catch(e) {
			console.log("error", e)
		}
		
	}

	async function installPlugin( plugin ) {

		try {

			var data = { 'action': plugin_prefix + 'install_plugin', plugin: plugin }
	
			result = await $.post(ajaxurl, data);
			return JSON.parse( result );

		} catch(e) {
			console.log("error", e)
		}

	}

	async function installTheme( theme, activate ) {

		try {

			var data = { 'action': plugin_prefix + 'install_theme', theme: theme, activate: activate }
	
			result = await $.post(ajaxurl, data);
			console.log( result );
			return JSON.parse( result );

		} catch(e) {
			console.log("error", e)
		}

	}

	function logMessage( id, message, error = false ) {
		
		const classes = "message " + ( error ? " error" : "" )
		$('#'+id).append( '<span class="' + classes + '">' + message + '</span>' );

	}

	const delay = time => new Promise(res=>setTimeout(res,time));

	
})( jQuery );