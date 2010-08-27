<?php session_start(); ?>
// Config
window.gaNrConfig = {
    feedMaxResults : 200
};

// Domready
$(document).ready(function(){
    // Settings form,
    $('#ga-newreferrers-settings').validate();
    $('#ga-newreferrers-settings').submit(function() {
        if ($(this).valid()) {
            return gaNrGenerateReport();
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

function gaDateToStr(date) {
    var dateYear = date.getFullYear();
    var dateMonth = (date.getMonth() + 1 < 10) ? '0' + (date.getMonth() + 1) : (date.getMonth() + 1);
    var dateDay = (date.getDate() < 10) ? '0' + (date.getDate() + 1) : date.getDate() + 1;
    return dateYear + '-' + dateMonth + '-' + dateDay;
}

// Generate new report
function gaNrGenerateReport() {
    // loading message
    $('#ga-main-content').html('Loading... ');
    // dates
    var endDate = new Date();
    var startDate = new Date();
    var compareDate = new Date();
    var compareEndDate = new Date();
    var downloadPeriod = parseInt($('#download_period').val());
    var comparePeriod = parseInt($('#compare_period').val());
    if (comparePeriod <= downloadPeriod) {
        comparePeriod = downloadPeriod;
    }
    var currentTime = <?php echo time() * 1000 ?>;
    
    startDate.setTime(currentTime - downloadPeriod * 86400000);
    compareDate.setTime(currentTime - comparePeriod * 86400000);
    compareEndDate.setTime(currentTime - (downloadPeriod - 1) * 86400000 );
    window.gaEndDateStr = '<?php echo date("Y-m-d") ?>';
    window.gaStartDateStr = gaDateToStr(startDate);
    window.gaCompareDateStr = gaDateToStr(compareDate);
    window.gaCompareEndDateStr = gaDateToStr(compareEndDate);
    var feedUrl = 'https://www.google.com/analytics/feeds/data' +
    '?start-date=' + window.gaStartDateStr +
    '&end-date=' + window.gaEndDateStr +
    '&dimensions=ga:pageTitle,ga:pagePath,ga:visitCount,ga:source' +
    '&filters=ga:medium%3D%3Dreferral;ga:visits%3E%3D' + $('#min_traffic').val() + ';ga:source!%3D(direct)' +
    '&metrics=ga:visits' +
    '&sort=-ga:visits' +
    '&max-results=' + window.gaNrConfig.feedMaxResults +
    '&ids=' + $('#ga_account_name').val();
    window.gaNrReferrers = new Array();
    window.gaNrCompareReferrers = new Array();
    window.gaService.getDataFeed(feedUrl, function(result){gaNrHandleDataFeed(result, false);}, gHandleError);
    return false;
}

function gaNrHandleDataFeed(result, reportId, compare) {
    if (compare == undefined) compare = false;
    var entries = result.feed.getEntries();
    if (entries.length > 0) {
        if (!reportId) {
            var accountAndProfileArray = $('#ga_account_name :selected').text().split(' -- ');
            var newReportUrl = APP_BASE_URL+'/ganalytics/ajaxNrCreateReport/account_name/'+accountAndProfileArray[0]+'/profile_name/'+accountAndProfileArray[1]+'/table_id/'+$('#ga_account_name :selected').val()+'/min_traffic/'+$('#min_traffic').val()+'/download_period/'+$('#download_period').val()+'/compare_period/'+$('#compare_period').val()+'/';
            $.getJSON(newReportUrl, function(data) {
                if (data.error == 0) {
                    reportId = data.report_id;
                    gaNrHandleDataFeed(result, reportId, compare);
                    return true;
                } else {
                    return false;
                }
            });
        } else {
            for (var i = 0, entry; entry = entries[i]; ++i) {
                if (!compare) {
                    window.gaNrReferrers.push(entry);
                } else {
                    window.gaNrCompareReferrers.push(entry);
                }
            }
            if (i >= window.gaNrConfig.feedMaxResults || !compare) {
                if (i < window.gaNrConfig.feedMaxResults && !compare) {
                    compare = true;
                    if (window.gaCompareDateStr == window.gaStartDateStr) {
                        gaNrHandleDataFeed({feed: {getEntries: function(){return new Array();}}}, reportId, compare);
                        return false;
                    }
                }
                if (compare) {
                    var _startDate = window.gaCompareDateStr;
                    var _endDate =  window.gaCompareEndDateStr;
                    var _startIndex = window.gaNrCompareReferrers.length + 1;
                    var _filter = 'ga:medium%3D%3Dreferral;ga:source!%3D(direct)';
                } else {
                    var _startDate = window.gaStartDateStr;
                    var _endDate = window.gaEndDateStr;
                    var _startIndex = window.gaNrReferrers.length + 1;
                    var _filter = 'ga:medium%3D%3Dreferral;ga:visits%3E%3D' + $('#min_traffic').val() + ';ga:source!%3D(direct)';
                }
                var feedUrl = 'https://www.google.com/analytics/feeds/data' +
                '?start-date=' + _startDate +
                '&end-date=' + _endDate +
                '&start-index=' + _startIndex +
                '&dimensions=ga:pageTitle,ga:pagePath,ga:visitCount,ga:source' +
                '&filters=' + _filter +
                '&metrics=ga:visits' +
                '&sort=-ga:visits' +
                '&max-results=' + window.gaNrConfig.feedMaxResults +
                '&ids=' + $('#ga_account_name').val();
                window.gaService.getDataFeed(feedUrl, function(result){gaNrHandleDataFeed(result, reportId, compare);}, gHandleError);
            } else if (compare) {
                saveReferrers(window.gaNrReferrers, window.gaNrCompareReferrers, reportId);
            }
        }
        /**/
    } else if (reportId && compare) {
        saveReferrers(window.gaNrReferrers, window.gaNrCompareReferrers, reportId);
    } else if (reportId && !compare) {
        compare = true;
        var _startDate = window.gaCompareDateStr;
        var _endDate =  window.gaCompareEndDateStr;
        var _startIndex = window.gaNrCompareReferrers.length + 1;
        var _filter = 'ga:medium%3D%3Dreferral;ga:source!%3D(direct)';
        var feedUrl = 'https://www.google.com/analytics/feeds/data' +
        '?start-date=' + _startDate +
        '&end-date=' + _endDate +
        '&start-index=' + _startIndex +
        '&dimensions=ga:pageTitle,ga:pagePath,ga:visitCount,ga:source' +
        '&filters=' + _filter +
        '&metrics=ga:visits' +
        '&sort=-ga:visits' +
        '&max-results=' + window.gaNrConfig.feedMaxResults +
        '&ids=' + $('#ga_account_name').val();
        window.gaService.getDataFeed(feedUrl, function(result){gaNrHandleDataFeed(result, reportId, compare);}, gHandleError);
    } else {
        alert('Empty result');
        loadReports();
    }
}

function saveReferrers(referrers, compareReferrers, reportId) {
    var tmpArray = new Array();
    for (var i = 0; i < referrers.length; i++) {
        if (tmpArray.length > 200) {
            sendReferrers(tmpArray, reportId);
            tmpArray = new Array();
        }
        var notIntCompare = true;
        for (var j = 0; j < compareReferrers.length; j++) {
            if (referrers[i].getValueOf('ga:source') == compareReferrers[j].getValueOf('ga:source')) {
                notIntCompare = false;
                break;
            }
        }
        if (notIntCompare) {
            tmpArray.push({
                'report_id' : reportId,
                'host'      : referrers[i].getValueOf('ga:source'),
                'page_path' : referrers[i].getValueOf('ga:pagePath'),
                'visits'    : referrers[i].getValueOf('ga:visits') + ''
            });
        }
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
    $.post(APP_BASE_URL+'/ganalytics/ajaxnrprocessreferrers/report_id/'+reportId+'/', {'report_id' : reportId}, function(){loadReport(reportId);});
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