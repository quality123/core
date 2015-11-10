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


namespace OCA\Federation\BackgroundJob;

use OC\BackgroundJob\QueuedJob;
use OCA\Federation\DbHandler;
use OCP\AppFramework\Http;
use OCP\BackgroundJob\IJobList;
use OCP\Http\Client\IClient;
use OCP\ILogger;
use OCP\IURLGenerator;

/**
 * Class GetSharedSecret
 *
 * request shared secret from remote ownCloud
 *
 * @package OCA\Federation\Backgroundjob
 */
class GetSharedSecret extends QueuedJob{

	/** @var IClient */
	private $httpClient;

	/** @var IJobList */
	private $jobList;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var DbHandler */
	private $dbHandler;

	private $endPoint = '/index.php/apps/federation/get-shared-secret';

	/**
	 * RequestSharedSecret constructor.
	 *
	 * @param IClient $httpClient
	 * @param IURLGenerator $urlGenerator
	 * @param IJobList $jobList
	 * @param DbHandler $dbHandler
	 */
	public function __construct(
		IClient $httpClient = null,
		IURLGenerator $urlGenerator = null,
		IJobList $jobList = null,
		DbHandler $dbHandler = null
	) {
		$this->httpClient = $httpClient ? $httpClient : \OC::$server->getHTTPClientService()->newClient();
		$this->jobList = $jobList ? $jobList : \OC::$server->getJobList();
		$this->urlGenerator = $urlGenerator ? $urlGenerator : \OC::$server->getURLGenerator();
		$this->dbHandler = $dbHandler ? $dbHandler : new DbHandler(\OC::$server->getDatabaseConnection(), \OC::$server->getL10N('federation'));
	}

	/**
	 * run the job, then remove it from the joblist
	 *
	 * @param JobList $jobList
	 * @param ILogger $logger
	 */
	public function execute($jobList, ILogger $logger = null) {
		$jobList->remove($this, $this->argument);
		$target = $this->argument['url'];
		// only execute if target is still in the list of trusted domains
		if ($this->trustedServers->isTrustedServer($target)) {
			parent::execute($jobList, $logger);
		}
	}

	protected function run($argument) {
		$target = $argument['url'];
		$source = $this->urlGenerator->getAbsoluteURL('/');
		$source = rtrim($source, '/');
		$token = $argument['token'];

		$result = $this->httpClient->get(
			$target . $this->endPoint,
			[
				'query' =>
					[
						'url' => $source,
						'token' => $token
					],
				'timeout' => 3,
				'connect_timeout' => 3,
			]
		);

		$status = $result->getStatusCode();

		// if we received a unexpected response we try again later
		if (
			$status !== Http::STATUS_OK
			&& $status !== Http::STATUS_FORBIDDEN
		) {
			$this->jobList->add(
				'OCA\Federation\Backgroundjob\RequestSharedSecret',
				$argument
			);
		} else {
			$body = $result->getBody();
			$result = json_decode($body, true);
			$this->dbHandler->addSharedSecret($target, $result['sharedSecret']);

		}
	}
}
