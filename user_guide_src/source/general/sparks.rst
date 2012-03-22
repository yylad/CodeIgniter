######
Sparks
######

Sparks are CodeIgniter's built-in packages. They are modular bits of code that
can be dropped into your application.  They typically provide some added
functionality to your application.

Sparks can be installed manually, or automatically via
a built-in command-line tool.

Installing a Spark via the Command Line
=======================================

In this example, we'll install the `example-spark` via the terminal.

Navigate to the root of your CodeIgniter application where you previously
installed sparks. On OSX or Linux, type:::

	php index.php --sparks install -v1.0.0 example-spark

You should see:::

	[ SPARK ]  Retrieving spark detail from getsparks.org
	[ SPARK ]  From Downtown! Retrieving spark from Mercurial repository at https://github.com/katzgrau/example-spark
	[ SPARK ]  Installing spark
	[ SPARK ]  Spark installed to ./sparks/example-spark/1.0.0 - You're on fire!

Specifying the version isn't required. You can leave that -v1.0.0 option out
in order to get the latest version.

Anyway, now your spark is installed! In your application, try:::

	<?php
	// We always specify the full path from the spark folder
	$this->load->spark('example-spark/1.0.0');
	// echo's "Hello from the example spark!"
	$this->example_spark->printHello();

Now you can install all the sparks you want! You can even autoload sparks in
your application's config/autoload.php:::

	<?php
	$autoload['sparks'] = array('example-spark/1.0.0');

If you feel like contributing your own, check out Making Sparks.

Installing a Spark Manually
===========================

Follow these directions for installing sparks without a script. This is most
practical for developers on shared servers, where using the PHP would prove
difficult or impossible.

	1. Browse or search for the package that you need. As an example, we'll use example-spark.

	2. Find the version you would like to download. This is most likely the top-listed version, which is the latest (sometimes denoted as 'HEAD').

	3. Click the link to "Get example-spark-1.0.0.zip", and download it.

	4. Extract the contents of the zip to a folder inside the sparks/ directory at the base of your application. You should include the version number too.

You should now have a directory like this:::

	/application
	/system
	/sparks
	..../example-spark
	......../1.0.0
	............/config
	............/libraries

Now your spark is installed! Try this from within your application:::

	$this->load->spark('example-spark/1.0.0'); # Don't forget to add the version!
	$this->example_spark->printHello(); # echo's "Hello from the example spark!"

You can also autoload sparks in your application's config/autoload.php:::

	$autoload['sparks'] = array('example-spark/1.0.0');

Searching for New Sparks
========================

Searching for a new spark is easy.  Imagine that we want a spark for parsing
markdown format.  We can just execute:::

  php index.php --spark search markdown

When we do, we'll see a result like:::

	markdown-extra - A markdown extra helper to parse markdown extra
	lack - Simple file based CMS
	markdown - A markdown helper for easy parsing of markdown

Which is a list of relevant sparks that we may choose to install (see above).
