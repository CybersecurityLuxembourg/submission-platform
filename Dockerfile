ARG NODE_VERSION=22.14
FROM node:${NODE_VERSION}-alpine AS base

# Add npm cache mount for faster builds
RUN --mount=type=cache,target=/root/.npm \
    npm config set cache /root/.npm

FROM base AS deps
WORKDIR /opt/medusa/deps
ARG NODE_ENV=production
ENV NODE_ENV=$NODE_ENV

# Copy package files
COPY package*.json ./

# Install dependencies with better error handling
RUN --mount=type=cache,target=/root/.npm \
    npm ci --omit=dev --no-audit --no-fund || \
    (echo "Error: npm ci failed. Ensure package-lock.json exists and is valid." && \
     echo "Files in directory:" && ls -la && \
     echo "Node version: $(node --version)" && \
     echo "npm version: $(npm --version)" && \
     exit 1)

FROM base AS builder
WORKDIR /opt/medusa/build
ARG NODE_ENV=production
ENV NODE_ENV=$NODE_ENV

# Copy package files and source code
COPY package*.json ./
COPY . .

# Clean any existing build artifacts
RUN rm -rf .medusa node_modules

# Install all dependencies (including dev deps for build)
RUN --mount=type=cache,target=/root/.npm \
    npm ci --no-audit --no-fund

# Build the application
RUN npm run build

FROM base AS runner

# Install runtime packages
RUN apk add --no-cache tini dumb-init bash \
    && addgroup -g 1001 -S nodejs \
    && adduser -S medusa -u 1001

USER medusa
WORKDIR /opt/medusa

# Copy production dependencies from deps stage
COPY --from=deps --chown=medusa:nodejs /opt/medusa/deps/node_modules ./node_modules

# Copy Medusa v2 build output
COPY --from=builder --chown=medusa:nodejs /opt/medusa/build/.medusa/server ./
COPY --from=builder --chown=medusa:nodejs /opt/medusa/build/src ./src
COPY --from=builder --chown=medusa:nodejs /opt/medusa/build/medusa-config.ts ./

# Copy and set permissions for start script
COPY --chown=medusa:nodejs start.sh ./
RUN chmod +x ./start.sh

ARG PORT=9000
ARG NODE_ENV=production
ENV PORT=$PORT
ENV NODE_ENV=$NODE_ENV

EXPOSE $PORT

# Health check
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://localhost:${PORT}/health || exit 1

ENTRYPOINT ["tini", "--"]
CMD ["./start.sh"]