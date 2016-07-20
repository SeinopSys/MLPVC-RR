<div id="content">
<?  if (isset($browser['browser_name'])){ ?>
	<div class="browser-<?=CoreUtils::BrowserNameToClass($browser['browser_name'])?>"></div>
<?  } ?>
	<h1><?=rtrim(($browser['browser_name']??'Unknown browser').' '.($browser['browser_ver']??''))?></h1>
	<p><?=!empty($browser['platform'])?"on {$browser['platform']}":'Unknown platform'?></p>

    <?=!empty($Session)?CoreUtils::Notice('warn',"You're debugging session #{$Session['id']} (belongs to ".User::GetProfileLink(User::Get($Session['user'])).")"):''?>
	<?=CoreUtils::Notice('info','Browser recognition testing page',"The following page is used to make sure that the site's browser detection script works as it should. If you're seeing a browser and/or operating system that's different from what you're currently using, please <a class='send-feedback'>let us know.</a>")?>

	<section>
		<h2>Your User Agent string</h2>
		<p><code><?=CoreUtils::EscapeHTML(!empty($browser['user_agent'])?$browser['user_agent']:'<empty>')?></code></p>
	</section>
</div>
