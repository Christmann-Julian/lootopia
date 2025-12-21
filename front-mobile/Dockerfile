FROM node:25.2.1

# Installation de npm et expo-cli
RUN npm install -g npm@11.6.2 expo-cli

WORKDIR /app

EXPOSE 19000 19001 19002

# Garder le conteneur actif sans démarrer Expo
CMD ["tail", "-f", "/dev/null"]