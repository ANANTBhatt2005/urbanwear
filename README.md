# 🛍️ URBANWEAR - Complete E-Commerce Platform

**Production-Ready Backend + PHP Frontend Integration**

---

## 📖 Documentation Index

Start here based on your needs:

### 🚀 **Getting Started (NEW?)**
→ Start with [**QUICK_START.md**](QUICK_START.md)
- 5-minute setup guide
- Copy-paste commands
- Test the flow
- **Read first time!**

### ⚙️ **Exact Commands (LOST?)**
→ Read [**COMMANDS.md**](COMMANDS.md)
- Every command you need
- Terminal-ready copy-paste
- Troubleshooting fixes
- **Use when stuck**

### 📋 **Full Technical Guide**
→ Read [**INTEGRATION_GUIDE.md**](INTEGRATION_GUIDE.md)
- Complete API documentation
- PHP helper functions
- Database models
- Security features
- **Read for deep understanding**

### 🏗️ **System Architecture**
→ Read [**ARCHITECTURE.md**](ARCHITECTURE.md)
- Flow diagrams
- Database relationships
- Authentication lifecycle
- Technology stack
- **Read to understand design**

### ✅ **Verification Checklist**
→ Use [**VERIFICATION_CHECKLIST.md**](VERIFICATION_CHECKLIST.md)
- Step-by-step verification
- Test all components
- Ensure everything works
- **Use before deployment**

### 📊 **Complete Feature Summary**
→ Read [**COMPLETE_SUMMARY.md**](COMPLETE_SUMMARY.md)
- What's been built
- Feature checklist
- File manifest
- Security features
- **Read for overview**

---

## 🎯 Quick Links

| Need | Link | Time |
|------|------|------|
| Get Running | [QUICK_START.md](QUICK_START.md) | 5 min |
| Copy Commands | [COMMANDS.md](COMMANDS.md) | 2 min |
| API Reference | [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md) | 20 min |
| See Architecture | [ARCHITECTURE.md](ARCHITECTURE.md) | 10 min |
| Verify Setup | [VERIFICATION_CHECKLIST.md](VERIFICATION_CHECKLIST.md) | 15 min |
| Full Details | [COMPLETE_SUMMARY.md](COMPLETE_SUMMARY.md) | 30 min |

---

## 🚀 30-Second Start

### 1. MongoDB IP Whitelist (One Time)
```
Go to: https://cloud.mongodb.com
Network Access → Add IP Address → 0.0.0.0/0 → Confirm
```

### 2. Start Backend
```bash
cd urbanwear-backend
npm run dev
```
Expected: `✅ MongoDB connected` + `🚀 Server running on port 5000`

### 3. Start XAMPP Apache
```
Open XAMPP Control Panel → Click "Start" next to Apache
```

### 4. Open Browser
```
http://localhost/login-connected.php
```

**Done!** 🎉 You're live!

---

## 📁 Key Files

### Frontend (PHP)
```
login-connected.php       → User login page
signup-connected.php      → User registration
user-dashboard.php        → User profile & orders
admin-dashboard.php       → Admin control panel
logout-connected.php      → Logout handler
api-helper.php           → API client library
```

### Backend (Node.js/Express)
```
urbanwear-backend/
├── server.js             → Main entry point
├── package.json          → Dependencies
├── .env                  → Configuration
└── src/
    ├── models/           → Database schemas
    ├── controllers/      → Business logic
    ├── routes/           → API endpoints
    └── middlewares/      → Auth, validation
```

---

## ✨ What's Included

### ✅ Authentication & Security
- User registration with password hashing (bcryptjs)
- Login with JWT token generation
- Role-based access control (user/admin)
- Session-based PHP authentication
- Protected API endpoints
- CORS enabled

### ✅ User Features
- User dashboard with profile
- Order history and tracking
- Account settings
- Logout functionality

### ✅ Admin Features
- Admin dashboard with statistics
- Product management (add/edit/delete)
- Order management and status updates
- User management and role assignment
- Real-time data from MongoDB

### ✅ API Features
- RESTful endpoints
- JWT authentication
- Input validation
- Error handling
- Comprehensive logging
- Health check endpoint

### ✅ Database
- MongoDB Atlas (cloud)
- Mongoose ODM
- Data validation
- Auto-timestamps

---

## 🔐 Security Highlights

✅ **Password Security**: bcryptjs hashing with 10 salt rounds  
✅ **Token Security**: JWT with HS256 algorithm, 7-day expiry  
✅ **Session Security**: PHP session with secure cookie handling  
✅ **Access Control**: Role-based middleware validation  
✅ **Data Validation**: Schema and controller-level validation  
✅ **CORS Protection**: Configured for cross-origin safety  

---

## 📊 Technology Stack

| Layer | Technology | Details |
|-------|-----------|---------|
| **Frontend** | PHP 7.4+ | Server-side rendering |
| **Communication** | cURL + JSON | HTTP/API calls |
| **Backend** | Node.js + Express | REST API |
| **Database** | MongoDB Atlas | Cloud-hosted NoSQL |
| **Authentication** | JWT + bcryptjs | Secure tokens |
| **Server** | XAMPP (Apache) | Local development |

---

## 🎯 Common Tasks

### Create New User
1. Go to: `http://localhost/signup-connected.php`
2. Fill form and submit
3. Auto-logged in and redirected to dashboard

### Login User
1. Go to: `http://localhost/login-connected.php`
2. Enter email and password
3. Redirected to dashboard (user) or admin panel (admin)

### Create Admin User
1. Signup normal user
2. Go to MongoDB Atlas
3. Find user in `users` collection
4. Change `role` from `"user"` to `"admin"`
5. Logout and login again

### Add Product (Admin)
1. Login as admin
2. Go to admin dashboard
3. Fill "Add New Product" form
4. Click "Add Product"
5. Appears in product list

