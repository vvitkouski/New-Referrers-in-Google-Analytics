<?php session_start(); ?>
// Config
window.gaNrConfig = {
    feedMaxResults : 100
};

// Domready
$(document).ready(function(){
    // Settings form,
    $('#ga-newreferrers-settings').validate();
    $('#ga-newreferrers-settings').submit(function() {
        if ($(this).valid()) {
            gaNrGenerateReport();
        }
        return false;
    });
    $('#ga_account_name').change(function(){
        loadReports();
    });
});

// GA Onload
function gaLocalOnLoadHandler() {
    $('#ga_account_name option[value=<?php 
        $tableId = isset($_SESSION["gaReferrer"]["currentTableId"]) ? ($_SESSION["gaReferrer"]["currentTableId"]) : "";
        echo $tableId;
    ?>]').attr("selected",true);
    loadReports();
}

// Generate new report
function gaNrGenerateReport() {
    var endDate = new Date();
    var startDate = new Date();
    startDate.setTime(<?php echo time() * 1000 ?> - $('#download_period').val() * 86400000);
    var endDateYear = endDate.getFullYear();
    var endDateMonth = (endDate.getMonth() + 1 < 10) ? '0' + (endDate.getMonth() + 1) : (endDate.getMonth() + 1);
    var endDateDay = (endDate.getDate() < 10) ? '0' + (endDate.getDate() + 1) : endDate.getDate() + 1;
    window.gaEndDateStr = '<?php echo date("Y-m-d") ?>';
    
    var startDateYear = startDate.getFullYear();
    var startDateMonth = (startDate.getMonth() + 1 < 10) ? '0' + (startDate.getMonth() + 1) : (startDate.getMonth() + 1);
    var startDateDay = (startDate.getDate() < 10) ? '0' + startDate.getDate() : startDate.getDate();
    window.gaStartDateStr = startDateYear + '-' + startDateMonth + '-' + startDateDay;

    var feedUrl = 'https://www.google.com/analytics/feeds/data' +
    '?start-date=' + window.gaStartDateStr +
    '&end-date=' + window.gaEndDateStr +
    '&dimensions=ga:pageTitle,ga:pagePath,ga:source' +
    '&filters=ga:pageviews%3E%3D' + $('#min_traffic').val() + ';ga:source!%3D(direct)' +
    '&metrics=ga:pageviews' +
    '&sort=-ga:pageviews' +
    '&max-results=' + window.gaNrConfig.feedMaxResults +
    '&ids=' + $('#ga_account_name').val();
    window.gaNrReferrers = new Array();
    window.gaService.getDataFeed(feedUrl, function(result){gaNrHandleDataFeed(result, false);}, gHandleError);
}

function gaNrHandleDataFeed(result, reportId) {
    var entries = result.feed.getEntries();
    if (entries.length > 0) {
        if (!reportId) {
            var accountAndProfileArray = $('#ga_account_name :selected').text().split(' -- ');
            var newReportUrl = APP_BASE_URL+'/ganalytics/ajaxNrCreateReport/account_name/'+accountAndProfileArray[0]+'/profile_name/'+accountAndProfileArray[1]+'/table_id/'+$('#ga_account_name :selected').val()+'/min_traffic/'+$('#min_traffic').val()+'/download_period/'+$('#download_period').val()+'/compare_period/'+$('#compare_period').val()+'/';
            $.getJSON(newReportUrl, function(data) {
                if (data.error == 0) {
                    reportId = data.report_id;
                    gaNrHandleDataFeed(result, reportId);
                    return true;
                } else {
                    return false;
                }
            });
        } else {
            for (var i = 0, entry; entry = entries[i]; ++i) {
                //alert (entry.getValueOf('ga:source ga:pageviews'));
                window.gaNrReferrers.push(entry);
            }
            if (i >= window.gaNrConfig.feedMaxResults) {
                var feedUrl = 'https://www.google.com/analytics/feeds/data' +
                '?start-date=' + window.gaStartDateStr +
                '&end-date=' + window.gaEndDateStr +
                '&start-index=' + (window.gaNrReferrers.length + 1) +
                '&dimensions=ga:pageTitle,ga:pagePath,ga:source' +
                '&filters=ga:pageviews%3E%3D' + $('#min_traffic').val() + ';ga:source!%3D(direct)' +
                '&metrics=ga:pageviews' +
                '&sort=-ga:pageviews' +
                '&max-results=' + window.gaNrConfig.feedMaxResults +
                '&ids=' + $('#ga_account_name').val();
                window.gaService.getDataFeed(feedUrl, function(result){gaNrHandleDataFeed(result, reportId);}, gHandleError);
            } else {
                saveReferrers(window.gaNrReferrers, reportId);
            }
        }
        /**/
    } else if (reportId) {
        saveReferrers(window.gaNrReferrers, reportId);
    }
}

function saveReferrers(referrers, reportId) {
    var tmpArray = new Array();
    for (var i = 0; i < referrers.length; i++) {
        if (tmpArray.length > 200) {
            sendReferrers(tmpArray, reportId);
            tmpArray = new Array();
        }
        tmpArray.push({
            'report_id' : reportId,
            'host'      : referrers[i].getValueOf('ga:source'),
            'page_path' : referrers[i].getValueOf('ga:pagePath'),
            'visits'    : referrers[i].getValueOf('ga:pageviews') + ''
        });
    }
    if (tmpArray.length) {
        sendReferrers(tmpArray, reportId, function() {
            processReferrers(reportId);
        });
    } else {
        processReferrers(reportId);
    }
}

function sendReferrers(data, reportId, callback) {
    if (callback == undefined) {
        callback = function(){};
    }
    $.post(APP_BASE_URL+'/ganalytics/ajaxnrsavetmpreferrers/report_id/'+reportId+'/', {'referrers' : data, 'report_id' : reportId}, callback);
}


function processReferrers(reportId) {
    $.post(APP_BASE_URL+'/ganalytics/ajaxnrprocessreferrers/account_name/report_id/'+reportId+'/', {'report_id' : reportId}, function(){loadReport(reportId);});
}


function loadReports() {
    $('#ga-main-content').html('Loading... ');
    $.get(APP_BASE_URL+'/ganalytics/ajaxgetreports/table_id/'+$('#ga_account_name').val()+'/', function(data) {
        $('#ga-main-content').html(data);
        $('#ga_nr_contentheadline').text('Recent Reports');
        $('#ga-recent-report-list li a').click(function(){
            loadReport($(this).attr('rel'));
            return false;
        });
    });
}

function loadReport(reportId) {
    $('#ga-main-content').html('Loading... ');
    $.get(APP_BASE_URL+'/ganalytics/ajaxreport/report_id/'+reportId+'/', function(data) {
        $('#ga-main-content').html(data);
        $('#ga_nr_contentheadline').text('Report Details #'+reportId);
        $('#ga-report-back_link').click(function(){
            loadReports();
            return false;
        });
        $('#ga-report').tablesorter();
    });
}