# URBANWEAR - Complete Backend-Frontend Integration Guide

## 🚀 Project Overview

This is a **production-ready e-commerce backend** built with:
- **Backend**: Node.js + Express.js (JavaScript, no TypeScript)
- **Database**: MongoDB Atlas (cloud)
- **Authentication**: JWT with role-based access control
- **Frontend**: PHP (XAMPP) integrated via cURL

**Ports:**
- Backend API: `http://localhost:5000`
- Frontend: `http://localhost` (XAMPP default)

---

## 🔧 Backend Setup

### 1. Verify Node.js Dependencies

Your `package.json` includes all required production dependencies:

```json
{
  "express": "^4.18.2",
  "mongoose": "^7.0.0",
  "bcryptjs": "^2.4.3",
  "jsonwebtoken": "^9.0.0",
  "cors": "^2.8.5",
  "morgan": "^1.10.0",
  "dotenv": "^16.0.3"
}
```

**Verify installed:**
```bash
cd c:\xampp\htdocs\clothing_project\urbanwear-backend
npm list
```

### 2. MongoDB Atlas Connection

**Current `.env` Configuration:**
```
NODE_ENV=development
PORT=5000
MONGO_URI=mongodb+srv://bhattanant82_db_user:TTxIlGt29WsrFBbR@urbanwear.g0bcdyv.mongodb.net/urbanwear?retryWrites=true&w=majority
```

**⚠️ CRITICAL STEP - Add IP Whitelist:**
1. Go to: https://cloud.mongodb.com
2. Login with your account
3. Navigate to **Network Access**
4. Click **Add IP Address**
5. Enter `0.0.0.0/0` to allow all IPs (for development)
6. Click **Confirm**

**Verify Connection:**
```bash
node test-mongodb.js
```

### 3. Start Backend Server

```bash
# Option 1: Development (with auto-reload)
npm run dev

# Option 2: Production
npm start

# Expected output:
# ✅ MongoDB connected successfully
# 🚀 Server running on port 5000
# Health check: GET http://localhost:5000/health
```

**Test Health Endpoint:**
```bash
curl http://localhost:5000/health
# Should return: {"status":"ok"}
```

---

## 📁 Backend API Endpoints

### Authentication Routes (`/api/v1/auth`)

#### Register New User
```bash
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "securepass123"
}

Response:
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "user": {
      "_id": "507f1f77bcf86cd799439011",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    }
  }
}
```

#### Login User
```bash
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "securepass123"
}

Response: Same as Register
```

### Product Routes (`/api/products`)

#### Get All Products (Public)
```bash
GET /api/products?category=men&page=1&limit=12

Response:
{
  "success": true,
  "data": {
    "products": [...],
    "total": 120,
    "page": 1,
    "pages": 10
  }
}
```

#### Get Single Product (Public)
```bash
GET /api/products/:productId
```

#### Add Product (Admin Only)
```bash
POST /api/admin/products
Authorization: Bearer <token>
Content-Type: application/json

{
  "title": "Urban T-Shirt",
  "price": 29.99,
  "stock": 100,
  "category": "men",
  "description": "...",
  "sizes": ["XS", "S", "M", "L", "XL"]
}
```

### Order Routes (`/api/orders`)

#### Create Order (Authenticated)
```bash
POST /api/orders
Authorization: Bearer <token>
Content-Type: application/json

{
  "products": [
    {
      "productId": "507f1f77bcf86cd799439011",
      "quantity": 2,
      "price": 29.99
    }
  ],
  "totalAmount": 59.98,
  "shippingAddress": "123 Main St"
}
```

#### Get User Orders
```bash
GET /api/orders
Authorization: Bearer <token>
```

### Admin Routes (`/api/admin`)

#### Get Dashboard Stats
```bash
GET /api/admin/dashboard
Authorization: Bearer <token>

Response:
{
  "totalUsers": 45,
  "totalOrders": 123,
  "totalRevenue": 15000.00,
  "totalProducts": 156
}
```

#### Get All Orders (Admin)
```bash
GET /api/admin/orders
Authorization: Bearer <token>
```

#### Update Order Status
```bash
PUT /api/admin/orders/:orderId
Authorization: Bearer <token>

{
  "orderStatus": "shipped"
}
```

---

## 🌐 Frontend Integration (PHP)

### Step 1: Included Files

All PHP pages require these at the top:

```php
<?php
session_start();
require_once 'api-helper.php';
?>
```

### Step 2: Available Helper Functions

#### Authentication Functions

```php
// Store user session after login
storeAuthToken($token, $user);

// Check if user is logged in
if (isLoggedIn()) { ... }

// Check if user is admin
if (isAdmin()) { ... }

// Require authentication (redirect if not)
requireLogin();

// Require admin access (redirect if not)
requireAdmin();

// Get current logged-in user
$user = getCurrentUser();

// Get JWT token from session
$token = getAuthToken();

// Clear session (logout)
clearAuthSession();
```

#### API Calls

```php
// GET request
$response = $API->get('/api/v1/products', $token);

// POST request
$response = $API->post('/api/v1/auth/login', [
    'email' => 'user@example.com',
    'password' => 'password'
]);

// PUT request
$response = $API->put('/api/v1/orders/123', $data, $token);

// DELETE request
$response = $API->delete('/api/v1/products/123', $token);

// Response format
[
  'success' => true|false,
  'message' => 'Response message',
  'data' => [...],
  'http_code' => 200|400|401|500
]
```

