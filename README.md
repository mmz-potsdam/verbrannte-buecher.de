Front-end for verbrannte-buecher.de
===================================

License
-------
    Code for the Front-end of verbrannte-buecher.de

    (C) 2023 Moses Mendelssohn Center for European-Jewish Studies
        Daniel Burckhardt


    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    You may run your copy of the code under the logos of GHIS/GHDI.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

Third Party Code
----------------
This projects builds on numerous third-party projects under a variety of
Open Source Licenses. Please check `composer.json` for these dependencies.

The XSLT-Stylesheets are based on the files from
    https://github.com/haoess/dta-tools/tree/master/stylesheets

Installation
------------
Requirements

- PHP 7.3 or 7.4 (check with `php -v`)
- composer (check with `composer -v`; if it is missing, see https://getcomposer.org/)

Adjust Local Settings

- vi .env.local (not commited)

Directory Permissions for cache and logs

- sudo setfacl -R -m u:www-data:rwX ./var
- sudo setfacl -dR -m u:www-data:rwX ./var

Generate `public/css/base.css` and `public/css/print.css`

- ./bin/console scss:compile

Development Notes
-----------------

### Local Web Server

- symfony server:start
- Listening on https://127.0.0.1:8000

### Translate templates

    ./bin/console translation:extract --force de
