
# cofedock.azurecr.io/laingame-db

# Derived from official mysql image (our base image)
FROM mysql:5.7

# config database auth
ENV MYSQL_DATABASE laingame
ENV MYSQL_USER lain
ENV MYSQL_PASSWORD p4ssp4ss
ENV MYSQL_ROOT_PASSWORD p4ssp4ss

# Add the content of the sql-scripts/ directory to your image
COPY ../../../laingame.sql /docker-entrypoint-initdb.d/