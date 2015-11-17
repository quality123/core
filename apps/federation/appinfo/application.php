<?php
/**
 * @author Björn Schießle <schiessle@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Federation\AppInfo;

use OCA\Federation\API\OCSAuthAPI;
use OCA\Federation\Controller\AuthController;
use OCA\Federation\Controller\SettingsController;
use OCA\Federation\DbHandler;
use OCA\Federation\Middleware\AddServerMiddleware;
use OCA\Federation\TrustedServers;
use OCP\API;
use OCP\App;
use OCP\AppFramework\IAppContainer;

class Application extends \OCP\AppFramework\App {

	/**
	 * @param array $urlParams
	 */
	public function __construct($urlParams = array()) {
		parent::__construct('federation', $urlParams);
		$this->registerService();
		$this->registerMiddleware();
	}

	/**
	 * register setting scripts
	 */
	public function registerSettings() {
		App::registerAdmin('federation', 'settings/settings-admin');
	}

	private function registerService() {
		$container = $this->getContainer();

		$container->registerService('addServerMiddleware', function(IAppContainer $c) {
			return new AddServerMiddleware(
				$c->getAppName(),
				\OC::$server->getL10N($c->getAppName()),
				\OC::$server->getLogger()
			);
		});

		$container->registerService('DbHandler', function(IAppContainer $c) {
			return new DbHandler(
				\OC::$server->getDatabaseConnection(),
				\OC::$server->getL10N($c->getAppName())
			);
		});

		$container->registerService('TrustedServers', function(IAppContainer $c) {
			return new TrustedServers(
				$c->query('DbHandler'),
				\OC::$server->getHTTPClientService(),
				\OC::$server->getLogger(),
				\OC::$server->getJobList(),
				\OC::$server->getSecureRandom()
			);
		});

		$container->registerService('SettingsController', function (IAppContainer $c) {
			$server = $c->getServer();
			return new SettingsController(
				$c->getAppName(),
				$server->getRequest(),
				$server->getL10N($c->getAppName()),
				$c->query('TrustedServers')
			);
		});


		$container->registerService('AuthController', function (IAppContainer $c) {
			$server = $c->getServer();
			return new AuthController(
				$c->getAppName(),
				$server->getRequest(),
				$server->getSecureRandom(),
				$server->getJobList(),
				$c->query('TrustedServers'),
				$c->query('DbHandler')
			);
		});
	}

	private function registerMiddleware() {
		$container = $this->getContainer();
		$container->registerMiddleware('addServerMiddleware');
	}

	/**
	 * register OCS API Calls
	 */
	public function registerOCSApi() {

		$container = $this->getContainer();
		$server = $container->getServer();

		$auth = new OCSAuthAPI(
			$server->getRequest(),
			$server->getSecureRandom(),
			$server->getJobList(),
			$container->query('TrustedServers'),
			$container->query('DbHandler')

		);

		API::register('get',
			'/apps/federation/api/v1/shared-secret',
			array($auth, 'getSharedSecret'),
			'federation',
			API::GUEST_AUTH
		);

		API::register('post',
			'/apps/federation/api/v1/request-shared-secret',
			array($auth, 'requestSharedSecret'),
			'federation',
			API::GUEST_AUTH
		);

	}

}
