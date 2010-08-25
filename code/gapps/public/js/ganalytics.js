// Load the Google data JavaScript client library  
google.load('gdata', '2.x', {packages: ['analytics']});
// Set the callback function when the library is ready  
google.setOnLoadCallback(gaInit);

// Config
window.gaConfig = {
    accountsMaxResults : 50
};

// gaIni function
function gaInit() {
    // Domready
    $(document).ready(function() {
        setTimeout(function(){
            // AuthSub
            if (gLogin('https://www.google.com/analytics/feeds')) {
                window.gaService = new google.gdata.analytics.AnalyticsService('gaExportAPI_acctSample_v2.0');
                // Retrive accounts list
                window.gaAccounts = new Array();
                window.gaService.getAccountFeed('https://www.google.com/analytics/feeds/accounts/default?max-results='+window.gaConfig.accountsMaxResults, gaHandleAccountFeed, gHandleError);
            }
        }, 4000);
        
    });
}

// Retrive accounts list
function gaHandleAccountFeed(result) {
    var entries = result.feed.getEntries();
    for (var i = 0, entry; entry = entries[i]; ++i) {
        window.gaAccounts.push(entry);
    }
    if (i >= window.gaConfig.accountsMaxResults) {
        window.gaService.getAccountFeed('https://www.google.com/analytics/feeds/accounts/default?max-results='+window.gaConfig.accountsMaxResults+'&start-index='+(window.gaAccounts.length + 1), gaHandleAccountFeed, gHandleError);
    } else {
        appInit();
    }
}

// appInit
function appInit() {
    // append accounts
    var gaAccountNameSelect = $('#ga_account_name');
    for (var i = 0; i < window.gaAccounts.length; i++) {
        gaAccountNameSelect.append('<option value="'+window.gaAccounts[i].getTableId().getValue()+'">'+window.gaAccounts[i].getPropertyValue('ga:AccountName')+' -- '+window.gaAccounts[i].getTitle().getText()+'</option>');
    }
    // Call local onload hanler
    if (gaLocalOnLoadHandler != undefined) {
        gaLocalOnLoadHandler();
    }
    // Call onload handler
    appOnLoadHandler();
}

