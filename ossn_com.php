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
    // CSS toevoegen
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

    // Registreer een pagina-handler voor de module
    ossn_register_page('clipboard', 'clipboard_page_handler');
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
 *
 * @param array $pages Pages
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

    // Controleer of de gebruiker geldig is
    if (!$user || !$user instanceof OssnUser) {
        error_log("DEBUG: Ongeldige gebruiker of gebruiker niet gevonden.");
        ossn_error_page();
        return;
    }

    // Download-functionaliteit
    if (isset($pages[1]) && $pages[1] === 'download') {
        clipboard_download_data($user);
        exit;
    }

    // Bekijk de gegevens
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
 * Fetch user data
 */
function clipboard_fetch_user_data($user) {
    // Haal tijdlijnberichten op
    $wall = new OssnWall();
    $user_posts = $wall->GetUserPosts($user, array('page_limit' => 1000));

    // Haal blogs op
    $blog = new Blog();
    $user_blogs = $blog->getUserBlogs($user);

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

    // Tijdlijnberichten downloaden
    echo "Timeline Posts:\n";
    if (!empty($data['posts'])) {
        $comments = new OssnComments();
        foreach ($data['posts'] as $post) {
            $post_data = json_decode($post->description, true);
            $post_text = $post_data['post'] ?? 'No description';
            $post_date = date('d-m-Y H:i', $post->time_created);
            echo "{$post_date}: {$post_text}\n";

            // Controleer op afbeelding bij de post
            $photo_url = $post->getPhotoURL();
            if ($photo_url) {
                echo "\tPhoto: {$photo_url}\n";
            }

            // Reacties ophalen en toevoegen
            $post_comments = $comments->GetComments($post->guid);
            if (!empty($post_comments)) {
                foreach ($post_comments as $comment) {
                    $comment_text = htmlspecialchars_decode($comment->{'comments:post'} ?? 'No comment text');
                    $comment_date = date('d-m-Y H:i', $comment->time_created);
                    $commenter = ossn_user_by_guid($comment->owner_guid);

                    // Check of de gebruiker geldig is
                    $commenter_name = $commenter ? $commenter->username : 'Unknown User';

                    echo "\tReply ({$comment_date}, by {$commenter_name}): {$comment_text}\n";

                    // Controleer op afbeelding bij de reactie
                    $comment_photo = ossn_get_file($comment->guid, 'commentphoto');
                    if ($comment_photo) {
                        $photo_url = $comment_photo->getURL();
                        echo "\t\tPhoto in reply: {$photo_url}\n";
                    }
                }
            } else {
                echo "\tNo replies.\n";
            }
        }
    } else {
        echo "No posts found.\n";
    }

    // Blogs downloaden
    echo "\nBlogs:\n";
    if (!empty($data['blogs'])) {
        foreach ($data['blogs'] as $blog_post) {
            $blog_title = htmlspecialchars_decode($blog_post->title);
            $blog_content = htmlspecialchars_decode(strip_tags($blog_post->description)); // HTML-tags verwijderen
            $blog_date = date('d-m-Y H:i', $blog_post->time_created);
            $blog_url = $blog_post->profileURL();

            echo "Title: {$blog_title}\n";
            echo "Date: {$blog_date}\n";
            echo "Content:\n{$blog_content}\n";
            echo "URL: {$blog_url}\n";
            echo "-------------------------\n";
        }
    } else {
        echo "No blogs found.\n";
    }
}

// Initialiseer de module
ossn_register_callback('ossn', 'init', 'clipboard_init');
