FROM node:12-alpine

# Remove default user to avoid UID conflict with the user created next
RUN deluser --remove-home node

# Create non-root user for container
ARG HOST_USER_UID
ARG HOST_USER_GID
ENV HOST_USER_UID=${HOST_USER_UID}
ENV HOST_USER_GID=${HOST_USER_GID}
RUN addgroup -S -g ${HOST_USER_GID} nonroot \
    && adduser -S -s /bin/sh -u ${HOST_USER_UID} -G nonroot -D nonroot
USER nonroot

# Change the cache path for npm and Yarn to be able to set volumes for them
RUN mkdir /tmp/npm-cache && npm config set cache /tmp/npm-cache
RUN mkdir /tmp/yarn-cache && yarn config set cache-folder /tmp/yarn-cache

WORKDIR /app
