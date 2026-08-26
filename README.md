# ChatTayo App

## App Description

ChatTayo is a real-time messaging app that runs in a browser. Users can sign in using their Google account (via Google OAuth) or by creating a standard account. They can find and message other users, manage their account information, messages, and conversations. The app also includes a forgot password feature. The tech stack used is Laravel, Vue.js, and Tailwind CSS.

## Installation
1. Clone the repository or download the zip file and extract it
2. Open the project in VS Code (or another code editor)
3. Install Node dependencies
    - Change directory to frontend folder
    - Run: `npm install`
4. Install PHP dependencies
    - Change directory to backend folder
    - Run: `composer install`
5. Environment Setup

    **Backend:**
    - Copy `backend/.env.example` to `backend/.env`
    - Configure:
      - Google OAuth credentials (GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT)
      - Reverb credentials (REVERB_APP_ID, REVERB_APP_KEY, REVERB_APP_SECRET, REVERB_PORT)
      - Brevo API credentials (BREVO_API_KEY, MAIL_FROM_ADDRESS, MAIL_FROM_NAME)

    Note: 
      - Google OAuth: Create OAuth credentials in Google Cloud Console and configure the authorized redirect URI.
      - Reverb: Configure the Reverb credentials according to your Laravel Reverb setup.
      - Brevo: Create an API key and verify the sender email.

    **Frontend:**
    - Copy `frontend/.env.example` to `frontend/.env`
    - Set the following values:
      - `VITE_API_BASE_URL=http://localhost:8000`
      - `VITE_REVERB_APP_KEY=your_reverb_app_key`

    Note: `VITE_REVERB_APP_KEY` must match `REVERB_APP_KEY` in backend `.env`

6. Generate application key: `php artisan key:generate`
7. Run database migration: `php artisan migrate` (make sure XAMPP or similar is running)

## Usage

Run these three commands in separate terminals:

- Frontend (inside `frontend`): `npm run dev`
- Backend (inside `backend`): `php artisan serve`
- Reverb (inside `backend`): `php artisan reverb:start`