---

## 📄 PHP Pages - Implementation Guide

### 1. Login Page (`login-connected.php`)

**Features:**
- Email/password authentication
- Auto-redirect to dashboard on success
- Role-based redirect (admin vs user)
- Error message display

**Created:** ✅ `/clothing_project/login-connected.php`

**Usage:**
```php
<?php
session_start();
require_once 'api-helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = $API->post('/api/v1/auth/login', [
        'email' => $_POST['email'],
        'password' => $_POST['password']
    ]);
    
    if ($response['success']) {
        storeAuthToken($response['data']['token'], $response['data']['user']);
        header('Location: user-dashboard.php');
    }
}
?>
```

### 2. Signup Page (`signup-connected.php`)

**Features:**
- User registration
- Password validation
- Auto-login after signup
- Email duplicate checking (backend validates)

**Created:** ✅ `/clothing_project/signup-connected.php`

### 3. User Dashboard (`user-dashboard.php`)

**Features:**
- Protected route (requireLogin)
- Display user profile
- Show order history
- Order status tracking
- Account settings

**Created:** ✅ `/clothing_project/user-dashboard.php`

### 4. Admin Dashboard (`admin-dashboard.php`)

**Features:**
- Protected route (requireAdmin)
- Dashboard statistics
- Product management (add, edit, delete)
- Order management
- User management

**Created:** ✅ `/clothing_project/admin-dashboard.php`

### 5. Logout (`logout-connected.php`)

**Features:**
- Clear session
- Redirect to login

**Created:** ✅ `/clothing_project/logout-connected.php`

---

## 🧪 Testing the Integration

### Test 1: Health Check
```bash
# Backend should be running
curl http://localhost:5000/health
# Response: {"status":"ok"}
```

### Test 2: User Registration
```bash
curl -X POST http://localhost:5000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@urbanwear.com",
    "password": "Test@123"
  }'
```

### Test 3: User Login
```bash
curl -X POST http://localhost:5000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@urbanwear.com",
    "password": "Test@123"
  }'
```

### Test 4: Frontend Flow
1. Open `http://localhost/login-connected.php`
2. Click "Sign up here"
3. Register new account
4. Should redirect to user dashboard
5. Click "Logout"
6. Should redirect to login page

---

## 🔐 Security Features Implemented

1. **Password Hashing**
   - bcryptjs with 10 salt rounds
   - Never stored as plain text

2. **JWT Authentication**
   - 7-day token expiry
   - HS256 algorithm
   - Token required for protected endpoints

3. **Role-Based Access Control**
   - `user` - Can view orders, update profile
   - `admin` - Full dashboard, CRUD operations

4. **CORS Protection**
   - Cross-origin requests allowed from localhost
   - Can be restricted in production

5. **Session Management**
   - PHP sessions store JWT token
   - Token validated on each API call
   - Auto-logout on session expiry

---

## 🗄️ Database Models

### User Model
```javascript
{
  name: String,
  email: String (unique),
  password: String (hashed),
  role: String (enum: ['user', 'admin']),
  createdAt: Date,
  updatedAt: Date
}
```

### Product Model
```javascript
{
  title: String,
  price: Number,
  stock: Number,
  category: String (enum: ['men', 'women', 'kids']),
  description: String,
  images: [String],
  sizes: [String],
  createdAt: Date,
  updatedAt: Date
}
```

### Order Model
```javascript
{
  userId: ObjectId (ref: User),
  products: [{
    productId: ObjectId,
    quantity: Number,
    price: Number
  }],
  totalAmount: Number,
  orderStatus: String (enum: ['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
  paymentStatus: String (enum: ['pending', 'completed', 'failed']),
  shippingAddress: String,
  createdAt: Date,
  updatedAt: Date
}
```

---

## 📋 Checklist for Production Deployment

- [ ] MongoDB Atlas IP whitelist configured (0.0.0.0/0 for dev, specific IPs for prod)
- [ ] `.env` file with secure credentials (not committed to git)
- [ ] Backend server tested and running on port 5000
- [ ] Frontend login/signup flow tested
- [ ] Database backups configured
- [ ] HTTPS enabled (required for production)
- [ ] CORS origins restricted to actual domain
- [ ] Rate limiting implemented
- [ ] Logging and monitoring configured
- [ ] Admin panel password reset feature
- [ ] Product images uploaded to CDN (Cloudinary configured)
- [ ] Email notifications for orders

---

## 🆘 Troubleshooting

### MongoDB Connection Errors
```
Error: connect ECONNREFUSED
```
**Solution:** 
1. Verify IP whitelist in MongoDB Atlas
2. Check MongoDB URI in .env
3. Run `node test-mongodb.js`

### CORS Errors
```
Access to XMLHttpRequest blocked by CORS policy
```
**Solution:**
- CORS is already enabled in backend
- Check browser console for specific error
- Verify URL format

### JWT Token Expired
```
401 Unauthorized: Token expired
```
**Solution:**
- User needs to login again
- Frontend redirects to login page automatically

### Admin Access Denied
```
403 Forbidden: Admin access required
```
**Solution:**
- Only admin users can access admin endpoints
- Contact admin to change user role in MongoDB

---

## 📞 Support & Contact

For issues or questions:
1. Check MongoDB Atlas connection
2. Verify .env file configuration
3. Check backend server logs
4. Review error messages in browser console

---

**Version:** 1.0.0  
**Last Updated:** 2024  
**Status:** Production Ready ✅
