<?php

/**
 * Test: Nette\Debug notices and warnings in console.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Debug;



require __DIR__ . '/../initialize.php';



Debug::$consoleMode = TRUE;
Debug::$productionMode = FALSE;

Debug::enable();



function first($arg1, $arg2)
{
	second(TRUE, FALSE);
}


function second($arg1, $arg2)
{
	third(array(1, 2, 3));
}


function third($arg1)
{
	$x++;
	rename('..', '..');
}


try	{
	first(10, 'any string');

} catch (Exception $e) {
	T::dump($e);
}




__halt_compiler() ?>

------EXPECT------

Notice: Undefined variable: x in %a%
Exception PhpException: rename(..,..): %a%
