FROM node:25.2.1-alpine

RUN npm install -g npm@11.6.2

WORKDIR /app

EXPOSE 5173

CMD ["sh", "-c", "npm install --legacy-peer-deps && npm run dev -- --host 0.0.0.0"]