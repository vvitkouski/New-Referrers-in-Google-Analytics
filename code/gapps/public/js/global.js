/* GLOBAL JS */
// Domready
$(document).ready(function() {
    // Logout button event
    $('#header-sign-out').click(gLogout);
});

// Init google
function gLogin(scope) {
    if (!google.accounts.user.checkLogin(scope)) {
        google.accounts.user.login(scope);
        return false;
    } else {
        return true;
    }
}

// gLogout
function gLogout() {
    google.accounts.user.logout();
    setTimeout(function(){window.location.reload()}, 2000);
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
    $('#loaded_container').hide();
    $('#loading_container').show();
    $('#loading_container').html(e.message);
}