/* GLOBAL JS */
// Init google
function gInit(scope) {
    if (!google.accounts.user.checkLogin(scope)) {
        google.accounts.user.login(scope);
    }
}

// onLoad handler
function appOnLoadHandler() {
    $(document).ready(function(){
        $('#loading_container').hide();
        $('#loaded_container').show();
    });
}

// Error handler
function gHandleError(e) {
    alert('Error');
    alert(e);
}