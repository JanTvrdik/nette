<?php

/**
 * Test: Nette\DI\Configurator and services inheritance and overwriting.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI\Configurator;



require __DIR__ . '/../bootstrap.php';



$configurator = new Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.inheritance3.neon')
	->createContainer();


Assert::true( $container->getService('application') instanceof Nette\Application\Application );
