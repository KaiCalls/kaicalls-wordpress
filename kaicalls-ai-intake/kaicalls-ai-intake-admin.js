jQuery(document).ready(function ($) {
    'use strict';

    const config = window.kaicallsAiIntakeAdmin || {};
    const messages = config.messages || {};
    const $widgetContent = $('#kaicalls-ai-intake-widget-content');

    function getMessage(key, fallback) {
        return messages[key] || fallback;
    }

    function renderError(message) {
        $widgetContent.empty().append(
            $('<p>').addClass('error').text(message || getMessage('fetchError', 'Error fetching data.'))
        );
    }

    function fetchWidgetData() {
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'kaicalls_ai_intake_fetch_widget_data',
                nonce: config.nonce
            },
            dataType: 'json',
            timeout: 15000,
            success: function (response) {
                if (!response.success) {
                    const errorMessage = response.data && response.data.error
                        ? response.data.error
                        : getMessage('fetchError', 'Error fetching data.');
                    renderError(errorMessage);
                    return;
                }

                const recentLeads = Array.isArray(response.data.recentLeads)
                    ? response.data.recentLeads
                    : [];
                const leadsThisWeek = parseInt(response.data.leadsThisWeek, 10) || 0;
                const leadsLabel = getMessage('leadsThisWeek', '%d leads this week').replace('%d', leadsThisWeek);

                $widgetContent.empty();
                $('<h5>').text(leadsLabel).appendTo($widgetContent);

                const $list = $('<ul>').addClass('kaicalls-ai-intake-recent-leads').appendTo($widgetContent);
                if (recentLeads.length > 0) {
                    recentLeads.forEach(function (lead) {
                        const createdAt = new Date(lead.created_at);
                        const leadDate = Number.isNaN(createdAt.getTime()) ? '' : ' (' + createdAt.toLocaleDateString() + ')';
                        const $item = $('<li>');

                        $('<strong>').text(lead.name || getMessage('unknownLead', 'Unknown lead')).appendTo($item);
                        $item.append(leadDate);
                        $list.append($item);
                    });
                } else {
                    $('<li>').text(getMessage('noRecentLeads', 'No recent leads.')).appendTo($list);
                }
            },
            error: function () {
                renderError(getMessage('fetchTryLater', 'Error fetching data. Please try again later.'));
            }
        });
    }

    if ($('#kaicalls_ai_intake_dashboard_widget').length) {
        fetchWidgetData();
    }
});
