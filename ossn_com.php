<?php
/**
 * Clipboard Component
 *
 * @package Open Source Social Network
 */

define('__CLIPBOARD__', ossn_route()->com . 'Clipboard/');

/**
 * Initialize Clipboard Component
 */
function clipboard_init() {
    // Voeg CSS toe
    ossn_extend_view('css/ossn.default', 'Clipboard/css/clipboard');

    // Voeg een tab toe in "Bewerken profiel"
    if (ossn_isLoggedin()) {
        ossn_add_hook('profile', 'edit:section', 'clipboard_edit_tab');
        ossn_register_menu_item('profile/edit/tabs', array(
            'name' => 'clipboard',
            'href' => '?section=clipboard',
            'text' => 'Clipboard', // Direct de tekst "Clipboard" instellen
        ));
    }

    // Registreer pagina-handlers
    ossn_register_page('clipboard', 'clipboard_page_handler');
    ossn_register_page('activity_log', 'activity_log_page_handler');
}

/**
 * Add clipboard tab to profile edit section
 */
function clipboard_edit_tab($hook, $type, $return, $params) {
    if ($params['section'] == 'clipboard') {
        return ossn_plugin_view('clipboard/profile_tab');
    }
}

/**
 * Page handler for Clipboard
 */
function clipboard_page_handler($pages) {
    if (!ossn_isLoggedin()) {
        ossn_error_page();
        return;
    }

    $username = isset($pages[0]) ? $pages[0] : null;

    // Controleer de toegangsrechten
    if (ossn_isAdminLoggedin()) {
        $user = $username ? ossn_user_by_username($username) : ossn_loggedin_user();
    } else {
        $user = ossn_loggedin_user();
    }

    if (!$user || !$user instanceof OssnUser) {
        error_log("DEBUG: Ongeldige gebruiker of gebruiker niet gevonden.");
        ossn_error_page();
        return;
    }

    if (isset($pages[1]) && $pages[1] === 'download') {
        clipboard_download_data($user);
        exit;
    }

    $data = clipboard_fetch_user_data($user);

    $title = ossn_print('clipboard:title', array($user->fullname));
    $content = ossn_plugin_view('clipboard/overview', array(
        'user' => $user,
        'data' => $data,
    ));
    $page = ossn_set_page_layout('contents', array(
        'content' => $content,
    ));
    echo ossn_view_page($title, $page);
}

/**
 * Page handler for Activity Log
 */
function activity_log_page_handler($pages) {
    if (!ossn_isLoggedin()) {
        ossn_error_page();
        return;
    }

    $user = ossn_loggedin_user();
    $activities = fetch_user_activities($user);

    $title = ossn_print('clipboard:activity_log', array($user->fullname));
    $content = ossn_plugin_view('clipboard/activity_log', array(
        'user' => $user,
        'activities' => $activities,
    ));
    $page = ossn_set_page_layout('contents', array(
        'content' => $content,
    ));
    echo ossn_view_page($title, $page);
}

/**
 * Fetch user activities for the activity log
 */
function fetch_user_activities($user) {
    $wall = new OssnWall();
    $user_posts = $wall->GetUserPosts($user, array('page_limit' => 1000));

    $comments = new OssnComments();
    $all_comments = [];
    
    foreach ($user_posts as $post) {
        // Controleer of $post een geldig object is
        if (!$post || !isset($post->guid)) {
            error_log("DEBUG: Invalid post data or missing GUID.");
            continue;
        }

        $post_comments = $comments->GetComments($post->guid);

        // Controleer op geldig resultaat van GetComments
        if ($post_comments === false || !is_array($post_comments)) {
            error_log("DEBUG: Invalid post_comments type for post GUID {$post->guid}.");
            continue;
        }

        $all_comments = array_merge($all_comments, $post_comments);
    }

    $activities = [];
    foreach ($user_posts as $post) {
        $activities[] = [
            'time' => $post->time_created,
            'type' => 'Post',
            'content' => json_decode($post->description, true)['post'] ?? ossn_print('clipboard:no_description'),
            'link' => ossn_site_url("post/view/{$post->guid}"),
        ];
    }

    foreach ($all_comments as $comment) {
        if (!$comment || !isset($comment->owner_guid) || !isset($comment->{'comments:post'})) {
            error_log("DEBUG: Invalid comment data or missing properties.");
            continue;
        }

        $commenter = ossn_user_by_guid($comment->owner_guid);
        $commenter_name = $commenter ? $commenter->fullname : ossn_print('clipboard:unknown_user');

        $activities[] = [
            'time' => $comment->time_created,
            'type' => 'Comment',
            'content' => htmlspecialchars($comment->{'comments:post'}),
            'link' => null,
            'author' => $commenter_name,
        ];
    }

    // Sorteer activiteiten op tijd (nieuwste eerst)
    usort($activities, function ($a, $b) {
        return $b['time'] - $a['time'];
    });

    return $activities;
}

/**
 * Fetch user data for clipboard
 */
function clipboard_fetch_user_data($user) {
    $wall = new OssnWall();
    $user_posts = $wall->GetUserPosts($user, array('page_limit' => 1000));

    // Controleer of de Blog-klasse beschikbaar is
    // check if blog class is availble
    if (class_exists('Blog')) {
        $blog = new Blog();
        $user_blogs = $blog->getUserBlogs($user);
    } else {
        error_log('Blog class not found. The Blogs component might not be installed or activated.');
        $user_blogs = array(); // Geen blogs beschikbaar
    }

    return array(
        'posts' => $user_posts,
        'blogs' => $user_blogs,
    );
}

/**
 * Download user data
 */
function clipboard_download_data($user) {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="user_data.txt"');

    $data = clipboard_fetch_user_data($user);

    echo "Timeline Posts:\n";
    if (!empty($data['posts'])) {
        foreach ($data['posts'] as $post) {
            $post_data = json_decode($post->description, true);
            $post_text = $post_data['post'] ?? 'No description';
            $post_date = date('d-m-Y H:i', $post->time_created);
            echo "{$post_date}: {$post_text}\n";
        }
    } else {
        echo "No posts found.\n";
    }
}

// Initialiseer de module
ossn_register_callback('ossn', 'init', 'clipboard_init');
