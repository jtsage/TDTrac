### Installation Requirements:
 * MySQL 4.0+
 * PHP 5.x+
 * Apache or similar webserver
 * Approx 2 MB of space for web files
 * A shared or unique MySQL database

### Installation Instructions:
 * Download lastest source package, or from the master branch of the GIT repository
 * Unzip the file to a folder on your web server
 * Copy "config.dist.php" to "config.php" world writeable (CHMOD 666)
 * Either create a new MySQL database for TDTrac's data, or plan on using a prefix and an existing database
 * Run "install.php" from a webserver - all server and host setting are configurable from your browser window
 * After installation, be sure to change the admin password (which is 'password' by default), and set config.php to only readable (CHMOD 444)

### Other Defaults you may wish to change:
 * Notifications are off by default.  Turn them on if you wish to use them using the user editor
 * By default, no-one has permissions to do anything (save the administrator can edit permissions) - use the permission editor to change this.

