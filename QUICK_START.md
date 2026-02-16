# URBANWEAR - Quick Start Guide (5 Minutes)

## 🎯 In 5 Minutes

### Step 1: Start Backend (1 min)
```bash
cd c:\xampp\htdocs\clothing_project\urbanwear-backend
npm install  # Only first time
npm run dev
```

Expected: `🚀 Server running on port 5000`

### Step 2: Add MongoDB IP Whitelist (2 min)
1. Go to: https://cloud.mongodb.com/v2/
2. Login with: `bhattanant82_db_user`
3. Find your project → Network Access
4. Click "Add IP Address"
5. Enter: `0.0.0.0/0`
6. Click Confirm

### Step 3: Start XAMPP (1 min)
1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Wait for green indicator

### Step 4: Test Frontend (1 min)
1. Open: `http://localhost/login-connected.php`
2. Click "Sign up here"
3. Register: 
   - Name: John Doe
   - Email: john@urbanwear.com
   - Password: Pass@123
4. Should show dashboard ✅

---

## 🚀 Available Pages

| Page | URL | Type | Status |
|------|-----|------|--------|
| Login | `/login-connected.php` | Public | ✅ Ready |
| Signup | `/signup-connected.php` | Public | ✅ Ready |
| User Dashboard | `/user-dashboard.php` | Protected | ✅ Ready |
| Admin Dashboard | `/admin-dashboard.php` | Admin Only | ✅ Ready |
| Logout | `/logout-connected.php` | Protected | ✅ Ready |

---

## 🔧 API Connection

All PHP pages automatically connected to:
- **Backend URL**: `http://localhost:5000`
- **API Prefix**: `/api/v1`
- **Authentication**: JWT token via session

### Made with `api-helper.php`:
```php
// Automatic
require_once 'api-helper.php';

// Creates global $API variable
// Handles all cURL requests
// Manages JWT tokens
// Handles sessions
```

---

## 📊 What's Connected

### Authentication ✅
- Register: PHP → Node.js → MongoDB
- Login: PHP → Node.js → MongoDB
- Session: PHP session storage
- Logout: Clear session

### Orders ✅
- Fetch orders: `/user-dashboard.php`
- Stats: `/admin-dashboard.php`
- Real data from MongoDB

### Products ✅
- Admin can add products
- View all products
- Filter by category
- Stock management

### Users ✅
- Admin can view all users
- Admin can change user roles
- Track user activity

---

## 🧪 Quick Test

### Test User Registration
```bash
curl -X POST http://localhost:5000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@test.com",
    "password": "Test@123"
  }'
```

### Test User Login
```bash
curl -X POST http://localhost:5000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@test.com",
    "password": "Test@123"
  }'
```

### Test Get Products
```bash
curl http://localhost:5000/api/v1/products
```

---

## 📱 Frontend Flow

### For Regular Users
```
login-connected.php 
    ↓
POST /api/v1/auth/login
    ↓
storeAuthToken() [session]
    ↓
user-dashboard.php (with $token)
    ↓
Fetch /api/v1/orders with $token
    ↓
Display user data + orders
```

### For Admins
```
login-connected.php 
    ↓
POST /api/v1/auth/login (with admin role)
    ↓
storeAuthToken() [session]
    ↓
admin-dashboard.php (with $token)
    ↓
Fetch /api/v1/admin/dashboard with $token
    ↓
Display stats + manage users/products/orders
```

---

## 🔑 Default Test Account

After signup, you automatically have:
- **Role**: `user` (regular user)
- **Token**: Stored in `$_SESSION['auth_token']`
- **Permissions**: View own orders, update profile

To create admin:
1. Manually update user role in MongoDB
2. Or use admin panel if you're already admin

---

## 📁 File Structure

```
clothing_project/
├── api-helper.php              ← PHP API client (include in all pages)
├── login-connected.php         ← Login page
├── signup-connected.php        ← Registration page
├── user-dashboard.php          ← User orders & profile
├── admin-dashboard.php         ← Admin panel
├── logout-connected.php        ← Logout handler
├── INTEGRATION_GUIDE.md        ← Full documentation
├── QUICK_START.md              ← This file
└── urbanwear-backend/
    ├── server.js               ← Express server
    ├── package.json            ← Dependencies
    ├── .env                    ← MongoDB URI
    └── src/
        ├── controllers/        ← Business logic
        ├── models/             ← Database schemas
        ├── routes/             ← API endpoints
        └── middlewares/        ← Auth, error handling
```

---

## ⚡ Common Commands

### Start Backend
```bash
cd urbanwear-backend
npm run dev
```

### Stop Backend
```
Ctrl + C
```

### Run Tests
```bash
npm test
```

### Check MongoDB Connection
```bash
node test-mongodb.js
```

### View Backend Logs
```
Check terminal where npm run dev is running
```

---

## ✅ Features Working

- [x] User registration with password hashing
- [x] User login with JWT tokens
- [x] Session-based authentication in PHP
- [x] Role-based access control (user/admin)
- [x] Product CRUD (admin only)
- [x] Order creation and tracking
- [x] User dashboard with orders
- [x] Admin dashboard with statistics
- [x] Protected routes
- [x] Auto-redirect based on role
- [x] Logout functionality
- [x] Error handling
- [x] Real MongoDB integration

---

## ❌ TODO / Not Yet Implemented

- [ ] Product upload with images
- [ ] Payment gateway integration
- [ ] Email notifications
- [ ] User profile picture upload
- [ ] Reviews and ratings
- [ ] Wishlist feature
- [ ] Advanced search/filters
- [ ] Two-factor authentication
- [ ] Admin analytics charts
- [ ] Bulk product import

---

## 🆘 Emergency Troubleshooting

### Backend won't start
```bash
# Check Node.js installed
node --version

# Check MongoDB URI in .env
cat .env

# Clear node_modules
rm -r node_modules
npm install
npm run dev
```

### Frontend shows 404
```
Make sure XAMPP Apache is started
Check URL: http://localhost (no port)
Not http://localhost:80 or http://127.0.0.1
```

### Login keeps failing
```
1. Check MongoDB Atlas IP whitelist
2. Check MONGO_URI in .env
3. Run: node test-mongodb.js
4. Check browser console for errors
```

### Token expired errors
```
Just login again - tokens expire after 7 days
Session will be cleared automatically
```

---

**Status**: ✅ Ready to Use  
**Last Updated**: 2024  
**Questions?** Check INTEGRATION_GUIDE.md
