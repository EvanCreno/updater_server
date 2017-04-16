<?php
/**
 * @license MIT <http://opensource.org/licenses/MIT>
 */

namespace Tests;

use UpdateServer\Config;
use UpdateServer\Request;
use UpdateServer\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase {
	/** @var Request */
	private $request;
	/** @var Config */
	private $config;
	/** @var Response */
	private $response;

	public function setUp() {
		date_default_timezone_set('Europe/Berlin');

		$this->request = $this->getMockBuilder('\UpdateServer\Request')
			->disableOriginalConstructor()->getMock();
		$this->config = $this->getMockBuilder('\UpdateServer\Config')
			->disableOriginalConstructor()->getMock();
		$this->response = new Response($this->request, $this->config);
	}

	public function dailyVersionProvider() {
		return [
			[
				'5',
				'',
			],
			[
				'6',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>100.0.0.0</version>
 <versionstring>Nextcloud daily</versionstring>
 <url>https://download.owncloud.org/community/owncloud-7.0.13.zip</url>
 <web>https://doc.owncloud.org/server/7.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'7',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>100.0.0.0</version>
 <versionstring>Nextcloud daily</versionstring>
 <url>https://download.owncloud.org/community/owncloud-8.0.11.zip</url>
 <web>https://doc.owncloud.org/server/7.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'8',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>100.0.0.0</version>
 <versionstring>Nextcloud daily</versionstring>
 <url>https://download.owncloud.org/community/owncloud-8.1.6.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'8.0.5',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>100.0.0.0</version>
 <versionstring>Nextcloud daily</versionstring>
 <url>https://download.owncloud.org/community/owncloud-8.1.6.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'9',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>100.0.0.0</version>
 <versionstring>Nextcloud daily</versionstring>
 <url>https://download.owncloud.org/community/owncloud-daily-master.zip</url>
 <web>https://doc.owncloud.org/server/9.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'9.0.3',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>100.0.0.0</version>
 <versionstring>Nextcloud daily</versionstring>
 <url>https://download.owncloud.org/community/owncloud-daily-master.zip</url>
 <web>https://doc.owncloud.org/server/9.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			]
		];
	}

	/**
	 * @dataProvider dailyVersionProvider
	 */
	public function testBuildResponseForOutdatedDaily($version, $expected) {
		$this->request
			->expects($this->once())
			->method('getChannel')
			->willReturn('daily');
		$this->request
			->expects($this->any())
			->method('getBuild')
			->willReturn('2015-10-19T18:44:30+00:00');
		$this->config
			->expects($this->once())
			->method('get')
			->with('daily')
			->willReturn(
				[
					'9.1' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-daily-master.zip',
						'web' => 'https://doc.owncloud.org/server/9.1/admin_manual/maintenance/upgrade.html',
					],
					'9.0' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-daily-master.zip',
						'web' => 'https://doc.owncloud.org/server/9.0/admin_manual/maintenance/upgrade.html',
					],
					'8.2' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-daily-stable9.zip',
						'web' => 'https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html',
					],
					'8.1' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-8.2.3.zip',
						'web' => 'https://doc.owncloud.org/server/8.1/admin_manual/maintenance/upgrade.html',
					],
					'8.0' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-8.1.6.zip',
						'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
					],
					'7' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-8.0.11.zip',
						'web' => 'https://doc.owncloud.org/server/7.0/admin_manual/maintenance/upgrade.html',
					],
					'6' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-7.0.13.zip',
						'web' => 'https://doc.owncloud.org/server/7.0/admin_manual/maintenance/upgrade.html',
					],
				]
			);
		$this->request
			->expects($this->any())
			->method('getMajorVersion')
			->willReturn($version[0]);
		if(isset($version[4])) {
			$this->request
				->expects($this->any())
				->method('getMinorVersion')
				->willReturn($version[4]);
		}

		$this->assertSame($expected, $this->response->buildResponse());
	}

	/**
	 * @dataProvider dailyVersionProvider
	 */
	public function testBuildResponseForCurrentDaily($version) {
		$this->request
			->expects($this->once())
			->method('getChannel')
			->willReturn('daily');
		$this->request
			->expects($this->any())
			->method('getBuild')
			->willReturn('2025-10-19T18:44:30+00:00');
		$this->request
			->expects($this->any())
			->method('getMajorVersion')
			->willReturn($version[0]);
		if(isset($version[4])) {
			$this->request
				->expects($this->any())
				->method('getMinorVersion')
				->willReturn($version[4]);
		}
		$this->config
			->expects($this->once())
			->method('get')
			->with('daily')
			->willReturn(
				[
					'9.1' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-daily-master.zip',
						'web' => 'https://doc.owncloud.org/server/9.1/admin_manual/maintenance/upgrade.html',
					],
					'9.0' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-daily-master.zip',
						'web' => 'https://doc.owncloud.org/server/9.0/admin_manual/maintenance/upgrade.html',
					],
					'8.2' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-daily-stable9.zip',
						'web' => 'https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html',
					],
					'8.1' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-8.2.3.zip',
						'web' => 'https://doc.owncloud.org/server/8.1/admin_manual/maintenance/upgrade.html',
					],
					'8.0' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-8.1.6.zip',
						'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
					],
					'7' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-8.0.11.zip',
						'web' => 'https://doc.owncloud.org/server/7.0/admin_manual/maintenance/upgrade.html',
					],
					'6' => [
						'downloadUrl' => 'https://download.owncloud.org/community/owncloud-7.0.13.zip',
						'web' => 'https://doc.owncloud.org/server/7.0/admin_manual/maintenance/upgrade.html',
					],
				]
			);

		$expected = '';

		$this->assertSame($expected, $this->response->buildResponse());
	}

	/**
	 * @return array
	 */
	public function responseProvider() {
		return [
			[
				'production',
				'11',
				'0',
				'0',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>11.0.1</version>
 <versionstring>Nextcloud 11.0.1</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-11.0.1.zip</url>
 <web>https://docs.nextcloud.com/server/11/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
 <signature>MySignature</signature>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'0',
				'8',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.0.9</version>
 <versionstring>Nextcloud 8.0.9</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.0.9.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'0',
				'7',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.0.7.1</version>
 <versionstring>Nextcloud 8.0.7.1</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.0.7.1.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'0',
				'7',
				'1',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.0.8</version>
 <versionstring>Nextcloud 8.0.8</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.0.8.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'0',
				'9',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.0.10</version>
 <versionstring>Nextcloud 8.0.10</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.0.10.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'0',
				'10',
				'',
				'',
			],
			[
				'production',
				'8',
				'0',
				'11',
				'',
				'',
			],
			[
				'production',
				'7',
				'0',
				'11',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>7.0.12</version>
 <versionstring>Nextcloud 7.0.12</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-7.0.12.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'1',
				'4',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.1.5</version>
 <versionstring>Nextcloud 8.1.5</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.1.5.zip</url>
 <web>https://doc.owncloud.org/server/8.1/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'1',
				'5',
				'',
				'',
			],
			[
				'production',
				'8',
				'2',
				'1',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.2.2</version>
 <versionstring>Nextcloud 8.2.2</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.2.2.zip</url>
 <web>https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'2',
				'3',
				'',
				'',
			],
			[
				'production',
				'8',
				'3',
				'3',
				'',
				'',
			],
			[
				'production',
				'',
				'',
				'',
				'',
				'',
			],
			[
				'stable',
				'8',
				'0',
				'9',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.0.10</version>
 <versionstring>Nextcloud 8.0.10</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.0.10.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'stable',
				'8',
				'0',
				'10',
				'',
				'',
			],
			[
				'stable',
				'8',
				'0',
				'11',
				'',
				'',
			],
			[
				'stable',
				'6',
				'0',
				'5',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>7.0.12</version>
 <versionstring>Nextcloud 7.0.12</versionstring>
 <url>https://downloads.owncloud.com/foo.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'stable',
				'7',
				'0',
				'11',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>7.0.12</version>
 <versionstring>Nextcloud 7.0.12</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-7.0.12.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'stable',
				'8',
				'1',
				'4',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.1.5</version>
 <versionstring>Nextcloud 8.1.5</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.1.5.zip</url>
 <web>https://doc.owncloud.org/server/8.1/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'stable',
				'8',
				'1',
				'5',
				'',
				'',
			],
			[
				'stable',
				'8',
				'2',
				'1',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.2.2</version>
 <versionstring>Nextcloud 8.2.2</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.2.2.zip</url>
 <web>https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'stable',
				'8',
				'2',
				'3',
				'',
				'',
			],
			[
				'stable',
				'8',
				'3',
				'3',
				'',
				'',
			],
			[
				'stable',
				'',
				'',
				'',
				'',
				'',
			],
			[
				'beta',
				'8',
				'0',
				'9',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.0.10</version>
 <versionstring>Nextcloud 8.0.10</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.0.10.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'beta',
				'8',
				'0',
				'10',
				'',
				'',
			],
			[
				'beta',
				'8',
				'0',
				'11',
				'',
				'',
			],
			[
				'beta',
				'7',
				'0',
				'11',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>7.0.12</version>
 <versionstring>Nextcloud 7.0.12</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-7.0.12.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'beta',
				'7',
				'0',
				'13',
				'',
				'',
			],
			[
				'beta',
				'8',
				'1',
				'4',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.1.5</version>
 <versionstring>Nextcloud 8.1.5</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.1.5.zip</url>
 <web>https://doc.owncloud.org/server/8.1/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'beta',
				'8',
				'1',
				'5',
				'',
				'',
			],
			[
				'beta',
				'8',
				'2',
				'1',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.2.2</version>
 <versionstring>Nextcloud 8.2.2</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.2.2.zip</url>
 <web>https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>1</autoupdater>
</nextcloud>
',
			],
			[
				'beta',
				'8',
				'2',
				'3',
				'',
				'',
			],
			[
				'beta',
				'8',
				'3',
				'3',
				'',
				'',
			],
			[
				'beta',
				'',
				'',
				'',
				'',
				'',
			],
			[
				'',
				'8',
				'2',
				'1',
				'',
				'',
			],
			[
				'',
				'',
				'',
				'',
				'',
				'',
			],
		];
	}

	/**
	 * @return array
	 */
	public function responseProviderWithDisabledUpdates() {
		return [
			[
				'production',
				'8',
				'0',
				'8',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.0.9</version>
 <versionstring>Nextcloud 8.0.9</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.0.9.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'0',
				'7',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.0.7.1</version>
 <versionstring>Nextcloud 8.0.7.1</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.0.7.1.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'0',
				'7',
				'1',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.0.8</version>
 <versionstring>Nextcloud 8.0.8</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.0.8.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'0',
				'9',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.0.10</version>
 <versionstring>Nextcloud 8.0.10</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.0.10.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'0',
				'10',
				'',
				'',
			],
			[
				'production',
				'8',
				'0',
				'11',
				'',
				'',
			],
			[
				'production',
				'7',
				'0',
				'11',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>7.0.12</version>
 <versionstring>Nextcloud 7.0.12</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-7.0.12.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'1',
				'4',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.1.5</version>
 <versionstring>Nextcloud 8.1.5</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.1.5.zip</url>
 <web>https://doc.owncloud.org/server/8.1/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'1',
				'5',
				'',
				'',
			],
			[
				'production',
				'8',
				'2',
				'1',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.2.2</version>
 <versionstring>Nextcloud 8.2.2</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.2.2.zip</url>
 <web>https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'production',
				'8',
				'2',
				'3',
				'',
				'',
			],
			[
				'production',
				'8',
				'3',
				'3',
				'',
				'',
			],
			[
				'production',
				'',
				'',
				'',
				'',
				'',
			],
			[
				'stable',
				'8',
				'0',
				'9',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.0.10</version>
 <versionstring>Nextcloud 8.0.10</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.0.10.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'stable',
				'8',
				'0',
				'10',
				'',
				'',
			],
			[
				'stable',
				'8',
				'0',
				'11',
				'',
				'',
			],
			[
				'stable',
				'6',
				'0',
				'5',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>7.0.12</version>
 <versionstring>Nextcloud 7.0.12</versionstring>
 <url>https://downloads.owncloud.com/foo.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'stable',
				'7',
				'0',
				'11',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>7.0.12</version>
 <versionstring>Nextcloud 7.0.12</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-7.0.12.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'stable',
				'8',
				'1',
				'4',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.1.5</version>
 <versionstring>Nextcloud 8.1.5</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.1.5.zip</url>
 <web>https://doc.owncloud.org/server/8.1/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'stable',
				'8',
				'1',
				'5',
				'',
				'',
			],
			[
				'stable',
				'8',
				'2',
				'1',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.2.2</version>
 <versionstring>Nextcloud 8.2.2</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.2.2.zip</url>
 <web>https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'stable',
				'8',
				'2',
				'3',
				'',
				'',
			],
			[
				'stable',
				'8',
				'3',
				'3',
				'',
				'',
			],
			[
				'stable',
				'',
				'',
				'',
				'',
				'',
			],
			[
				'beta',
				'8',
				'0',
				'9',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.0.10</version>
 <versionstring>Nextcloud 8.0.10</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.0.10.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'beta',
				'8',
				'0',
				'10',
				'',
				'',
			],
			[
				'beta',
				'8',
				'0',
				'11',
				'',
				'',
			],
			[
				'beta',
				'7',
				'0',
				'11',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>7.0.12</version>
 <versionstring>Nextcloud 7.0.12</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-7.0.12.zip</url>
 <web>https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'beta',
				'7',
				'0',
				'13',
				'',
				'',
			],
			[
				'beta',
				'8',
				'1',
				'4',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.1.5</version>
 <versionstring>Nextcloud 8.1.5</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.1.5.zip</url>
 <web>https://doc.owncloud.org/server/8.1/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'beta',
				'8',
				'1',
				'5',
				'',
				'',
			],
			[
				'beta',
				'8',
				'2',
				'1',
				'',
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.2.2</version>
 <versionstring>Nextcloud 8.2.2</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.2.2.zip</url>
 <web>https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'beta',
				'8',
				'2',
				'3',
				'',
				'',
			],
			[
				'beta',
				'8',
				'3',
				'3',
				'',
				'',
			],
			[
				'beta',
				'',
				'',
				'',
				'',
				'',
			],
			[
				'',
				'8',
				'2',
				'1',
				'',
				'',
			],
			[
				'',
				'',
				'',
				'',
				'',
				'',
			],
		];
	}

	/**
	 * @param string $channel
	 * @param string $majorVersion
	 * @param string $minorVersion
	 * @param string $revisionVersion
	 * @param string $maintenanceVersion
	 * @param string $expected
	 *
	 * @dataProvider responseProvider
	 */
	public function testBuildResponseForChannel($channel,
												$majorVersion,
												$minorVersion,
												$maintenanceVersion,
												$revisionVersion,
												$expected) {
		$config = [
			'11.0' => [
				'100' => [
					'latest' => '11.0.1',
					'web' => 'https://docs.nextcloud.com/server/11/admin_manual/maintenance/upgrade.html',
					'signature' => 'MySignature',
				],
			],
			'8.2' => [
				'100' => [
					'latest' => '8.2.2',
					'web' => 'https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html',
				],
			],
			'8.1' => [
				'100' => [
					'latest' => '8.1.5',
					'web' => 'https://doc.owncloud.org/server/8.1/admin_manual/maintenance/upgrade.html',
				],
			],
			'8.0' => [
				'100' => [
					'latest' => '8.0.10',
					'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
				],
			],
			'8.0.7' => [
				'100' => [
					'latest' => '8.0.7.1',
					'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
				],
			],
			'8.0.7.1' => [
				'100' => [
					'latest' => '8.0.8',
					'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
				],
			],
			'8.0.8' => [
				'100' => [
					'latest' => '8.0.9',
					'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
				],
			],
			'7' => [
				'100' => [
					'latest' => '7.0.12',
					'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
				],
			],
			'6' => [
				'100' => [
					'latest' => '7.0.12',
					'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
					'downloadUrl' => 'https://downloads.owncloud.com/foo.zip',
				]
			],
		];
		$this->request
			->expects($this->any())
			->method('getChannel')
			->willReturn($channel);
		$this->config
			->expects($this->any())
			->method('get')
			->with($channel)
			->willReturn($config);
		$this->request
			->expects($this->any())
			->method('getMajorVersion')
			->willReturn($majorVersion);
		$this->request
			->expects($this->any())
			->method('getMinorVersion')
			->willReturn($minorVersion);
		$this->request
			->expects($this->any())
			->method('getMaintenanceVersion')
			->willReturn($maintenanceVersion);
		$this->request
			->expects($this->any())
			->method('getRevisionVersion')
			->willReturn($revisionVersion);

		$this->assertSame($expected, $this->response->buildResponse());
	}

	/**
	 * @param string $channel
	 * @param string $majorVersion
	 * @param string $minorVersion
	 * @param string $revisionVersion
	 * @param string $maintenanceVersion
	 * @param string $expected
	 *
	 * @dataProvider responseProviderWithDisabledUpdates
	 */
	public function testBuildResponseWithDisabledUpdaterChannel($channel,
												$majorVersion,
												$minorVersion,
												$maintenanceVersion,
												$revisionVersion,
												$expected) {
		$config = [
			'8.2' => [
				'100' => [
					'latest' => '8.2.2',
					'web' => 'https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html',
					'autoupdater' => false,
				],
			],
			'8.1' => [
				'100' => [
					'latest' => '8.1.5',
					'web' => 'https://doc.owncloud.org/server/8.1/admin_manual/maintenance/upgrade.html',
					'autoupdater' => false,
				],
			],
			'8.0' => [
				'100' => [
					'latest' => '8.0.10',
					'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
					'autoupdater' => false,
				],
			],
			'8.0.7' => [
				'100' => [
					'latest' => '8.0.7.1',
					'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
					'autoupdater' => false,
				],
			],
			'8.0.7.1' => [
				'100' => [
					'latest' => '8.0.8',
					'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
					'autoupdater' => false,
				],
			],
			'8.0.8' => [
				'100' => [
					'latest' => '8.0.9',
					'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
					'autoupdater' => false,
				],
			],
			'7' => [
				'100' => [
					'latest' => '7.0.12',
					'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
					'autoupdater' => false,
				],
			],
			'6' => [
				'100' => [
					'latest' => '7.0.12',
					'web' => 'https://doc.owncloud.org/server/8.0/admin_manual/maintenance/upgrade.html',
					'downloadUrl' => 'https://downloads.owncloud.com/foo.zip',
					'autoupdater' => false,
				],
			],
		];
		$this->request
			->expects($this->any())
			->method('getChannel')
			->willReturn($channel);
		$this->config
			->expects($this->any())
			->method('get')
			->with($channel)
			->willReturn($config);
		$this->request
			->expects($this->any())
			->method('getMajorVersion')
			->willReturn($majorVersion);
		$this->request
			->expects($this->any())
			->method('getMinorVersion')
			->willReturn($minorVersion);
		$this->request
			->expects($this->any())
			->method('getMaintenanceVersion')
			->willReturn($maintenanceVersion);
		$this->request
			->expects($this->any())
			->method('getRevisionVersion')
			->willReturn($revisionVersion);

		$this->assertSame($expected, $this->response->buildResponse());
	}

	public function updateChanceDataProvider() {
		return [
			[
				'9901',
				6,
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>9.0.0</version>
 <versionstring>Nextcloud 9.0.0</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-9.0.0.zip</url>
 <web>https://doc.owncloud.org/server/9.0/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'9994',
				6,
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.2.2</version>
 <versionstring>Nextcloud 8.2.2</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.2.2.zip</url>
 <web>https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
			[
				'9901',
				4,
				'',
			],
			[
				'',
				4,
				'<?xml version="1.0" encoding="UTF-8"?>
<nextcloud>
 <version>8.2.2</version>
 <versionstring>Nextcloud 8.2.2</versionstring>
 <url>https://download.nextcloud.com/server/releases/nextcloud-8.2.2.zip</url>
 <web>https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html</web>
 <autoupdater>0</autoupdater>
</nextcloud>
',
			],
		];
	}

	/**
	 * @dataProvider updateChanceDataProvider
	 * @param string $mtime
	 * @param string $phpMinorVersion
	 * @param string $expected
	 */
	public function testBuildResponseStableChannelWithUpdateChance($mtime, $phpMinorVersion, $expected) {
		$config = [
			'8.2' => [
				'95' => [
					'latest' => '8.2.2',
					'web' => 'https://doc.owncloud.org/server/8.2/admin_manual/maintenance/upgrade.html',
					'autoupdater' => false,
					'minPHPVersion' => '5.4',
				],
				'5' => [
					'latest' => '9.0.0',
					'web' => 'https://doc.owncloud.org/server/9.0/admin_manual/maintenance/upgrade.html',
					'autoupdater' => false,
					'minPHPVersion' => '5.6',
				]
			],
		];

		$this->request
			->expects($this->any())
			->method('getChannel')
			->willReturn('production');
		$this->config
			->expects($this->any())
			->method('get')
			->with('production')
			->willReturn($config);
		$this->request
			->expects($this->any())
			->method('getMajorVersion')
			->willReturn('8');
		$this->request
			->expects($this->any())
			->method('getMinorVersion')
			->willReturn('2');
		$this->request
			->expects($this->any())
			->method('getMaintenanceVersion')
			->willReturn('0');
		$this->request
			->expects($this->any())
			->method('getRevisionVersion')
			->willReturn('0');
		$this->request
			->expects($this->any())
			->method('getInstallationMtime')
			->willReturn($mtime);
		$this->request
			->expects($this->any())
			->method('getPHPMajorVersion')
			->willReturn('5');
		$this->request
			->expects($this->any())
			->method('getPHPMinorVersion')
			->willReturn($phpMinorVersion);
		$this->request
			->expects($this->any())
			->method('getPHPReleaseVersion')
			->willReturn('0');

		$this->assertSame($expected, $this->response->buildResponse());
	}
}
