Setup Instructions
1. Server Requirements

Element Server Suite Community Edition: Set up using the Helm charts from the repository
Matrix Homeserver: Configure Synapse or Dendrite
Element Call Backend: Set up LiveKit for video calling
Nextcloud Server 31: With PHP 8.1+

2. Installation Steps

1. Clone this repository to your Nextcloud apps directory:

cd /path/to/nextcloud/apps
git clone <repository-url> nextcloud_chat

2. Install dependencies:
cd nextcloud_chat
composer install
npm install

3. Build the frontend:
npm run build

4. Enable the app in Nextcloud:
php occ app:enable nextcloud_chat


Configure Matrix server settings in Nextcloud admin panel

3. Element Integration
The app integrates Element Web by:

Embedding it as an iframe with custom configuration
Using Matrix JS SDK for direct API access
Integrating Element Call as a widget for video calls
Providing seamless SSO between Nextcloud and Matrix

4. Key Features

Secure Messaging: End-to-end encrypted chat using Matrix protocol
Video Calling: High-quality calls with Element Call integration
Federation: Chat with users on other Matrix homeservers
Nextcloud Integration: Deep integration with Nextcloud ecosystem
Modern UI: Vue 3 with Nextcloud design system

This comprehensive implementation provides a solid foundation for replacing Nextcloud Talk with a Matrix-based solution while maintaining the familiar Nextcloud user experience.
