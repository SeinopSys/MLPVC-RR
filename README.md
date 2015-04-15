An automated system for handling requests & reservations, made for MLP-VectorClub by [DJDavid98](http://djdavid98.eu)

## Local setup

In order to get the site up and running locally you'll need to set up a few things in advance. Here's a step-by-step guide on getting everything to work on your local machine:

1. Clone the repository
2. Import `setup/database.sql` to a new, empty database of your choice
3. Copy `setup/conf.php` to `www/conf.php` and fill in the details<br>(you can get the deviantArt client keys for yourself [here](http://www.deviantart.com/developers/register))
	- `DB_HOST`: Database host (most likely `localhost`)
	- `DB_NAME`: Name of the database you imported the SQL file into in the previous step
	- `DB_USER`: Database user that has access to said database
	- `DB_PASS`: Password for the user
	- `DA_CLIENT`: deviantArt application's `client_id`
	- `DA_SECRET`: deviantArt application's `client_secret`
	- `EP_DL_SITE`: Absolute URL of the website where episode downloads can be found<br>(not going to make that one public for obvious reasons)
	- `GA_TRACKING_CODE`: Google Analytics tracking code
4. Configure your server
    1. If you're using NGINX, you can see a sample configuration in `setup/nginx.conf`. Edit this file as described below, move it to `/etc/nginx/sites-available` (you should rename it, but you don't have to) and create a symlink in `/etc/nginx/sites-enabled` to enable the configuration. *Don't forget to run `service nginx restart`*
        - Change `server_name` to your domain or use `localhost`
        - Set the `root` to the `www` directory of this repository on your machine
        - Optionally, change the `listen` port if needed
        - Finally, in the last location block, make sure that the `php5-fpm` configuration matches your server setup, if not you'll have to edit that as well.
    2. If you're using Apache, then you'll have to use the file located at `setup/.htaccess`, which only contains the `mod_rewrite` directives neccessary for the site to function. Any other configuration options should be changed through the Apache main configuration file(s).
5. Type your domain (or `localhost`) into the address bar of your favourite browser and the home page should appear

The only parts of the server config that are crucial and must be kept intact are the rewrites which make readable & pretty URLs without the `.php` extension possible.