<!DOCTYPE html>
<html>
    <head>
        <title>Account Information</title>
        <link rel="stylesheet" href="styles/main.css" />
        <link
            href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap"
            rel="stylesheet" />
    </head>
    <body>
        <header>
            <!-- Top Bar Navigation  -->
            <div class ="dashboardPage" id="main">
            <div id="navHead" class="navHead">
                <nav class="accountDrop">
                    <button
                        class="openBtn"
                        onclick="closeNav()"
                        id="openCloseButton">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <span>
                        <i class="fa-solid fa-book-open-reader"></i>
                    </span>
                </nav>
                <!-- User Profile Dropdown -->
                <div id="userProfileDropdown" class="userProfileDropdown">
                    <?php include "includes/navAccount.php"?>
                </div>
            </div>
        </div>
        </header>
        <main>
        <div class="container">
            <h1>Account Information</h1>
            <form>
            <div id="loading" style="display: none;">
            Uploading...
            </div>
            <div id="message"></div>
            </form>
        </div>
        </main>
    </body>
</html>
