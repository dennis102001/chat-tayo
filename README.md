# ChatTayo App

## App Description

ChatTayo is a real-time messaging app that runs in browsers. Users can sign in using their Google account (via Google OAuth) or by creating a standard account. They can find and message other users, manage their account information, messages, and conversations. The app also includes a forgot password feature. It was developed using Laravel and Vue.js.

## Installation

1. Clone the repository or download the zip file and extract it
2. Open the project in VS Code (or anything alike)
3. Install Node dependencies
    - Change directory to frontend folder
    - Run: `npm install`
4. Install PHP dependencies
    - Change directory to backend folder
    - Run: `composer install`
5. Run database migration `php artisan migrate` (make sure xampp server (or anything alike) is running)

## Environment Setup

- Copy `.env.example` to `.env`
- Add your:
  - Mailer credentials
  - Google OAuth keys

## Usage
Run these three commands in separate terminals
- `npm run dev`
- `php artisan serve`
- `php artisan reverb:start`

