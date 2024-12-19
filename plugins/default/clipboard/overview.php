<?php
/**
 * Clipboard Overview
 *
 * @package Open Source Social Network
 */

// Haal de data en de gebruiker op
// Retrieve data and user
$data = $params['data'];
$user = $params['user'];

// Start de HTML-weergave voor het clipboard-overzicht
// Start the HTML display for the clipboard overview
echo "<div class='clipboard-overview'>";

// Toon de titel met de naam van de gebruiker
// Display the title with the user's name
echo "<h3>" . ossn_print('clipboard:user_content', array($user->fullname)) . "</h3>";

// ---------- TIJDLIJNBERICHTEN WEERGEVEN ----------
// ---------- DISPLAY TIMELINE POSTS ----------
echo "<h4>" . ossn_print('clipboard:posts') . "</h4>";
if (!empty($data['posts'])) {
    // Loop door alle tijdlijnberichten
    // Loop through all timeline posts
    foreach ($data['posts'] as $post) {
        // Decodeer de beschrijving van de post
        // Decode the post description
        $post_data = json_decode($post->description, true);
        $post_text = $post_data['post'] ?? ossn_print('clipboard:no_description');
        $post_date = date('d-m-Y H:i', $post->time_created);

        // Toon het tijdlijnbericht
        // Display the timeline post
        echo "<div class='clipboard-post'>";
        echo "<p><strong>{$post_date}:</strong> {$post_text}</p>";

        // Controleer of er een afbeelding bij de post zit
        // Check if the post contains an image
        $photo_url = $post->getPhotoURL();
        if ($photo_url) {
            echo "<div class='clipboard-photo'>";
            echo "<img src='{$photo_url}' alt='Post afbeelding'>";
            echo "</div>";
        }

        // ---------- REACTIES WEERGEVEN ----------
        // ---------- DISPLAY COMMENTS ----------
       echo "<h5>" . ossn_print('clipboard:comments') . "</h5>";
        $comments = new OssnComments();
        $post_comments = $comments->GetComments($post->guid);

        if (!empty($post_comments) && is_array($post_comments)) {
            echo "<div class='clipboard-comments'>";
            foreach ($post_comments as $comment) {
                $comment_text = htmlspecialchars($comment->{'comments:post'} ?? ossn_print('clipboard:no_description'));
                $comment_date = date('d-m-Y H:i', $comment->time_created);

                // Gebruikersnaam ophalen
                $commenter = ossn_user_by_guid($comment->owner_guid);
                $commenter_name = $commenter ? $commenter->username : ossn_print('clipboard:unknown_user');

                echo "<div class='clipboard-comment'>";
                echo "<p><strong>{$comment_date}, by {$commenter_name}:</strong> {$comment_text}</p>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p>" . ossn_print('clipboard:no_comments') . "</p>";
        }

        echo "</div>"; // Einde tijdlijnbericht
        // End of timeline post
    }
} else {
    // Geen berichten gevonden
    // No posts found
    echo "<p>" . ossn_print('clipboard:no_posts') . "</p>";
}

// ---------- BLOGS WEERGEVEN ----------
// ---------- DISPLAY BLOGS ----------
echo "<h4>" . ossn_print('clipboard:blogs') . "</h4>";
if (!empty($data['blogs'])) {
    // Loop door alle blogs
    // Loop through all blogs
    foreach ($data['blogs'] as $blog_post) {
        $blog_title = htmlspecialchars($blog_post->title);
        $blog_date = date('d-m-Y H:i', $blog_post->time_created);
        $blog_url = $blog_post->profileURL();

        // Toon bloginformatie
        // Display blog information
        echo "<div class='clipboard-blog'>";
        echo "<p><strong><a href='{$blog_url}' target='_blank'>{$blog_title}</a></strong> - {$blog_date}</p>";
        echo "</div>";
    }
} else {
    // Geen blogs gevonden
    // No blogs found
    echo "<p>" . ossn_print('clipboard:no_blogs') . "</p>";
}

// ---------- DOWNLOAD-KNOP TOEVOEGEN ----------
// ---------- ADD DOWNLOAD BUTTON ----------
$download_url = ossn_site_url("clipboard/{$user->username}/download");

// Toon downloadknop
// Display download button
echo "<div class='clipboard-download'>";
echo "<a href='{$download_url}'>" . ossn_print('clipboard:download_all') . "</a>";
echo "</div>";

echo "</div>"; // Einde van clipboard-overzicht
// End of clipboard overview
?>
