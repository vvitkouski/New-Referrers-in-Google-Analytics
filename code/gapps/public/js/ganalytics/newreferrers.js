// Load the Google data JavaScript client library  
google.load('gdata', '2.x', {packages: ['analytics']});
// Set the callback function when the library is ready  
google.setOnLoadCallback(gaInit);

// Config
window.gaConfig = {
    accountsMaxResult : 50
};

// gaIni function
function gaInit() {
    // AuthSub
    gInit('https://www.google.com/analytics/feeds');
    window.gaService = new google.gdata.analytics.AnalyticsService('gaExportAPI_acctSample_v2.0');
    // Domready
    $(document).ready(function() {
        // Retrive accounts list
        window.gaAccounts = new Array();
        window.gaService.getAccountFeed('https://www.google.com/analytics/feeds/accounts/default?max-results='+window.gaConfig.accountsMaxResult, gaHandleAccountFeed, gHandleError);
    });
}

// Retrive accounts list
function gaHandleAccountFeed(result) {
    var entries = result.feed.getEntries();
    for (var i = 0, entry; entry = entries[i]; ++i) {
        window.gaAccounts.push(entry);
    }
    if (i >= window.gaConfig.accountsMaxResult) {
        window.gaService.getAccountFeed('https://www.google.com/analytics/feeds/accounts/default?max-results='+window.gaConfig.accountsMaxResult+'&start-index='+window.gaAccounts.length + 1, gaHandleAccountFeed, gHandleError);
    } else {
        appInit();
    }
}

// appInit
function appInit() {
    // append accounts
    var gaAccountNameSelect = $('#ga_account_name');
    for (var i = 0; i < window.gaAccounts.length; i++) {
        gaAccountNameSelect.append('<option value="'+window.gaAccounts[i].title.getText()+'">'+window.gaAccounts[i].title.getText()+'</option>');
    }
    // Call onload handler
    appOnLoadHandler();
}

