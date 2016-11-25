function pageReload()
{
    window.location.reload(true);
}

function disableRConForm()
{
    document.getElementById("commandTextField").readOnly = true; 
    document.getElementById("passwordTextField").readOnly = true; 
    document.getElementById("submitButton").disabled = true;
    return true;
}