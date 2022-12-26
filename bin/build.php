<?php

passthru('
rm -rf build && mkdir build
cp -r bin src composer.json build/ && rm build/bin/build.php &&
composer install -d build/ --no-interaction --no-progress --prefer-dist --no-dev &&
php -d phar.readonly=off ./phar-composer.phar build build/ &&
php ./rocket.phar --info --no-color
', $code);

exit($code);
