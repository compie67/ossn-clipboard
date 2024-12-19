<?php
/**
 * Activity Log View
 *
 * @package Open Source Social Network
 */

// Haal de activiteiten op
$activities = isset($params['data']['activities']) ? $params['data']['activities'] : [];

if (empty($activities)) {
    echo "<p>" . ossn_print('clipboard:no_activity') . "</p>";
    return;
}


echo "<div class='activity-log-container'>";
echo "<h3>" . ossn_print('clipboard:activity_log_title') . "</h3>";

if (!empty($activities)) {
    echo "<ul class='activity-log-list'>";
    foreach ($activities as $activity) {
        echo "<li>";
        echo "<p><strong>" . date('d-m-Y H:i', $activity['time']) . ":</strong> ";

        if ($activity['type'] === 'Post') {
            echo ossn_print('clipboard:post') . ": " . $activity['content'];
            if (!empty($activity['link'])) {
                echo " (<a href='{$activity['link']}' target='_blank'>" . ossn_print('clipboard:view') . "</a>)";
            }
        } elseif ($activity['type'] === 'Comment') {
            echo ossn_print('clipboard:comment') . " " . ossn_print('clipboard:by') . " <strong>{$activity['author']}</strong>: " . $activity['content'];
        }

        echo "</p>";
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>" . ossn_print('clipboard:no_activities') . "</p>";
}

echo "</div>";
?>
