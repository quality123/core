<?php
/**
 * @author Roeland Jago Douma <rullzer@owncloud.com>
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
namespace Test\Share20;

use OC\Share20\Manager;
use OC\Share20\Exception;


use OCP\IUser;
use OCP\IUserManager;
use OCP\IGroupManager;
use OCP\ILogger;
use OCP\IAppConfig;
use OCP\Files\Folder;
use OCP\Share20\IShareProvider;

class ManagerTest extends \Test\TestCase {

	/** @var Manager */
	protected $manager;

	/** @var IUser */
	protected $user;

	/** @var IUserManager */
	protected $userManager;

	/** @var IGroupManager */
	protected $groupManager;

	/** @var ILogger */
	protected $logger;

	/** @var IAppConfig */
	protected $appConfig;

	/** @var Folder */
	protected $userFolder;

	/** @var IShareProvider */
	protected $defaultProvider;

	public function setUp() {
		
		$this->user = $this->getMock('\OCP\IUser');
		$this->userManager = $this->getMock('\OCP\IUserManager');
		$this->groupManager = $this->getMock('\OCP\IGroupManager');
		$this->logger = $this->getMock('\OCP\ILogger');
		$this->appConfig = $this->getMock('\OCP\IAppConfig');
		$this->userFolder = $this->getMock('\OCP\Files\Folder');
		$this->defaultProvider = $this->getMock('\OC\Share20\IShareProvider');

		$this->manager = new Manager(
			$this->user,
			$this->userManager,
			$this->groupManager,
			$this->logger,
			$this->appConfig,
			$this->userFolder,
			$this->defaultProvider
		);
	}

	/**
	 * @expectedException OC\Share20\Exception\ShareNotFound
	 */
	public function testDeleteNoShareId() {
		$share = $this->getMock('\OC\Share20\IShare');

		$share
			->expects($this->once())
			->method('getId')
			->with()
			->willReturn(null);

		$this->manager->deleteShare($share);
	}

	public function dataTestDelete() {
		$user = $this->getMock('\OCP\IUser');
		$user->method('getUID')->willReturn('sharedWithUser');

		$group = $this->getMock('\OCP\IGroup');
		$group->method('getGID')->willReturn('sharedWithGroup');
	
		return [
			[\OCP\Share::SHARE_TYPE_USER, $user, 'sharedWithUser'],
			[\OCP\Share::SHARE_TYPE_GROUP, $group, 'sharedWithGroup'],
			[\OCP\Share::SHARE_TYPE_LINK, '', ''],
			[\OCP\Share::SHARE_TYPE_REMOTE, 'foo@bar.com', 'foo@bar.com'],
		];
	}

	/**
	 * @dataProvider dataTestDelete
	 */
	public function testDeleteUser($shareType, $sharedWith, $sharedWith_string) {
		$manager = $this->getMockBuilder('\OC\Share20\Manager')
			->setConstructorArgs([
				$this->user,
				$this->userManager,
				$this->groupManager,
				$this->logger,
				$this->appConfig,
				$this->userFolder,
				$this->defaultProvider
			])
			->setMethods(['getShareById', 'deleteChildren'])
			->getMock();

		$sharedBy = $this->getMock('\OCP\IUser');
		$sharedBy->method('getUID')->willReturn('sharedBy');

		$path = $this->getMock('\OCP\Files\File');
		$path->method('getId')->willReturn(1);

		$share = $this->getMock('\OC\Share20\IShare');
		$share->method('getId')->willReturn(42);
		$share->method('getShareType')->willReturn($shareType);
		$share->method('getSharedWith')->willReturn($sharedWith);
		$share->method('getSharedBy')->willReturn($sharedBy);
		$share->method('getPath')->willReturn($path);
		$share->method('getTarget')->willReturn('myTarget');

		$manager->expects($this->once())->method('getShareById')->with(42)->willReturn($share);
		$manager->expects($this->once())->method('deleteChildren')->with($share);

		$this->defaultProvider
			->expects($this->once())
			->method('delete')
			->with($share);

		$hookListner = $this->getMockBuilder('Dummy')->setMethods(['listen'])->getMock();
		\OCP\Util::connectHook('OCP\Share', 'pre_unshare', $hookListner, 'listen');
		\OCP\Util::connectHook('OCP\Share', 'post_unshare', $hookListner, 'listen');

		$hookListnerExpects = [
			'id' => 42,
			'itemType' => 'file',
			'itemSource' => 1,
			'shareType' => $shareType,
			'shareWith' => $sharedWith_string,
			'itemparent' => null,
			'uidOwner' => 'sharedBy',
			'fileSource' => 1,
			'fileTarget' => 'myTarget',
		];

		$hookListner
			->expects($this->exactly(2))
			->method('listen')
			->with($hookListnerExpects);

		$manager->deleteShare($share);
	}

	public function testDeleteChildren() {
		$manager = $this->getMockBuilder('\OC\Share20\Manager')
			->setConstructorArgs([
				$this->user,
				$this->userManager,
				$this->groupManager,
				$this->logger,
				$this->appConfig,
				$this->userFolder,
				$this->defaultProvider
			])
			->setMethods(['deleteShare'])
			->getMock();

		$share = $this->getMock('\OC\Share20\IShare');

		$child1 = $this->getMock('\OC\Share20\IShare');
		$child2 = $this->getMock('\OC\Share20\IShare');
		$child3 = $this->getMock('\OC\Share20\IShare');

		$shares = [
			$child1,
			$child2,
			$child3,
		];

		$this->defaultProvider
			->expects($this->once())
			->method('getChildren')
			->with($share)
			->willReturn($shares);

		$manager
			->expects($this->exactly(3))
			->method('deleteShare')
			->withConsecutive($child1, $child2, $child3);

		$this->invokePrivate($manager, 'deleteChildren', [$share]);
	}

	/**
	 * @expectedException OC\Share20\Exception\ShareNotFound
	 */
	public function testGetShareByIdNotFoundInBackend() {
		$this->defaultProvider
			->expects($this->once())
			->method('getShareById')
			->with(42)
			->will($this->throwException(new \OC\Share20\Exception\ShareNotFound()));

		$this->manager->getShareById(42);
	}

	/**
	 * @expectedException OC\Share20\Exception\ShareNotFound
	 */
	public function testGetShareByIdNotAuthorized() {
		$otherUser1 = $this->getMock('\OCP\IUser');
		$otherUser2 = $this->getMock('\OCP\IUser');
		$otherUser3 = $this->getMock('\OCP\IUser');

		$share = $this->getMock('\OC\Share20\IShare');
		$share
			->expects($this->once())
			->method('getSharedWith')
			->with()
			->willReturn($otherUser1);
		$share
			->expects($this->once())
			->method('getSharedBy')
			->with()
			->willReturn($otherUser2);
		$share
			->expects($this->once())
			->method('getShareOwner')
			->with()
			->willReturn($otherUser3);

		$this->defaultProvider
			->expects($this->once())
			->method('getShareById')
			->with(42)
			->willReturn($share);

		$this->manager->getShareById(42);
	}

	public function dataGetShareById() {
		return [
			['getSharedWith'],
			['getSharedBy'],
			['getShareOwner'],
		];
	}

	/**
	 * @dataProvider dataGetShareById
	 */
	public function testGetShareById($currentUserIs) {
		$otherUser1 = $this->getMock('\OCP\IUser');
		$otherUser2 = $this->getMock('\OCP\IUser');
		$otherUser3 = $this->getMock('\OCP\IUser');

		$share = $this->getMock('\OC\Share20\IShare');
		$share
			->method('getSharedWith')
			->with()
			->willReturn($currentUserIs === 'getSharedWith' ? $this->user : $otherUser1);
		$share
			->method('getSharedBy')
			->with()
			->willReturn($currentUserIs === 'getSharedBy' ? $this->user : $otherUser2);
		$share
			->method('getShareOwner')
			->with()
			->willReturn($currentUserIs === 'getShareOwner' ? $this->user : $otherUser3);

		$this->defaultProvider
			->expects($this->once())
			->method('getShareById')
			->with(42)
			->willReturn($share);

		$this->assertEquals($share, $this->manager->getShareById(42));
	}
}
