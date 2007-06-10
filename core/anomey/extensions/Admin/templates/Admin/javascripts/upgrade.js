window.addEvent('load', function() { 
	var url = '../update';
	
	new Ajax(url, {
		method: 'get',
		onComplete: function(available) {
			if(available == 'true') {
				var message = new Element('li', {'class': 'info'}).setHTML('A new update for anomey is available.' + 
				' Download it from <a href="http://anomey.ch/download">anomey.ch</a>.');
				$('messages').adopt(message);
			}
		}
	}).request();
});
