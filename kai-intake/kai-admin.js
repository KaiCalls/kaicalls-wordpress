jQuery(document).ready(function ($) {
    'use strict';

    function fetchWidgetData() {
        $.ajax({
            url: kai_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'kai_fetch_widget_data',
                nonce: kai_ajax.nonce
            },
            dataType: 'json',
            timeout: 15000,
            success: function (response) {
                if (response.success) {
                    let content = '<h5>' + response.data.leadsThisWeek + ' leads this week</h5>';
                    content += '<ul class="kai-recent-leads">';
                    if (response.data.recentLeads.length > 0) {
                        response.data.recentLeads.forEach(function (lead) {
                            const leadDate = new Date(lead.created_at).toLocaleDateString();
                            content += '<li><strong>' + $('<div>').text(lead.name).html() + '</strong> (' + leadDate + ')</li>';
                        });
                    } else {
                        content += '<li>No recent leads.</li>';
                    }
                    content += '</ul>';
                    $('#kai-widget-content').html(content);
                } else {
                    $('#kai-widget-content').html('<p class="error">' + (response.data.error || 'Error fetching data.') + '</p>');
                }
            },
            error: function () {
                $('#kai-widget-content').html('<p class="error">Error fetching data. Please try again later.</p>');
            }
        });
    }

    if ($('#kai_intake_dashboard_widget').length) {
        fetchWidgetData();
    }
});
