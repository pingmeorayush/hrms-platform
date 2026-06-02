FROM node:22-alpine

ARG UID=1000
ARG GID=1000

ENV HOME=/tmp/npm-home \
    NPM_CONFIG_CACHE=/tmp/npm-cache

RUN mkdir -p /usr/src/app/node_modules /tmp/npm-cache /tmp/npm-home \
    && chown -R "${UID}:${GID}" /usr/src/app /tmp/npm-cache /tmp/npm-home

WORKDIR /usr/src/app

COPY package.json package-lock.json ./
RUN npm install \
    && chown -R "${UID}:${GID}" /usr/src/app /tmp/npm-cache /tmp/npm-home

COPY docker/entrypoint.sh /usr/local/bin/web-entrypoint
RUN chmod +x /usr/local/bin/web-entrypoint

USER ${UID}:${GID}

ENTRYPOINT ["web-entrypoint"]
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173"]
