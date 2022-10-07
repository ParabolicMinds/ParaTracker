<?php
/*

ParaTracker is released under the MIT license, which reads thus:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/


if(!isset($dynamicTrackerCalledFromCorrectFile))
{
	//We are running in static mode. Set the output buffer here, for JSON compatibility.
	//ParaTrackerDynamic.php already does this for itself, so we have to check if it was already done before doing another.
	ob_start();
}

echo "<!--";

//This variable will allow ParaFunc to execute.
//The point of this variable is to prevent ParaFunc from being executed directly,
//as it would be a complete waste of CPU power.
$safeToExecuteParaFunc = "1";

//Check to see if we're running in Dynamic mode. If we are, DO NOT load ParaFunc.php, as it
//has already been loaded.
if(!isset($dynamicTrackerCalledFromCorrectFile))
{
	//We are not running in dynamic mode, so load ParaFunc.php
	//ParaFunc.php MUST exist, or we must terminate!
	if (file_exists("ParaFunc.php"))
	{
		include_once 'ParaFunc.php';
	}
	else
	{
		echo '--> <h3 class="errorMessage">ParaFunc.php not found - cannot continue!</h3>';
		exit();
	}
}

//Check to see if an update needs done, and do it
if(checkForAndDoUpdateIfNecessary($dynamicIPAddressPath) === 0)
{
	// I'm not sure what I was doing, sometimes returning 0 and sometimes false...
	// pretty sure 0 was for "Stop and do not continue" and false was for "Did not update but continue anyway"
	exit();
}

//Just as good practice, let's declare this
$output = "";

//We no longer need to check to see if the connection was successful. Just output a page.
if(strtolower(paraTrackerSkin) == "json")
{
	//Since we're giving a JSON response, we have to give the page a JSON header
	header("Content-Type: application/json");

	//We are running in JSON mode! Output the JSON response here.
	$output = renderJSONOutput($dynamicIPAddressPath);

	//Remove everything from the buffer so we can supply JSON info only
	ob_clean();
}
else
{
	//Add an HTML end comment, then render a normal tracker page
	$output = "-->";
	$output .= htmlDeclarations('', '');
	$output .= renderNormalHTMLPage($dynamicIPAddressPath);

}

echo $output;

//Flush the output buffer to the client
ob_end_flush();

?>
