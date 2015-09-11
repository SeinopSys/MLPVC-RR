DocReady.push(function User(){
	var $signoutBtn = $('#signout'),
		$name = $content.children('h1'),
		name = $name.text().trim(),
		sameUser = name === $sidebar.children('.welcome').find('.un').text().trim();
	$('.session-list').find('button.remove').on('click',function(e){
		e.preventDefault();

		var title = 'Removing session',
			$btn = $(this),
			$li = $btn.closest('li'),
			$browser = $btn.parent(),
			browser = $browser.text().trim(),
			$platform = $browser.next().children('strong'),
			platform = $platform.length ? ' on <em>'+$platform.text().trim()+'</em>' : '';

		// First item is sometimes the current session, trigger logout button instead
		if ($li.index() === 0){
			var current = /current/i.test($browser.parent().children().last().text());
			if (current)
				return $signoutBtn.trigger('click');
		}

		var SessionID = parseInt($btn.attr('data-sid'));

		if (typeof SessionID === 'undefined' || isNaN(SessionID) || !isFinite(SessionID))
			return $.Dialog.fail(title,'Could not locate Session ID, please reload the page and try again.');

		$.Dialog.confirm(title,(sameUser?'You':name)+' will be logged out form <em>'+browser+'</em>'+platform+'.<br>Continue?',function(sure){
			if (!sure) return;

			$.Dialog.wait(title,'Signing out from '+browser);

			$.post('/user/sessiondel/'+SessionID, $.mkAjaxHandler(function(){
				if (!this.status) return $.Dialog.fail(title,this.message);

				if ($li.siblings().length !== 0){
					$li.remove();
					$.Dialog.close();
				}
				else {
					$.Dialog.success(title, 'Session removed successfully');
					HandleNav.reload(function(){
						$.Dialog.close();
					},2000);
				}
			}));
		});
	});
	$('#signout-everywhere').on('click',function(){
		var title = 'Sign out from ALL sessions';

		$.Dialog.confirm(title,"This will invalidate ALL sessions. Continue?",function(sure){
			if (!sure) return;

			$.Dialog.wait(title,'Signing out');

			$.post('/signout?everywhere',$.mkAjaxHandler(function(){
				if (this.status){
					$.Dialog.success(title,this.message);
					HandleNav.reload(function(){
						$.Dialog.close();
					},1000);
				}
				else $.Dialog.fail(title,this.message);
			}));
		});
	});
	$('#unlink').on('click',function(){
		var title = 'Unlink account & sign out';
		$.Dialog.confirm(title,'Are you sure you want to unlink your account?',function(sure){
			if (!sure) return;

			$.Dialog.wait(title,'Removing account link');

			$.post('/signout?unlink', $.mkAjaxHandler(function(){
				if (this.status){
					$.Dialog.success(title,this.message);
					HandleNav.reload(function(){
						$.Dialog.close();
					},1000);
				}
				else $.Dialog.fail(title,this.message);
			}));
		});
	});
});