### View Orders (User)
1. Login as regular user
2. Go to dashboard
3. Scroll to "Your Orders" section
4. See all user orders from MongoDB

---

## 🌐 URLs Reference

**Frontend Pages:**
```
http://localhost/login-connected.php       → Login page
http://localhost/signup-connected.php      → Signup page
http://localhost/user-dashboard.php        → User dashboard
http://localhost/admin-dashboard.php       → Admin dashboard
http://localhost/logout-connected.php      → Logout
```

**Backend API:**
```
http://localhost:5000/health               → Server health
http://localhost:5000/api/v1/auth/register  → Register (POST)
http://localhost:5000/api/v1/auth/login     → Login (POST)
http://localhost:5000/api/v1/products       → Get products (GET)
http://localhost:5000/api/v1/orders         → Get orders (GET)
http://localhost:5000/api/v1/admin/dashboard→ Admin stats (GET)
```

---

## 🆘 Troubleshooting Quick Links

| Problem | Solution | Link |
|---------|----------|------|
| Backend won't start | Check MongoDB whitelist | [COMMANDS.md](COMMANDS.md#troubleshooting) |
| Can't login | Check email/password | [VERIFICATION_CHECKLIST.md](VERIFICATION_CHECKLIST.md) |
| API not responding | Check ports 5000/80 | [QUICK_START.md](QUICK_START.md#emergency-troubleshooting) |
| Database connection fails | Add IP whitelist | [COMMANDS.md](COMMANDS.md#) |
| Frontend loading error | Check XAMPP status | [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md) |

---

## 📈 Next Steps After Setup

### Immediate
1. ✅ Test signup/login flow
2. ✅ Create test accounts
3. ✅ Verify admin dashboard

### Short Term
- Connect product images (Cloudinary)
- Add payment gateway (Stripe)
- Setup email notifications
- Create search/filter features

### Medium Term
- Deploy backend to cloud
- Setup custom domain
- Enable HTTPS
- Configure production database

### Long Term
- Advanced analytics
- Mobile app
- More payment methods
- Machine learning features

---

## 📞 Quick Support

### Check These First
1. **Backend Logs**: Look at terminal where `npm run dev` runs
2. **Browser Console**: F12 → Console for JavaScript errors
3. **Network Requests**: F12 → Network to see API calls
4. **MongoDB Status**: https://cloud.mongodb.com

### Common Issues
- **Port 5000 in use?** → Change port in `.env` and restart
- **XAMPP won't start?** → Close other apps using port 80
- **Login fails?** → Check MongoDB whitelist
- **API errors?** → Check backend logs in terminal

---

## ✅ Success Indicators

You know it's working when:

```
✅ Backend: "🚀 Server running on port 5000"
✅ Frontend: Login page loads with purple form
✅ Signup: Creates user and auto-redirects to dashboard
✅ Dashboard: Shows your user info and orders
✅ Admin: Shows statistics and management panels
✅ Logout: Redirects to login page
✅ Re-login: Works with same credentials
✅ Database: New users appear in MongoDB
✅ Security: Passwords hashed (not plain text)
✅ Tokens: JWT in session, validated on each request
```

---

## 📚 Learning Resources

- **JWT Tokens**: https://jwt.io (understand JWT format)
- **Express.js**: https://expressjs.com (API framework)
- **MongoDB**: https://docs.mongodb.com (database docs)
- **PHP Sessions**: https://www.php.net/manual/en/book.session.php
- **cURL**: https://curl.se/libcurl/ (HTTP client)

---

## 🎯 Use Cases

### For Development
- Test features locally
- Debug API issues
- Try different user roles
- Experiment with workflows

### For Learning
- Understand full-stack development
- Learn JWT authentication
- See role-based access in action
- Study API design patterns

### For Production (With Modifications)
- Deploy backend to Heroku/Railway/AWS
- Use custom domain with HTTPS
- Connect payment gateway
- Add email service
- Setup monitoring/logging

---

## 📄 File Structure

```
clothing_project/                          ← Main folder (htdocs)
├── README.md                              ← This file
├── QUICK_START.md                         ← 5-minute guide
├── COMMANDS.md                            ← Copy-paste commands
├── INTEGRATION_GUIDE.md                   ← Technical reference
├── ARCHITECTURE.md                        ← System design
├── VERIFICATION_CHECKLIST.md              ← Testing guide
├── COMPLETE_SUMMARY.md                    ← Full feature list
│
├── api-helper.php                         ← PHP API client
├── login-connected.php                    ← Login page
├── signup-connected.php                   ← Signup page
├── user-dashboard.php                     ← User dashboard
├── admin-dashboard.php                    ← Admin panel
├── logout-connected.php                   ← Logout handler
│
└── urbanwear-backend/                     ← Backend folder
    ├── server.js                          ← Entry point
    ├── package.json                       ← Dependencies
    ├── .env                               ← Configuration
    ├── nodemon.json                       ← Dev config
    │
    └── src/
        ├── models/                        ← Database schemas
        ├── controllers/                   ← Business logic
        ├── routes/                        ← API routes
        ├── middlewares/                   ← Auth, validation
        ├── services/                      ← Helper functions
        ├── config/                        ← Configuration
        ├── utils/                         ← Utilities
        ├── types/                         ← Type definitions
        ├── tests/                         ← Test files
        └── jobs/                          ← Background jobs
```

---

## 🎉 You're Ready!

**Everything is set up and ready to use.**

### Next Step: [Start with QUICK_START.md →](QUICK_START.md)

---

**Platform**: URBANWEAR E-Commerce  
**Version**: 1.0.0  
**Status**: ✅ Production Ready  
**Last Updated**: 2024  
**License**: MIT
