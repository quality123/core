<?php
/** @var array $_ */
/** @var OC_L10N $l */
script('federation', 'settings-admin');
style('federation', 'settings-admin')
?>
<div id="ocFederationSettings" class="section">
	<h2><?php p($l->t('Federation')); ?></h2>
	<em><?php p($l->t('ownCloud Federation allows you to connect with other trusted ownClouds to exchange the user directory. For example this will be used to auto-complete external users for federated sharing.')); ?></em>

	<p id="ocFederationShareUsers">
		<input type="checkbox" class="checkbox" id="shareUsers" />
		<label for="shareUsers">Share internal user list with other ownClouds</label>
	</p>

	<h3>Trusted ownCloud Servers</h3>
	<p id="ocFederationAddServer">
		<button id="ocFederationAddServerButton" class="">+ Add ownCloud server</button>
		<input id="serverUrl" class="hidden" type="text" value="" placeholder="ownCloud Server" name="server_url"/>
		<span class="msg"></span>
	</p>
	<ul id="listOfTrustedServers">
		<?php foreach($_['trustedServers'] as $trustedServer) { ?>
			<li id="<?php p($trustedServer['id']); ?>">
				<?php p($trustedServer['url']); ?>
			</li>
		<?php } ?>
	</ul>

</div>

