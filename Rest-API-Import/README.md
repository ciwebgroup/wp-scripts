# WordPress Content Imports

## rest-import.php

Allows you to use wget to extract desired content from the hosting WordPress site, even if you do not have access.

- `cd /path/to/wp-content/`
- `wget -O ./import.json "https://www.domain.com/wp-json/wp/v2/pages?per_page=100&page=1"`
- `php ./rest-import.php`