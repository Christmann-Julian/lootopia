FROM node:25.2.1-alpine

# Installation de npm à la bonne version
RUN npm install -g npm@11.6.2

WORKDIR /app

EXPOSE 5173

# Démarrage du serveur de développement Vite
CMD ["sh", "-c", "npm install && npm run dev -- --host 0.0.0.0"]