<?php
# Database configuration
	defined('DB_HOST') || define('DB_HOST', '');
	defined('DB_USER') || define('DB_USER', '');
	defined('DB_PASS') || define('DB_PASS', '');
	defined('DB_NAME') || define('DB_NAME', '');

# Series Status
	defined('NEW_SERIES') 		|| define('NEW_SERIES'		, 0);
	defined('RETURNING_SERIES') || define('RETURNING_SERIES', 1);
	defined('CANCELED_SERIES') 	|| define('CANCELED_SERIES'	, 2);

# Define application host
	defined('LANGUAGE') || define('LANGUAGE', 'pt_BR');

# Recaptcha configuration
	defined('RECAPTHCA_PUB_KEY')  	|| define('RECAPTHCA_PUB_KEY', '');
	defined('RECAPTHCA_PRIVATE_KEY')|| define('RECAPTHCA_PRIVATE_KEY', '');

# Facebook
    defined('FB_APP_ID')		|| define('FB_APP_ID', '');
    defined('FB_APP_SECRET')	|| define('FB_APP_SECRET', '');