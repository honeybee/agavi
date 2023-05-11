<?php

/**
 * @runTestsInSeparateProcesses
 * @agaviIsolationEnvironment testing-use_database_on
 */
class AgaviDatabaseManagerTest extends AgaviUnitTestCase
{
	private $_dbm = null;
	
	public function setUp(): void
	{
		$this->_dbm = $this->getContext()->getDatabaseManager();
	}

	protected function tearDown(): void
	{
		$this->_dbm = null;
	}

	public function testInitialization()
	{
		$this->assertInstanceOf('AgaviDatabaseManager', $this->_dbm);
	}

}
?>