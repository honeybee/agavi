<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
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
 * AgaviPhpUnitTestCase is the base class for all Agavi Testcases.
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
abstract class AgaviPhpUnitTestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * @var        string  the name of the environment to bootstrap in isolated tests.
	 */
	protected $isolationEnvironment;
	
	/**
	 * @var        string  the name of the default context to use in isolated tests.
	 */
	protected $isolationDefaultContext;
	
	/**
	 * @var         bool if the cache in the isolated process should be cleared
	 */
	protected $clearIsolationCache = false;
	
	/**
	 * set the environment to bootstrap in isolated tests
	 * 
	 * @param        string the name of the environment
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.0
	 */
	public function setIsolationEnvironment($environmentName)
	{
		$this->isolationEnvironment = $environmentName;
	}
	
	/**
	 * set the default context to use in isolated tests
	 * 
	 * @param        string the name of the context
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.0
	 */
	public function setIsolationDefaultContext($contextName)
	{
		$this->isolationDefaultContext = $contextName;
	}
	
	/**
	 * set whether the cache should be cleared for the isolated subprocess
	 * 
	 * @param        bool true if the cache should be cleared
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.0
	 */
	public function setClearCache($flag)
	{
		$this->clearIsolationCache = (bool)$flag;
	}
	
	/**
	 * Performs custom preparations on the process isolation template.
	 *
	 * @param        PHPUnit_Util_Template $template
	 * @since        1.0.0
	*/
	protected function prepareTemplate(PHPUnit_Util_Template $template)
	{
		parent::prepareTemplate($template);
		
		$vars = array(
			'agavi_environment' => '',
			'agavi_default_context' => '',
			'agavi_clear_cache' => 'false', // literal strings required for proper template rendering
		);
		
		if(!empty($this->isolationEnvironment)) {
			$vars['agavi_environment'] = $this->isolationEnvironment;
		}
		
		if(!empty($this->isolationDefaultContext)) {
			$vars['agavi_default_context'] = $this->isolationDefaultContext;
		}
		
		if($this->clearIsolationCache) {
			$vars['agavi_clear_cache'] = 'true'; // literal strings required for proper template rendering
		}
		
		$template->setVar($vars);
		
		$templateFile = AgaviConfig::get('core.agavi_dir') . DIRECTORY_SEPARATOR . 'testing' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'TestCaseMethod.tpl';
		$template->setFile($templateFile);
	}
	
}