<?php
class MyTestConfigHandler extends AgaviConfigHandler
{
	public function execute($config, $context = null)
	{
		return '';
	}
}

class AgaviConfigHandlerTest extends AgaviUnitTestCase
{
	protected $ch = null;
	public function setUp(): void
	{
		$this->ch = new MyTestConfigHandler();
		$this->ch->initialize('MyValidationFile.mvf');
	}

	protected function tearDown(): void
	{
		$this->ch = null;
	}

	public function testGetValidationFile()
	{
		$this->assertSame('MyValidationFile.mvf', $this->ch->getValidationFile());
	}

}
