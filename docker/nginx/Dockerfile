FROM nginx:1.17.1-alpine

RUN apk add --no-cache \
    openssl \
    bash \
    zsh \
    && mkdir -p /etc/ssl/private \
    && chmod -R 777 /etc/ssl/private \
    && openssl req -new -x509 -days 365 -nodes -out /etc/ssl/private/server.pem -keyout /etc/ssl/private/server.key -subj '/C=IT/ST=RER/L=Bologna/O=MADO/OU=IT Department/CN=studiomado.it'
    #echo; echo; echo; echo 'PHP READY'; echo; echo; echo; nginx -g 'daemon off;' && bash"
