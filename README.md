28-12-2024
Changes Made
Bug Fix: Missing Blog Class Handling

Added a check in clipboard_fetch_user_data() to verify if the Blog class exists before using it.
If the Blog class is unavailable (e.g., the Blogs component is not installed), the code logs a warning and gracefully defaults to an empty list of blogs. This prevents crashes in environments without the Blogs component.
Improved Fallback Logic

Ensured that the Clipboard component remains functional even when optional dependencies, like the Blogs component, are missing.
Enhanced Debugging

Added error_log statements to provide useful debugging information when:
Posts or comments contain invalid data.
The Blog class is missing or unavailable.
Updated ossn_com.php

Improved the structure and readability of the file.
Added robust error handling for unexpected situations (e.g., missing GUIDs or invalid objects).
Updated component.xml

Bumped the component version to 2.1 to reflect the latest updates.
Enhanced the description to better communicate the component’s purpose and functionality.
Retained the MIT license, which allows free use, modification, and distribution with attribution.

19-12-2024
code added and made complete

test could be at shadow.nlsociaal.nl


Clipboard Component for Open Source Social Network (OSSN)

Overview

The Clipboard Component is a custom extension for the Open Source Social Network (OSSN) that provides additional functionality for users to view and manage their data, including timeline posts and blogs. This component enhances the user profile section by introducing a new tab and associated features for better data management.

Features

Custom Tab in Profile Edit Section: Adds a "Clipboard" tab to the profile edit section.

Data Fetching: Retrieves timeline posts and blogs for the logged-in user.

Data Export: Allows users to download their data in a plain text format.

Admin Access: Enables administrators to access and manage user data.

Installation

Clone or download the Clipboard component repository.

Place the Clipboard folder into the OSSN components directory:

/path/to/ossn/components/

Log in to the OSSN administrator dashboard.

Navigate to the Components section and activate the Clipboard component.

Usage

1. Profile Edit Tab

Once activated, a new "Clipboard" tab will appear in the Edit Profile section. Users can navigate to this tab to view data such as timeline posts and blogs.

2. Data Viewing

The component displays an overview of the user's timeline posts and blogs. This includes:

Post content and timestamps.

Blog titles, content previews, and publication dates.

3. Data Download

Users can download their data by visiting the Clipboard tab and selecting the Download Data option. The data will be exported in a plain text format, including:

Timeline posts with comments and attached media (if any).

Blog details such as titles, content, and URLs.

Code Breakdown

Key Functions

clipboard_init()

Registers hooks and menu items.

Extends the default CSS to include Clipboard-specific styling.

Adds the Clipboard tab to the profile edit section.

clipboard_edit_tab()

Returns the view for the Clipboard tab in the profile edit section.

clipboard_page_handler()

Handles routing for the Clipboard component.

Supports functionalities such as displaying user data and initiating downloads.

clipboard_fetch_user_data($user)

Fetches timeline posts and blogs for the specified user.

Returns data as an associative array.

clipboard_download_data($user)

Outputs user data as a downloadable plain text file.

Includes timeline posts, comments, and blogs with details.

Folder Structure

Clipboard/
|-- css/
|   |-- clipboard.css         # Styles for the component
|-- views/
|   |-- clipboard/            # Contains view files for displaying data
|-- README.md                 # Documentation file
|-- Clipboard.php             # Main component logic

Example Data Structure

Timeline Posts

2024-12-19 15:30: Example post content
	Photo: https://example.com/photo.jpg
	Reply (2024-12-19 15:45, by User123): Example comment text
		Photo in reply: https://example.com/commentphoto.jpg

Blogs

Title: Example Blog Title
Date: 2024-12-19 14:00
Content:
This is an example blog post content.
URL: https://example.com/blogpost

Compatibility

Requires Open Source Social Network (OSSN) version 6.0 or higher.

Contributing

Contributions are welcome! If you'd like to contribute, please follow these steps:

Fork the repository.

Create a feature branch (git checkout -b feature-branch-name).

Commit your changes (git commit -m 'Add some feature').

Push to the branch (git push origin feature-branch-name).

Open a pull request.
