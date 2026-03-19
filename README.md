# Full Stack Shopping Cart

## Tech Stack
- **Backend**: Laravel 10, MySQL, Firebase Admin SDK
- **Frontend**: Next.js, Redux Toolkit, RTK Query, Firebase Auth

## Setup

### Backend (Laravel)
```bash
cd cart-api
composer install
cp .env.example .env
php artisan key:generate
# Configure DB in .env
php artisan migrate --seed
php artisan serve
```
Place Firebase service account JSON at `storage/app/firebase-credentials.json`.

### Frontend (Next.js)
```bash
cd cart-frontend
npm install
cp .env.local.example .env.local
# Fill in Firebase config values
npm run dev
```

## Firebase Setup
1. Create project at console.firebase.google.com
2. Enable Authentication → Google Sign-In
3. Add Web App → copy config to `.env.local`
4. Generate service account key → save as `firebase-credentials.json` in Laravel

## API Endpoints
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | /api/products | ✓ | List all products |
| GET | /api/products/:id | ✓ | Get single product |
| GET | /api/cart | ✓ | Get user's cart |
| POST | /api/cart | ✓ | Add item to cart |
| PUT | /api/cart/:id | ✓ | Update item quantity |
| DELETE | /api/cart/:id | ✓ | Remove item |
| POST | /api/cart/batch | ✓ | Sync cart (debounced) |
```