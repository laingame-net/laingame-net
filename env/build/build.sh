#!/bin/bash



cd /laingame

cd backend

composer install --prefer-dist --no-progress --no-suggest

cd frontend

npm install
npm run prod

cp ./dist/*.js ../backend/public_html/js/
cp ./dist/*.css ../backend/public_html/css/

cd ..

lftp $INPUT_HOST -u $INPUT_USER,$INPUT_PASSWORD -e "set ftp:ssl-force $INPUT_FORCESSL; set ssl:verify-certificate false; mirror --reverse --continue --dereference -x ^\.git/$ ./public_html $INPUT_REMOTEDIR; quit"
