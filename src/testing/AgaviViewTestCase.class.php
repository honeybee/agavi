<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * AgaviViewTestCase is the base class for all view testcases and provides
 * the necessary assertions
 * 
 * 
 * @package    agavi
 * @subpackage testing
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
abstract class AgaviViewTestCase extends AgaviFragmentTestCase
{
	/**
	 * @var        string the (short) name of the view
	 */
	protected $viewName;
	
	/**
	 * @var        mixed the result of the view execution
	 */
	protected $viewResult;
	
	/**
	 *  creates the view instance for this testcase
	 * 
	 * @return     AgaviView
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function createViewInstance()
	{
		$this->getContext()->getController()->initializeModule($this->moduleName);
		$viewName = $this->normalizeViewName($this->viewName);
		$viewInstance = $this->getContext()->getController()->createViewInstance($this->moduleName, $viewName);
		$viewInstance->initialize($this->container);
		return $viewInstance;
	}
	
	/**
	 *  runs the view instance for this testcase
	 * 
	 * @param      string the name of the output type to run the view for
	 *                    null for the default output type
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function runView($otName = null)
	{
		$this->container->setOutputType($this->getContext()->getController()->getOutputType($otName));
		$this->container->setViewInstance($this->createViewInstance());
		$executionFilter = $this->createExecutionFilter();
		$this->viewResult = $executionFilter->executeView($this->container);
	}
	
	/**
	 * assert that the view handles the given output type
	 * 
	 * @param      string  the output type name
	 * @param      boolean true if the generic 'execute' method should be accepted as handled
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function assertHandlesOutputType($method, $acceptGeneric = false, $message = '')
	{
		$viewInstance = $this->createViewInstance();
		$constraint = new AgaviConstraintViewHandlesOutputType($viewInstance, $acceptGeneric);
		
		self::assertThat($method, $constraint, $message);
	}
	
	/**
	 * assert that the view does not handle the given output type
	 * 
	 * @param      string  the output type name
	 * @param      boolean true if the generic 'execute' method should be accepted as handled
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function assertNotHandlesOutputType($method, $acceptGeneric = false, $message = '')
	{
		$viewInstance = $this->createViewInstance();
		$constraint = self::logicalNot(new AgaviConstraintViewHandlesOutputType($viewInstance, $acceptGeneric));
		
		self::assertThat($method, $constraint, $message);
	}
	
	protected function assertResponseHasRedirect($message = 'Failed asserting that the view redirects')
	{
		$response = $this->container->getResponse();
		try {
			$this->assertTrue($response->hasRedirect(), $message);
		} catch (AgaviException $e) {
			$this->fail($message);
		}
	}
	
	protected function assertResponseHasNoRedirect($message = 'Failed asserting that the view does not redirect')
	{
		$response = $this->container->getResponse();
		try {
			$this->assertFalse($response->hasRedirect(), $message);
		} catch (AgaviException $e) {
			$this->fail($message);
		}
	}
	
	protected function assertResponseRedirectsTo($expected, $message = 'Failed asserting that the view redirects to the given target.')
	{
		$response = $this->container->getResponse();
		try {
			$this->assertEquals($expected, $response->getRedirect(), $message);
		} catch (AgaviException $e) {
			$this->fail($message);
		}
	}
	
	protected function assertResponseHasContentType($expected, $message = 'Failed asserting that the response content type is %1$s.')
	{
		$response = $this->container->getResponse();
		
		if(!($response instanceof AgaviWebResponse)) {
			$this->fail(sprintf($message . ' (response is not an AgaviWebResponse)', $expected));
		}
		$this->assertEquals($expected, $response->getContentType(), sprintf($message, $expected));
	}
	
	protected function assertResponseHasHeader($expected, $expectedValue = null, $message = 'Failed asserting that the response has a header named <%1$s> with the value <%2$s>')
	{
		$response = $this->container->getResponse();
		
		if(!($response instanceof AgaviWebResponse)) {
			$this->fail(sprintf($message . ' (response is not an AgaviWebResponse)', $expected));
		}
		$this->assertEquals($expected, $response->getHeader($expected), sprintf($message, $expected, $expectedValue));
	}
	
	protected function assertResponseHasHTTPStatus($expected, $message = 'Failed asserting that the respons status is %1$s.')
	{
		$response = $this->container->getResponse();
		
		if(!($response instanceof AgaviWebResponse)) {
			$this->fail(sprintf($message . ' (response is not an AgaviWebResponse)', $expected));
		}
		$this->assertEquals($expected, $response->getHttpStatusCode(), sprintf($message, $expected));
	}
	
	protected function assertResponseHasContent($expected, $message = 'Failed asserting that the response has content <%1$s>.')
	{
		$response = $this->container->getResponse();
		$this->assertEquals($expected, $response->getContent(), sprintf($message, $expected));
	}
	
	protected function assertViewResultEquals($expected, $message = 'Failed asserting the expected view result.')
	{
		$this->assertEquals($expected, $this->viewResult, sprintf($message, $expected));
	}
	
	protected function assertForwards($expectedModule, $expectedAction, $message = '')
	{
		if (!($this->viewResult instanceof AgaviExecutionContainer))
		{
			$this->fail('Failed asserting that the view result is a forward.');
		}
	}
	
	protected function assertHasLayer($expectedLayer, $message = '')
	{
		$viewInstance = $this->container->getViewInstance();
		$layer = $viewInstance->getLayer($expectedLayer);
		
		if (null == $layer)
		{
			$this->fail('Failed asserting that the view contains the layer.');
		}
	}
	
}

?>