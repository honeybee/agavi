<?php

class Ticket958Test extends AgaviUnitTestCase
{
	public function testTicket958()
	{
		$this->expectException(InvalidArgumentException::class);
		$tm = $this->getContext()->getTranslationManager();
		$tz = AgaviTimeZone::createCustomTimeZone($tm, '+01:00');
	}
}

?>