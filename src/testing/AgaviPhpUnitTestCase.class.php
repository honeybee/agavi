<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Blacklist;
use PHPUnit\Util\GlobalState;
use SebastianBergmann\Template\Template;

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
abstract class AgaviPhpUnitTestCase extends TestCase
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
	 * @var         string store the dataName since we can't access it from PHPUnit\Framework\TestCase.
	 */
	protected $myDataName;

	private static $annotationCache = array();

	/**
	 * Constructs a test case with the given name.
	 *
	 * @param        string
	 * @param        array
	 * @param        string
	 *
	 * @since        1.1.0
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->myDataName = $dataName;
	}


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
	 * get the environment to bootstrap in isolated tests
	 *
	 * @return       string the name of the isolation environment
	 *
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.2
	 */
	public function getIsolationEnvironment()
	{
		$environmentName = null;

		$annotations = $this->getAnnotations();

		if(!empty($annotations['method']['agaviIsolationEnvironment'])) {
			$environmentName = $annotations['method']['agaviIsolationEnvironment'][0];
		} elseif(!empty($annotations['class']['agaviIsolationEnvironment'])) {
			$environmentName = $annotations['class']['agaviIsolationEnvironment'][0];
		} elseif(!empty($this->isolationEnvironment)) {
			$environmentName = $this->isolationEnvironment;
		}

		return $environmentName;
	}


	/**
	 * set the default context to use in isolated tests
	 *
	 * @param        string the name of the context
	 *
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.2
	 */
	public function setIsolationDefaultContext($contextName)
	{
		$this->isolationDefaultContext = $contextName;
	}


	/**
	 * get the default context to use in isolated tests
	 *
	 * @return       string the default context to use in isolated tests
	 *
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.2
	 */
	public function getIsolationDefaultContext()
	{
		$ctxName = null;

		$annotations = $this->getAnnotations();

		if(!empty($annotations['method']['agaviIsolationDefaultContext'])) {
			$ctxName = $annotations['method']['agaviIsolationDefaultContext'][0];
		} elseif(!empty($annotations['class']['agaviIsolationDefaultContext'])) {
			$ctxName = $annotations['class']['agaviIsolationDefaultContext'][0];
		} elseif(!empty($this->isolationDefaultContext)) {
			$ctxName = $this->isolationDefaultContext;
		}

		return $ctxName;
	}


	/**
	 * set whether the cache should be cleared for the isolated subprocess
	 *
	 * @param        bool true if the cache should be cleared
	 *
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.2
	 */
	public function setClearCache($flag)
	{
		$this->clearIsolationCache = (bool)$flag;
	}


	/**
	 * check whether to clear the cache in isolated tests
	 *
	 * @return       bool true if the cache is cleared in isolated tests
	 *
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.2
	 */
	public function getClearCache()
	{
		$flag = null;

		$annotations = $this->getAnnotations();

		if(!empty($annotations['method']['agaviClearIsolationCache'])) {
			$flag = true;
		} elseif(!empty($annotations['class']['agaviClearIsolationCache'])) {
			$flag = true;
		} else {
			$flag = $this->clearIsolationCache;
		}

		return $flag;
	}

	/**
	 * Retrieve the classes and defining files the given class depends on (including the given class)
	 *
	 * @param        ReflectionClass The class to get the dependend classes for.
	 * @param        callable A callback function which takes a file name as argument
	 *                        and returns whether the file is blacklisted.
	 *
	 * @return       string[] An array containing class names as keys and path to the
	 *                        file's defining class as value.
	 *
	 * @author       Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since        1.1.0
	 */
	private function getClassDependendFiles(ReflectionClass $reflectionClass, $isBlacklisted) {
		$requires = array();

		while($reflectionClass) {
			$file = $reflectionClass->getFileName();
			// we don't care for duplicates since we're using require_once anyways
			if(!$isBlacklisted($file) && is_file($file)) {
				$requires[$reflectionClass->getName()] = $file;
			}
			foreach($reflectionClass->getInterfaces() as $interface) {
				$file = $interface->getFileName();
				$requires = array_merge($requires, $this->getClassDependendFiles($interface, $isBlacklisted));
			}
			if(is_callable(array($reflectionClass, 'getTraits'))) {
				// FIXME: remove check after bumping php requirement to 5.4
				foreach($reflectionClass->getTraits() as $trait) {
					$file = $trait->getFileName();
					$requires = array_merge($requires, $this->getClassDependendFiles($trait, $isBlacklisted));
				}
			}
			$reflectionClass = $reflectionClass->getParentClass();
		}
		return $requires;
	}

	/**
	 * Get the dependend classes of this test.
	 *
	 * @return       string[] An array containing class names as keys and path to the
	 *                        file's defining class as value.
	 *
	 * @author       Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since        1.1.0
	 */
	private function getDependendClasses() {
		// We need to collect the dependend classes in case there is a test which
		// has set @agaviBootstrap to off. That results in the Agavi autoloader not
		// being started and if the test class depends on any files from Agavi (like
		// AgaviPhpUnitTestCase) it would not be loaded when the test is instantiated

		$classesInTest = array();
		$reflectionClass = new ReflectionClass(get_class($this));
		$testFile = $reflectionClass->getFileName();

		$getDeclaredFuncs = array('get_declared_classes', 'get_declared_interfaces');
		if(version_compare(PHP_VERSION, '5.4', '>=')) {
			$getDeclaredFuncs[] = 'get_declared_traits';
		}
		foreach($getDeclaredFuncs as $getDeclaredFunc) {
			foreach($getDeclaredFunc() as $name) {
				$reflectionClass = new ReflectionClass($name);
				if($testFile === $reflectionClass->getFileName()) {
					$classesInTest[] = $name;
				}
			}
		}

		// FIXME: added by phpunit 4.x
		if(class_exists(Blacklist::class)) {
			$blacklist = new Blacklist;
			$isBlacklisted = function($file) use ($testFile, $blacklist) {
				return $file === $testFile || $blacklist->isBlacklisted($file);
			};
		} elseif(is_callable(array(GlobalState::class, 'phpunitFiles'))) {
			$blacklist = GlobalState::phpunitFiles();
			$isBlacklisted = function($file) use ($testFile, $blacklist) {
				return $file === $testFile || isset($blacklist[$file]);
			};
		} else {
			$isBlacklisted = function($file) use ($testFile) {
				return $file === $testFile;
			};
		}

		$classesToFile = array('AgaviTesting' => realpath(__DIR__ . '/AgaviTesting.class.php'));
		foreach($classesInTest as $className) {
			$classesToFile = array_merge(
				$classesToFile,
				$this->getClassDependendFiles(new ReflectionClass($className), $isBlacklisted)
			);
		}

		return $classesToFile;
	}

	/**
	 * Performs custom preparations on the process isolation template.
	 *
	 * @param        Text_Template $template
	 *
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        1.0.2
	*/
	protected function prepareTemplate(Template $template)
	{
		// FIXME: workaround for php unit bug (https://github.com/sebastianbergmann/phpunit/pull/1338)
		$template->setVar(array(
			'dataName' => "'.(" . var_export($this->myDataName, true) . ").'"
		));

		// FIXME: if we have full composer autoloading we can remove this
		// we need to restore the included files even without global state, since otherwise
		// the agavi test class files would be missing.
		// We can't write include()s directly since Agavi possibly get's bootstrapped later
		// in the process (but before the test instance is created) and if we'd load any
		// files which are being loaded by the bootstrap process chaos would ensue since
		// the bootstrap process uses plain include()s without _once
		$fileAutoloader = sprintf('
			spl_autoload_register(function($name) {
				$classMap = %s;
				if(isset($classMap[$name])) {
					include($classMap[$name]);
				}
			});
		', var_export($this->getDependendClasses(), true));

		// these constants are either used by out bootstrap wrapper script
		// (AGAVI_TESTING_ORIGINAL_PHPUNIT_BOOTSTRAP) or can be used by the user's
		// bootstrap script (AGAVI_TESTING_IN_SEPERATE_PROCESS)
		$constants = sprintf('
			define("AGAVI_TESTING_IN_SEPERATE_PROCESS", true);
			define("AGAVI_TESTING_ORIGINAL_PHPUNIT_BOOTSTRAP", %s);
			',
			var_export(isset($GLOBALS["__PHPUNIT_BOOTSTRAP"]) ? $GLOBALS["__PHPUNIT_BOOTSTRAP"] : null, true)
		);


		$isolatedTestSettings = array(
			'environment' => $this->getIsolationEnvironment(),
			'defaultContext' => $this->getIsolationDefaultContext(),
			'clearCache' => $this->getClearCache(),
			'bootstrap' => $this->doBootstrap(),
		);
		$globals = sprintf('
			$GLOBALS["AGAVI_TESTING_CONFIG"] = %s;
			$GLOBALS["AGAVI_TESTING_ISOLATED_TEST_SETTINGS"] = %s;
			$GLOBALS["__PHPUNIT_BOOTSTRAP"] = %s;
			',
			var_export(AgaviConfig::toArray(), true),
			var_export($isolatedTestSettings, true),
			var_export(__DIR__ . '/scripts/IsolatedBootstrap.php', true)
		);

		if(!$this->preserveGlobalState) {
			$template->setVar(array(
				'included_files' => $fileAutoloader,
				'constants' => $constants,
				'globals' => $globals,
			));
		} else {
			// HACK: oh great, text/template doesn't expose the already set variables, but we need to modify
			// them instead of overwriting them. So let's use the reflection to the rescue here.
			$reflected = new ReflectionObject($template);
			$property = $reflected->getProperty('values');
			$property->setAccessible(true);
			$oldVars = $property->getValue($template);
			$template->setVar(array(
				'included_files' => $fileAutoloader,
				'constants' => $oldVars['constants'] . PHP_EOL . $constants,
				'globals' => $oldVars['globals'] . PHP_EOL . $globals,
			));

		}
	}

	/**
	 * Whether or not an agavi bootstrap should be done in isolation.
	 *
	 * @return       boolean true if agavi should be bootstrapped
	 *
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 *
	 * @since        1.0.2
	 */
	protected function doBootstrap()
	{
		$flag = true;

		$annotations = $this->getAnnotations();
		if(!empty($annotations['method']['agaviBootstrap'])) {
			$flag = AgaviToolkit::literalize($annotations['method']['agaviBootstrap'][0]);
		} elseif(!empty($annotations['class']['agaviBootstrap'])) {
			$flag = AgaviToolkit::literalize($annotations['class']['agaviBootstrap'][0]);
		}
		return $flag;
	}

	/**
	 * Returns the annotations for this test.
	 *
	 * @return array
	 */
	public function getAnnotations()
	{
		return self::parseTestMethodAnnotations(
			get_class($this),
			$this->getName()
		);
	}


	/**
	 * @param  string $className
	 * @param  string $methodName
	 * @return array
	 * @throws ReflectionException
	 * @since  Method available since Release 3.4.0
	 */
	public static function parseTestMethodAnnotations($className, $methodName = '')
	{
		if (!isset(self::$annotationCache[$className])) {
			$class = new ReflectionClass($className);
			self::$annotationCache[$className] = self::parseAnnotations($class->getDocComment());
		}

		if (!empty($methodName) && !isset(self::$annotationCache[$className . '::' . $methodName])) {
			$method = new ReflectionMethod($className, $methodName);
			self::$annotationCache[$className . '::' . $methodName] = self::parseAnnotations($method->getDocComment());
		}

		return array(
		  'class'  => self::$annotationCache[$className],
		  'method' => !empty($methodName) ? self::$annotationCache[$className . '::' . $methodName] : array()
		);
	}


	/**
	 * @param  string $docblock
	 * @return array
	 * @since  Method available since Release 3.4.0
	 */
	private static function parseAnnotations($docblock)
	{
		$annotations = array();

		if (preg_match_all('/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?$/m', $docblock, $matches)) {
			$numMatches = count($matches[0]);

			for ($i = 0; $i < $numMatches; ++$i) {
				$annotations[$matches['name'][$i]][] = $matches['value'][$i];
			}
		}

		return $annotations;
	}

	/**
	 * Evaluate an HTML or XML string and assert its structure and/or contents.
	 *
	 * The first argument ($matcher) is an associative array that specifies the
	 * match criteria for the assertion:
	 *
	 *  - `id`           : the node with the given id attribute must match the
	 *                     corresponding value.
	 *  - `tag`          : the node type must match the corresponding value.
	 *  - `attributes`   : a hash. The node's attributes must match the
	 *                     corresponding values in the hash.
	 *  - `content`      : The text content must match the given value.
	 *  - `parent`       : a hash. The node's parent must match the
	 *                     corresponding hash.
	 *  - `child`        : a hash. At least one of the node's immediate children
	 *                     must meet the criteria described by the hash.
	 *  - `ancestor`     : a hash. At least one of the node's ancestors must
	 *                     meet the criteria described by the hash.
	 *  - `descendant`   : a hash. At least one of the node's descendants must
	 *                     meet the criteria described by the hash.
	 *  - `children`     : a hash, for counting children of a node.
	 *                     Accepts the keys:
	 *    - `count`        : a number which must equal the number of children
	 *                       that match
	 *    - `less_than`    : the number of matching children must be greater
	 *                       than this number
	 *    - `greater_than` : the number of matching children must be less than
	 *                       this number
	 *    - `only`         : another hash consisting of the keys to use to match
	 *                       on the children, and only matching children will be
	 *                       counted
	 *
	 * <code>
	 * // Matcher that asserts that there is an element with an id="my_id".
	 * $matcher = array('id' => 'my_id');
	 *
	 * // Matcher that asserts that there is a "span" tag.
	 * $matcher = array('tag' => 'span');
	 *
	 * // Matcher that asserts that there is a "span" tag with the content
	 * // "Hello World".
	 * $matcher = array('tag' => 'span', 'content' => 'Hello World');
	 *
	 * // Matcher that asserts that there is a "span" tag with content matching
	 * // the regular expression pattern.
	 * $matcher = array('tag' => 'span', 'content' => 'regexp:/Try P(HP|ython)/');
	 *
	 * // Matcher that asserts that there is a "span" with an "list" class
	 * // attribute.
	 * $matcher = array(
	 *   'tag'        => 'span',
	 *   'attributes' => array('class' => 'list')
	 * );
	 *
	 * // Matcher that asserts that there is a "span" inside of a "div".
	 * $matcher = array(
	 *   'tag'    => 'span',
	 *   'parent' => array('tag' => 'div')
	 * );
	 *
	 * // Matcher that asserts that there is a "span" somewhere inside a
	 * // "table".
	 * $matcher = array(
	 *   'tag'      => 'span',
	 *   'ancestor' => array('tag' => 'table')
	 * );
	 *
	 * // Matcher that asserts that there is a "span" with at least one "em"
	 * // child.
	 * $matcher = array(
	 *   'tag'   => 'span',
	 *   'child' => array('tag' => 'em')
	 * );
	 *
	 * // Matcher that asserts that there is a "span" containing a (possibly
	 * // nested) "strong" tag.
	 * $matcher = array(
	 *   'tag'        => 'span',
	 *   'descendant' => array('tag' => 'strong')
	 * );
	 *
	 * // Matcher that asserts that there is a "span" containing 5-10 "em" tags
	 * // as immediate children.
	 * $matcher = array(
	 *   'tag'      => 'span',
	 *   'children' => array(
	 *     'less_than'    => 11,
	 *     'greater_than' => 4,
	 *     'only'         => array('tag' => 'em')
	 *   )
	 * );
	 *
	 * // Matcher that asserts that there is a "div", with an "ul" ancestor and
	 * // a "li" parent (with class="enum"), and containing a "span" descendant
	 * // that contains an element with id="my_test" and the text "Hello World".
	 * $matcher = array(
	 *   'tag'        => 'div',
	 *   'ancestor'   => array('tag' => 'ul'),
	 *   'parent'     => array(
	 *     'tag'        => 'li',
	 *     'attributes' => array('class' => 'enum')
	 *   ),
	 *   'descendant' => array(
	 *     'tag'   => 'span',
	 *     'child' => array(
	 *       'id'      => 'my_test',
	 *       'content' => 'Hello World'
	 *     )
	 *   )
	 * );
	 *
	 * // Use assertTag() to apply a $matcher to a piece of $html.
	 * $this->assertTag($matcher, $html);
	 *
	 * // Use assertTag() to apply a $matcher to a piece of $xml.
	 * $this->assertTag($matcher, $xml, '', false);
	 * </code>
	 *
	 * The second argument ($actual) is a string containing either HTML or
	 * XML text to be tested.
	 *
	 * The third argument ($message) is an optional message that will be
	 * used if the assertion fails.
	 *
	 * The fourth argument ($html) is an optional flag specifying whether
	 * to load the $actual string into a DOMDocument using the HTML or
	 * XML load strategy.  It is true by default, which assumes the HTML
	 * load strategy.  In many cases, this will be acceptable for XML as well.
	 *
	 * @param array  $matcher
	 * @param string $actual
	 * @param string $message
	 * @param bool   $isHtml
	 * @since  Method available since Release 3.3.0
	 * @deprecated
	 * @codeCoverageIgnore
	 */
	public static function assertTag($matcher, $actual, $message = '', $isHtml = true)
	{
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);

		$dom     = self::load($actual, $isHtml);
		$tags    = self::findNodes($dom, $matcher, $isHtml);
		$matched = count($tags) > 0 && $tags[0] instanceof DOMNode;

		self::assertTrue($matched, $message);
	}

	/**
	 * This assertion is the exact opposite of assertTag().
	 *
	 * Rather than asserting that $matcher results in a match, it asserts that
	 * $matcher does not match.
	 *
	 * @param array  $matcher
	 * @param string $actual
	 * @param string $message
	 * @param bool   $isHtml
	 * @since  Method available since Release 3.3.0
	 * @deprecated
	 * @codeCoverageIgnore
	 */
	public static function assertNotTag($matcher, $actual, $message = '', $isHtml = true)
	{
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);

		$dom     = self::load($actual, $isHtml);
		$tags    = self::findNodes($dom, $matcher, $isHtml);
		$matched = count($tags) > 0 && $tags[0] instanceof DOMNode;

		self::assertFalse($matched, $message);
	}


	/**
	 * Gets elements by case insensitive tagname.
	 *
	 * @param  DOMDocument $dom
	 * @param  string      $tag
	 * @return DOMNodeList
	 * @since  Method available since Release 3.4.0
	 */
	protected static function getElementsByCaseInsensitiveTagName(DOMDocument $dom, $tag)
	{
		$elements = $dom->getElementsByTagName(strtolower($tag));

		if ($elements->length == 0) {
			$elements = $dom->getElementsByTagName(strtoupper($tag));
		}

		return $elements;
	}

	/**
	 * Get the text value of this node's child text node.
	 *
	 * @param  DOMNode $node
	 * @return string
	 * @since  Method available since Release 3.3.0
	 * @author Mike Naberezny <mike@maintainable.com>
	 * @author Derek DeVries <derek@maintainable.com>
	 */
	protected static function getNodeText(DOMNode $node)
	{
		if (!$node->childNodes instanceof DOMNodeList) {
			return '';
		}

		$result = '';

		foreach ($node->childNodes as $childNode) {
			if ($childNode->nodeType === XML_TEXT_NODE) {
				$result .= trim($childNode->data) . ' ';
			} else {
				$result .= self::getNodeText($childNode);
			}
		}

		return str_replace('  ', ' ', $result);
	}


	/**
	 * Recursively get flat array of all descendants of this node.
	 *
	 * @param  DOMNode $node
	 * @return array
	 * @since  Method available since Release 3.3.0
	 * @author Mike Naberezny <mike@maintainable.com>
	 * @author Derek DeVries <derek@maintainable.com>
	 */
	protected static function getDescendants(DOMNode $node)
	{
		$allChildren = array();
		$childNodes  = $node->childNodes ? $node->childNodes : array();

		foreach ($childNodes as $child) {
			if ($child->nodeType === XML_CDATA_SECTION_NODE ||
				$child->nodeType === XML_TEXT_NODE) {
				continue;
			}

			$children    = self::getDescendants($child);
			$allChildren = array_merge($allChildren, $children, array($child));
		}

		return isset($allChildren) ? $allChildren : array();
	}


	/**
	 * Parse out the options from the tag using DOM object tree.
	 *
	 * @param  DOMDocument $dom
	 * @param  array       $options
	 * @param  boolean     $isHtml
	 * @return array
	 * @since  Method available since Release 3.3.0
	 * @author Mike Naberezny <mike@maintainable.com>
	 * @author Derek DeVries <derek@maintainable.com>
	 */
	public static function findNodes(DOMDocument $dom, array $options, $isHtml = TRUE)
	{
		$valid = array(
		  'id', 'class', 'tag', 'content', 'attributes', 'parent',
		  'child', 'ancestor', 'descendant', 'children'
		);

		$filtered = array();
		$options  = self::assertValidKeys($options, $valid);

		// find the element by id
		if ($options['id']) {
			$options['attributes']['id'] = $options['id'];
		}

		if ($options['class']) {
			$options['attributes']['class'] = $options['class'];
		}

		// find the element by a tag type
		if ($options['tag']) {
			if ($isHtml) {
				$elements = self::getElementsByCaseInsensitiveTagName(
				  $dom, $options['tag']
				);
			} else {
				$elements = $dom->getElementsByTagName($options['tag']);
			}

			foreach ($elements as $element) {
				$nodes[] = $element;
			}

			if (empty($nodes)) {
				return FALSE;
			}
		}

		// no tag selected, get them all
		else {
			$tags = array(
			  'a', 'abbr', 'acronym', 'address', 'area', 'b', 'base', 'bdo',
			  'big', 'blockquote', 'body', 'br', 'button', 'caption', 'cite',
			  'code', 'col', 'colgroup', 'dd', 'del', 'div', 'dfn', 'dl',
			  'dt', 'em', 'fieldset', 'form', 'frame', 'frameset', 'h1', 'h2',
			  'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'i', 'iframe',
			  'img', 'input', 'ins', 'kbd', 'label', 'legend', 'li', 'link',
			  'map', 'meta', 'noframes', 'noscript', 'object', 'ol', 'optgroup',
			  'option', 'p', 'param', 'pre', 'q', 'samp', 'script', 'select',
			  'small', 'span', 'strong', 'style', 'sub', 'sup', 'table',
			  'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title',
			  'tr', 'tt', 'ul', 'var'
			);

			foreach ($tags as $tag) {
				if ($isHtml) {
					$elements = self::getElementsByCaseInsensitiveTagName(
					  $dom, $tag
					);
				} else {
					$elements = $dom->getElementsByTagName($tag);
				}

				foreach ($elements as $element) {
					$nodes[] = $element;
				}
			}

			if (empty($nodes)) {
				return FALSE;
			}
		}

		// filter by attributes
		if ($options['attributes']) {
			foreach ($nodes as $node) {
				$invalid = FALSE;

				foreach ($options['attributes'] as $name => $value) {
					// match by regexp if like "regexp:/foo/i"
					if (preg_match('/^regexp\s*:\s*(.*)/i', $value, $matches)) {
						if (!preg_match($matches[1], $node->getAttribute($name))) {
							$invalid = TRUE;
						}
					}

					// class can match only a part
					else if ($name == 'class') {
						// split to individual classes
						$findClasses = explode(
						  ' ', preg_replace("/\s+/", " ", $value)
						);

						$allClasses = explode(
						  ' ',
						  preg_replace("/\s+/", " ", $node->getAttribute($name))
						);

						// make sure each class given is in the actual node
						foreach ($findClasses as $findClass) {
							if (!in_array($findClass, $allClasses)) {
								$invalid = TRUE;
							}
						}
					}

					// match by exact string
					else {
						if ($node->getAttribute($name) != $value) {
							$invalid = TRUE;
						}
					}
				}

				// if every attribute given matched
				if (!$invalid) {
					$filtered[] = $node;
				}
			}

			$nodes    = $filtered;
			$filtered = array();

			if (empty($nodes)) {
				return FALSE;
			}
		}

		// filter by content
		if ($options['content'] !== NULL) {
			foreach ($nodes as $node) {
				$invalid = FALSE;

				// match by regexp if like "regexp:/foo/i"
				if (preg_match('/^regexp\s*:\s*(.*)/i', $options['content'], $matches)) {
					if (!preg_match($matches[1], self::getNodeText($node))) {
						$invalid = TRUE;
					}
				}

				// match by exact string
				else if (strstr(self::getNodeText($node), $options['content']) === FALSE) {
					$invalid = TRUE;
				}

				if (!$invalid) {
					$filtered[] = $node;
				}
			}

			$nodes    = $filtered;
			$filtered = array();

			if (empty($nodes)) {
				return FALSE;
			}
		}

		// filter by parent node
		if ($options['parent']) {
			$parentNodes = self::findNodes($dom, $options['parent']);
			$parentNode  = isset($parentNodes[0]) ? $parentNodes[0] : NULL;

			foreach ($nodes as $node) {
				if ($parentNode !== $node->parentNode) {
					break;
				}

				$filtered[] = $node;
			}

			$nodes    = $filtered;
			$filtered = array();

			if (empty($nodes)) {
				return FALSE;
			}
		}

		// filter by child node
		if ($options['child']) {
			$childNodes = self::findNodes($dom, $options['child']);
			$childNodes = !empty($childNodes) ? $childNodes : array();

			foreach ($nodes as $node) {
				foreach ($node->childNodes as $child) {
					foreach ($childNodes as $childNode) {
						if ($childNode === $child) {
							$filtered[] = $node;
						}
					}
				}
			}

			$nodes    = $filtered;
			$filtered = array();

			if (empty($nodes)) {
				return FALSE;
			}
		}

		// filter by ancestor
		if ($options['ancestor']) {
			$ancestorNodes = self::findNodes($dom, $options['ancestor']);
			$ancestorNode  = isset($ancestorNodes[0]) ? $ancestorNodes[0] : NULL;

			foreach ($nodes as $node) {
				$parent = $node->parentNode;

				while ($parent->nodeType != XML_HTML_DOCUMENT_NODE) {
					if ($parent === $ancestorNode) {
						$filtered[] = $node;
					}

					$parent = $parent->parentNode;
				}
			}

			$nodes    = $filtered;
			$filtered = array();

			if (empty($nodes)) {
				return FALSE;
			}
		}

		// filter by descendant
		if ($options['descendant']) {
			$descendantNodes = self::findNodes($dom, $options['descendant']);
			$descendantNodes = !empty($descendantNodes) ? $descendantNodes : array();

			foreach ($nodes as $node) {
				foreach (self::getDescendants($node) as $descendant) {
					foreach ($descendantNodes as $descendantNode) {
						if ($descendantNode === $descendant) {
							$filtered[] = $node;
						}
					}
				}
			}

			$nodes    = $filtered;
			$filtered = array();

			if (empty($nodes)) {
				return FALSE;
			}
		}

		// filter by children
		if ($options['children']) {
			$validChild   = array('count', 'greater_than', 'less_than', 'only');
			$childOptions = self::assertValidKeys(
							  $options['children'], $validChild
							);

			foreach ($nodes as $node) {
				$childNodes = $node->childNodes;

				foreach ($childNodes as $childNode) {
					if ($childNode->nodeType !== XML_CDATA_SECTION_NODE &&
						$childNode->nodeType !== XML_TEXT_NODE) {
						$children[] = $childNode;
					}
				}

				// we must have children to pass this filter
				if (!empty($children)) {
					// exact count of children
					if ($childOptions['count'] !== NULL) {
						if (count($children) !== $childOptions['count']) {
							break;
						}
					}

					// range count of children
					else if ($childOptions['less_than']    !== NULL &&
							$childOptions['greater_than'] !== NULL) {
						if (count($children) >= $childOptions['less_than'] ||
							count($children) <= $childOptions['greater_than']) {
							break;
						}
					}

					// less than a given count
					else if ($childOptions['less_than'] !== NULL) {
						if (count($children) >= $childOptions['less_than']) {
							break;
						}
					}

					// more than a given count
					else if ($childOptions['greater_than'] !== NULL) {
						if (count($children) <= $childOptions['greater_than']) {
							break;
						}
					}

					// match each child against a specific tag
					if ($childOptions['only']) {
						$onlyNodes = self::findNodes(
						  $dom, $childOptions['only']
						);

						// try to match each child to one of the 'only' nodes
						foreach ($children as $child) {
							$matched = FALSE;

							foreach ($onlyNodes as $onlyNode) {
								if ($onlyNode === $child) {
									$matched = TRUE;
								}
							}

							if (!$matched) {
								break(2);
							}
						}
					}

					$filtered[] = $node;
				}
			}

			$nodes    = $filtered;
			$filtered = array();

			if (empty($nodes)) {
				return;
			}
		}

		// return the first node that matches all criteria
		return !empty($nodes) ? $nodes : array();
	}


	/**
	 * Load an $actual document into a DOMDocument.  This is called
	 * from the selector assertions.
	 *
	 * If $actual is already a DOMDocument, it is returned with
	 * no changes.  Otherwise, $actual is loaded into a new DOMDocument
	 * as either HTML or XML, depending on the value of $isHtml.
	 *
	 * Note: prior to PHPUnit 3.3.0, this method loaded a file and
	 * not a string as it currently does.  To load a file into a
	 * DOMDocument, use loadFile() instead.
	 *
	 * @param  string|DOMDocument  $actual
	 * @param  boolean             $isHtml
	 * @param  string              $filename
	 * @return DOMDocument
	 * @since  Method available since Release 3.3.0
	 * @author Mike Naberezny <mike@maintainable.com>
	 * @author Derek DeVries <derek@maintainable.com>
	 */
	public static function load($actual, $isHtml = FALSE, $filename = '')
	{
		if ($actual instanceof DOMDocument) {
			return $actual;
		}

		$internal  = libxml_use_internal_errors(TRUE);
		$reporting = error_reporting(0);
		$dom       = new DOMDocument;

		if ($isHtml) {
			$loaded = $dom->loadHTML($actual);
		} else {
			$loaded = $dom->loadXML($actual);
		}

		libxml_use_internal_errors($internal);
		error_reporting($reporting);

		if ($loaded === FALSE) {
			$message = '';

			foreach (libxml_get_errors() as $error) {
				$message .= $error->message;
			}

			if ($filename != '') {
				throw new Exception(
				  sprintf(
					'Could not load "%s".%s',

					$filename,
					$message != '' ? "\n" . $message : ''
				  )
				);
			} else {
				throw new Exception($message);
			}
		}

		return $dom;
	}


	/**
	 * Validate list of keys in the associative array.
	 *
	 * @param  array $hash
	 * @param  array $validKeys
	 * @return array
	 * @throws InvalidArgumentException
	 * @since  Method available since Release 3.3.0
	 * @author Mike Naberezny <mike@maintainable.com>
	 * @author Derek DeVries <derek@maintainable.com>
	 */
	public static function assertValidKeys(array $hash, array $validKeys)
	{
		$valids = array();

		// Normalize validation keys so that we can use both indexed and
		// associative arrays.
		foreach ($validKeys as $key => $val) {
			is_int($key) ? $valids[$val] = NULL : $valids[$key] = $val;
		}

		$validKeys = array_keys($valids);

		// Check for invalid keys.
		foreach ($hash as $key => $value) {
			if (!in_array($key, $validKeys)) {
				$unknown[] = $key;
			}
		}

		if (!empty($unknown)) {
			throw new InvalidArgumentException(
			  'Unknown key(s): ' . implode(', ', $unknown)
			);
		}

		// Add default values for any valid keys that are empty.
		foreach ($valids as $key => $value) {
			if (!isset($hash[$key])) {
				$hash[$key] = $value;
			}
		}

		return $hash;
	}

}
