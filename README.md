# torrentapi

A private torrent database API for caching and web development projects.

## About

This is an easy to set up torrent API for caching torrent files. It currently supports:  
- Authentication
- Upload
- Download
- Torrent file meta

## Installation

#### Setup with authentication (the default)
1. Create a database and add the details to the `src/Endpoint.php` file
2. Run the SQL listed below:
    ```mysql
    CREATE TABLE users
    (
        id BIGINT(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
        username VARCHAR(150) NOT NULL,
        api_key VARCHAR(300) NOT NULL,
        is_enabled TINYINT(1) DEFAULT '0' NOT NULL
    );
    CREATE UNIQUE INDEX users_api_key_uindex ON users (api_key);
    CREATE UNIQUE INDEX users_id_uindex ON users (id);
    CREATE UNIQUE INDEX users_username_uindex ON users (username);
    ```
3. Continue onto `Setting up the server`

#### Set up without authentication
1. In `src/App.php` set the constant `MUST_VALIDATE` to `false`
2. Continue onto `Setting up the server`

#### Setting up the server
1. Set up your web root to point to the `public/` directory
2. Browse to your URL (e.g. `http://localhost/`)

## Example endpoints

__`/`__  
__`/?id={INFO_HASH}`__  
__`/?id={INFO_HASH}&mode=d`__  
__`/?id={INFO_HASH}&mode=download`__  
These will all download the requested torrent file (if found). This is the default action

__`/?id={INFO_HASH}&mode=i`__  
__`/?id={INFO_HASH}&mode=info`__  
This will give information on the requested torrent file (if found). Returned info will include:
- info_hash
- title (if available)
- files (if available)

__`/?mode=u`__  
__`/?mode=upload`__  
By making a call to these (or to `/` with a POST value of `mode=u/upload`) it will begin the upload process.  
Please note that for this, a torrent file must be provided using the `$_FILES` parameter `torrent_file`.
