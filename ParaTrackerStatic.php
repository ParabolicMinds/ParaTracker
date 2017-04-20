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
        include 'ParaFunc.php';
    }
    else
    {
        echo '--> <h3 class="errorMessage">ParaFunc.php not found - cannot continue!</h3> <!--';
        exit();
    }
}

//Check to see if an update needs done, and do it
checkForAndDoUpdateIfNecessary();


if (file_exists("info/" . dynamicIPAddressPath . "serverDump.txt") && file_get_contents("info/" . dynamicIPAddressPath . "serverDump.txt") != "")
{
    //Server dump detected - connection assumed successful! Rendering a normal page.
    if(strtolower(paraTrackerSkin) == "json")
    {
        //We are running in JSON mode! Output the JSON response here.
        renderJSONPage();
    }
    else
    {
        //Render a normal tracker page
        $output = renderNormalHTMLPage();

        //Flush the output buffer to the client
        ob_end_flush();
    }
}
else
{
    //Could not connect to the server! Display error page.
    if(strtolower(paraTrackerSkin) == "json")
    {
        //We are running in JSON mode! Output the JSON response here.
        renderJSONPage();
    }
    else
    {
        //Render a normal error page
        $output = renderNoConnectionHTMLPage();

    }
}

echo "-->";

echo $output;

//Flush the output buffer to the client
ob_end_flush();

?>