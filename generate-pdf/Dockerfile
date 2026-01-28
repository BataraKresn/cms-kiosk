FROM node:20-bookworm-slim

ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1 \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome-stable

# Install Chrome and fonts required by Puppeteer
RUN set -eux; \
    apt-get update; \
    apt-get install -y wget gnupg ca-certificates fonts-liberation; \
    wget -q -O /tmp/google-chrome.deb https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb; \
    apt-get install -y /tmp/google-chrome.deb --no-install-recommends || true; \
    apt --fix-broken install -y; \
    rm -f /tmp/google-chrome.deb; \
    rm -rf /var/lib/apt/lists/*

WORKDIR /usr/src/app

COPY package*.json ./

RUN npm install --omit=dev && npm install -g pm2

COPY . .

EXPOSE 3333 3335

CMD ["pm2-runtime", "start", "ecosystem.config.js"]
