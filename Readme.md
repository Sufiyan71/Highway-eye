
# Highway Toll Dashboard

A simple PHP-based web dashboard to display highway toll data, featuring vehicle counts, filterable records (by type and date range), pagination, and media (image/video) viewing capabilities.

## Features

*   Dashboard overview cards showing counts per vehicle type and total.
*   Filter toll records by Vehicle Type, Start Date, and End Date.
*   Paginated display of toll records.
*   View associated images or videos for a toll record in a modal popup.
*   Neumorphic UI styling using Tailwind CSS.
*   Basic admin login.
*   Sidebar navigation (expands on hover).
*   Real-time clock display.

## Prerequisites

*   **XAMPP:** Or a similar web server stack (WAMP, MAMP, LAMP) that includes:
    *   Apache Web Server
    *   PHP (Version 7.4 or higher recommended)
*   **PostgreSQL:** Database Server (Version 9.6 or higher recommended). XAMPP typically bundles MySQL/MariaDB, so you might need to install PostgreSQL separately if it's not included in your XAMPP version or if you prefer a separate installation.
*   **Web Browser:** Chrome, Firefox, Safari, Edge, etc.
*   **(Optional) Git:** For cloning the repository.

## Setup Instructions

1.  **Clone or Download:**
    *   **Git:** `git clone <repository_url>`
    *   **Download:** Download the ZIP file and extract it.

2.  **Place Project Files:**
    *   Move the entire project folder (`frontend`) into your XAMPP `htdocs` directory.
        *   Example Windows: `C:\xampp\htdocs\highway-toll-dashboard`
        *   Example macOS: `/Applications/XAMPP/htdocs/highway-toll-dashboard`
        *   Example Linux: `/opt/lampp/htdocs/highway-toll-dashboard`

3.  **Database Setup (PostgreSQL):**
    *   Ensure your PostgreSQL server is running. You can start it via `pgAdmin`, command line, or system services.
    *   Open `psql` or a GUI tool like `pgAdmin`.
    *   **Create Database:** Create a new database. The default name used in the config is `toll_booth`.
        ```sql
        CREATE DATABASE toll_booth;
        ```
    *   **Connect to Database:** Connect to the newly created database (`\c toll_booth` in psql).
    *   **Create Tables:** Execute the SQL commands from the `schema.sql` file included in this project. This will create the `vehicle`, `vehicle_sub_type`, and `highway_toll_data` tables.
        ```bash
        # Example using psql command line:
        psql -U your_postgres_user -d toll_booth -f path/to/highway-toll-dashboard/schema.sql
        ```
    *   **(Optional but Recommended) Import Sample Data:** Execute the SQL commands from `sample_data.sql` to populate the database with test data.
        ```bash
        # Example using psql command line:
        psql -U your_postgres_user -d toll_booth -f path/to/highway-toll-dashboard/sample_data.sql
        ```

4.  **Configure Database Connection:**
    *   Open the `db_config.php` file in a text editor.
    *   Locate the following lines and update them with your PostgreSQL connection details:
        ```php
        $host = "localhost"; // Or your PostgreSQL host IP/domain
        $dbname = "toll_booth"; // The database name you created
        $user = "postgres";   // Your PostgreSQL username (default is often postgres)
        $password = "Sufiyan@6346"; // !! CHANGE THIS to your actual PostgreSQL password !!
        ```
    *   **IMPORTANT:** Ensure the password is correct and secure.

5.  **Create Logs Directory:**
    *   Manually create a folder named `logs` inside the `highway-toll-dashboard` project directory (`htdocs/highway-toll-dashboard/logs`).
    *   Ensure your web server (Apache) has **write permissions** to this `logs` directory. Permissions needed might vary based on your OS and setup (e.g., `chmod 755 logs` or `chmod 777 logs` - be mindful of security implications with 777).

6.  **Start XAMPP:**
    *   Open the XAMPP Control Panel.
    *   Start the **Apache** module.
    *   Ensure your **PostgreSQL** service is running (either via XAMPP if it manages it, or separately).

## Solving PostgreSQL Issues in XAMPP

XAMPP, by default, does not come with PostgreSQL, but it can be integrated with PostgreSQL by following these steps:

### Step 1: Install PostgreSQL
1. Download and install PostgreSQL from the official website: [https://www.postgresql.org/download/](https://www.postgresql.org/download/).
2. During installation, ensure that you install pgAdmin (GUI tool) and the required PostgreSQL server.

### Step 2: Configure XAMPP to Use PostgreSQL
1. Open the **php.ini**(production & development) file located in the XAMPP directory (`xampp/php/php.ini`).
2. Search for the line containing `extension=pgsql` and remove the semicolon (`;`) at the beginning of the line to enable the PostgreSQL extension.
    ```ini
    extension=pgsql
    ```
3. Save the file and restart the Apache server from the XAMPP Control Panel.

### Step 3: Verify the PostgreSQL Connection
1. Create a simple PHP script in your `htdocs` folder to verify the PostgreSQL connection.
    ```php
    <?php
    $host = 'localhost';
    $dbname = 'toll_booth';
    $user = 'postgres';
    $password = 'your_password_here';
    
    $connection = pg_connect("host=$host dbname=$dbname user=$user password=$password");
    
    if ($connection) {
        echo "Connected to PostgreSQL!";
    } else {
        echo "Connection failed.";
    }
    ?>
    ```
2. Open the script in your browser: `http://localhost/test_pg_connection.php`. If you see the message **"Connected to PostgreSQL!"**, PostgreSQL is configured correctly.

### Step 4: Install PHP pgSQL Driver (if necessary)
If you still encounter issues, you might need to install the `php-pgsql` extension for your operating system. On Windows, this is usually included with the PostgreSQL installation. If you're on Linux, you can install it with:
```bash
sudo apt-get install php-pgsql
````

After installation, restart Apache and try connecting again.

## Running the Application

1. Open your web browser.
2. Navigate to the login page:

   * `http://localhost/frontend/login.php`
   

## Credentials

* **Admin Username:** `admin`
* **Admin Password:** `password123`

*Note:* The login (`login.php`) currently attempts to authenticate against a hardcoded API endpoint (`https://rst.moodscope.in/api/auth.php`). If this API is not available or doesn't work with these credentials, the login will fail. For local testing without the API, you would need to modify `login.php` to perform a direct database check or use hardcoded credentials for demonstration.

## Notes

* **Error Reporting:** `display_errors` is currently enabled in `main.php` and `db_config.php` for development purposes. **Disable this in a production environment** for security reasons.
* **Media Files:** The `image_path` and `video_path` stored in the database are relative paths. You'll need to create corresponding `images` and `videos` directories (or configure Apache) and place media files there for the modal links to work correctly. The sample data includes placeholder paths like `images/car1.jpg`.
* **Dependencies:** This project uses CDN links for Tailwind CSS and Flatpickr. An internet connection is required for these styles and scripts to load.

