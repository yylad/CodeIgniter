<?php

class Loader_test extends CI_TestCase {
	
	private $ci_obj;
	
	public function set_up()
	{
		// Instantiate a new loader
		$this->load = new Mock_Core_Loader();
		
		// mock up a ci instance
		$this->ci_obj = new StdClass;
		
		// Fix get_instance()
		$this->ci_instance($this->ci_obj);
	}

	// --------------------------------------------------------------------
	
	public function test_library()
	{
		$this->_setup_config_mock();

		// Test no lib given
		$this->assertEquals(NULL, $this->load->library());

		// Test loading multiple with an array.
		$this->assertNull($this->load->library(array('table')));

		// Does the table class exist now its been loaded
		$this->assertTrue(class_exists('CI_Table'), 'Table class exists');

		// Check the old (now deprecated) super-global assignment works
		$this->assertAttributeInstanceOf('CI_Table', 'table', $this->ci_obj);
		
		// Return the library instance
		$email = $this->load->library('email');

		// The factory created instance must be CI_Email
		$this->assertInstanceOf('CI_Email', $email);

		// Check the old deprecated approach works too
		$this->assertInstanceOf('CI_Email', $this->ci_obj->email);

		// Are those two objects entirely the same
		$this->assertSame($email, $this->ci_obj->email);

		$email2 = $this->load->library('email');

		// Are those two objects entirely the same
		$this->assertNotSame($email, $email2);
	}

	// --------------------------------------------------------------------

	public function test_load_library_in_application_dir()
	{
		$this->_setup_config_mock();
		
		$content = '<?php class Super_test_library {} ';
		
		vfsStream::newFile('Super_test_library.php')
			->withContent($content)
			->at($this->load->libs_dir);

		// Return the library instance
		$library = $this->load->library('super_test_library');

		$this->assertInstanceOf('Super_test_library', $library);
		
		// Was the model class instantiated.
		$this->assertTrue(class_exists('Super_test_library'));		
	}
	
	// --------------------------------------------------------------------
	
	private function _setup_config_mock()
	{
		// Mock up a config object until we
		// figure out how to test the library configs
		$config = $this->getMock('CI_Config', NULL, array(), '', FALSE);
		$config->expects($this->any())
			   ->method('load')
			   ->will($this->returnValue(TRUE));
		
		// Add the mock to our stdClass
		$this->ci_instance_var('config', $config);
	}

	// --------------------------------------------------------------------

	public function test_non_existent_model()
	{
		$this->setExpectedException(
			'RuntimeException',
			'CI Error: Unable to locate the model you have specified: ci_test_nonexistent_model.php'
		);
			
		$this->load->model('ci_test_nonexistent_model.php');
	}

	// --------------------------------------------------------------------
	
	/**
	 * @coverts CI_Loader::model
	 */
	public function test_models()
	{
		$this->ci_set_core_class('model', 'CI_Model');
		
		$content = '<?php class Unit_test_model extends CI_Model {} ';
		
		$model = vfsStream::newFile('unit_test_model.php')->withContent($content)
														->at($this->load->models_dir);
		
		$this->assertNull($this->load->model('unit_test_model'));
		
		// Was the model class instantiated.
		$this->assertTrue(class_exists('Unit_test_model'));
		
		// Test no model given
		$this->assertNull($this->load->model(''));	
	}

	// --------------------------------------------------------------------
	
	// public function testDatabase()
	// {
	// 	$this->assertEquals(NULL, $this->load->database());
	// 	$this->assertEquals(NULL, $this->load->dbutil());		
	// }

	// --------------------------------------------------------------------
	
	/**
	 * @coverts CI_Loader::view
	 */
	public function test_load_view()
	{
		$this->ci_set_core_class('output', 'CI_Output');
		
		$content = 'This is my test page.  <?php echo $hello; ?>';
		$view = vfsStream::newFile('unit_test_view.php')->withContent($content)
														->at($this->load->views_dir);
		
		// Use the optional return parameter in this test, so the view is not
		// run through the output class.
		$this->assertEquals('This is my test page.  World!',
		$this->load->view('unit_test_view', array('hello' => "World!"), TRUE));
		
	}

	// --------------------------------------------------------------------
	
	/**
	 * @coverts CI_Loader::view
	 */
	public function test_non_existent_view()
	{
		$this->setExpectedException(
			'RuntimeException',
			'CI Error: Unable to load the requested file: ci_test_nonexistent_view.php'
			);
			
		$this->load->view('ci_test_nonexistent_view', array('foo' => 'bar'));
	}

	// --------------------------------------------------------------------

	public function test_file()
	{
		$content = 'Here is a test file, which we will load now.';
		$file = vfsStream::newFile('ci_test_mock_file.php')->withContent($content)
														   ->at($this->load->views_dir);
		
		// Just like load->view(), take the output class out of the mix here.
		$load = $this->load->file(vfsStream::url('application').'/views/ci_test_mock_file.php', 
								TRUE);
		
		$this->assertEquals($content, $load);
		
		$this->setExpectedException(
			'RuntimeException',
			'CI Error: Unable to load the requested file: ci_test_file_not_exists'
			);
		
		$this->load->file('ci_test_file_not_exists', TRUE);
		
	}

	// --------------------------------------------------------------------
	
	public function test_vars()
	{
		$vars = array(
			'foo'	=> 'bar'
		);
		
		$this->assertNull($this->load->vars($vars));
		$this->assertNull($this->load->vars('foo', 'bar'));
	}

	// --------------------------------------------------------------------
	
	public function test_helper()
	{
		$this->assertEquals(NULL, $this->load->helper('array'));
		
		$this->setExpectedException(
			'RuntimeException',
			'CI Error: Unable to load the requested file: helpers/bad_helper.php'
			);
		
		$this->load->helper('bad');
	}
	
	// --------------------------------------------------------------------

	public function test_loading_multiple_helpers()
	{
		$this->assertEquals(NULL, $this->load->helpers(array('file', 'array', 'string')));
	}
	
	// --------------------------------------------------------------------
	
	// public function testLanguage()
	// {
	// 	$this->assertEquals(NULL, $this->load->language('test'));
	// }	

	// --------------------------------------------------------------------

	public function test_load_config()
	{
		$this->_setup_config_mock();
		
		$this->assertNull($this->load->config('config', FALSE));
	}
	
	// --------------------------------------------------------------------

	public function test_load_bad_config()
	{
		$this->_setup_config_mock();
		
		$this->setExpectedException(
			'RuntimeException',
			'CI Error: The configuration file foobar.php does not exist.'
			);
		
		$this->load->config('foobar', FALSE);
	}

	// --------------------------------------------------------------------
	
}
