# Under Construction - feel free to contribute 

# Nextcloud Chat

A Matrix-based chat and video calling application for Nextcloud, powered by Element Web and Element Call.

## Features

- **Secure Messaging**: End-to-end encrypted chat using the Matrix protocol
- **Video Calls**: High-quality video calls with Element Call integration
- **Federation**: Chat with users on other Matrix homeservers
- **Modern UI**: Built with Vue 3 and Nextcloud design system
- **Nextcloud Integration**: Seamless integration with Nextcloud ecosystem

## Requirements

- Nextcloud Server 31+
- PHP 8.1+
- Node.js 18+ (for building)
- Matrix Homeserver (Synapse, Dendrite, etc.)
- Element Server Suite Community Edition
- Element Call instance

## Installation

### 1. Install the App

```bash
# Clone to your Nextcloud apps directory
cd /path/to/nextcloud/apps
git clone <repository-url> nextcloud_chat

# Install dependencies and build
cd nextcloud_chat
composer install --no-dev
npm ci
npm run build

# Enable the app
php occ app:enable nextcloud_chat
