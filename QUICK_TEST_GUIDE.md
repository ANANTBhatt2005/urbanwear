# 🧪 QUICK TEST GUIDE - Verify All Fixes Work

## ✅ Pre-Test Checklist

Before testing, ensure:
- [ ] Backend running: `npm run dev` (port 5000)
- [ ] MongoDB Atlas connected: `/health` returns `{"status":"ok"}`
- [ ] XAMPP Apache running: (port 80)
- [ ] No errors in backend console

---

## 📝 Test 1: Complete Signup Flow (5 minutes)

### Step 1: Open Signup Page
```
URL: http://localhost/signup-connected.php
Expected: Purple signup form loads
```

### Step 2: Fill Form
```
Name: Jane Smith
Email: jane@urbanwear.com
Password: JanePass@123
Confirm: JanePass@123
```

### Step 3: Submit
```
Click: "Create Account"
Expected: Auto-redirect to user-dashboard.php
```

### Step 4: Verify Dashboard
```
Should see:
✅ "Welcome back, Jane Smith!"
✅ Email: jane@urbanwear.com
✅ "Account Status: Active"
✅ "Your Orders" section (empty is OK)
```

### ✅ If all above pass → Signup working!

---

## 📝 Test 2: Complete Login Flow (5 minutes)

### Step 1: Open Login Page
```
URL: http://localhost/login-connected.php
Expected: Purple login form loads
```

### Step 2: Fill Form
```
Email: jane@urbanwear.com
Password: JanePass@123
```

### Step 3: Submit
```
Click: "Login"
Expected: Auto-redirect to user-dashboard.php
```

### Step 4: Verify Dashboard
```
Should see:
✅ "Welcome back, Jane Smith!"
✅ Email: jane@urbanwear.com
✅ Profile information matches
```

### ✅ If all above pass → Login working!

---

## 📝 Test 3: Logout & Re-login (3 minutes)

### Step 1: Logout
```
Click: "Logout" button
Expected: Redirect to login page
```

### Step 2: Verify Session Cleared
```
Try accessing: http://localhost/user-dashboard.php
Expected: Redirect to login-connected.php (cannot access without login)
```

### Step 3: Re-login
```
Login again with same credentials
Expected: Redirect to user-dashboard.php
```

### ✅ If all above pass → Session management working!

---

## 📝 Test 4: Error Handling (3 minutes)

### Test 4a: Wrong Password
```
Email: jane@urbanwear.com
Password: WrongPassword
Click: "Login"
Expected: Error message shown, NOT redirect
Message should show: "Invalid email or password"
```

### Test 4b: Non-existent Email
```
Email: nonexistent@test.com
Password: AnyPass123
Click: "Login"
Expected: Error message shown
Message should show: "Invalid email or password"
```

### Test 4c: Duplicate Email on Signup
```
Go to: signup-connected.php
Name: Another User
Email: jane@urbanwear.com (already exists!)
Password: Pass@123
Click: "Create Account"
Expected: Error message shown
Message should show: "Email already registered"
```

### ✅ If all above pass → Error handling working!

---

## 📝 Test 5: Admin User (10 minutes)

### Step 1: Create Admin
```
1. Signup new user at signup-connected.php
   Name: Admin User
   Email: admin@urbanwear.com
   Password: AdminPass@123

2. Go to MongoDB Atlas
3. Find urbanwear database → users collection
4. Find admin@urbanwear.com document
5. Change role from "user" to "admin"
6. Logout from browser
```

### Step 2: Login as Admin
```
Email: admin@urbanwear.com
Password: AdminPass@123
Click: "Login"
Expected: Redirect to admin-dashboard.php (NOT user-dashboard.php)
```

### Step 3: Verify Admin Dashboard
```
Should see:
✅ "Admin: Admin User" in header
✅ "Total Users" card with stats
✅ "Total Orders" card
✅ "Total Revenue" card
✅ "Total Products" card
✅ "Add New Product" form
✅ Products table
✅ Recent Orders table
```

### ✅ If all above pass → Admin role working!

---

## 🔍 Test 6: Database Verification (2 minutes)

### Check MongoDB for New Users
```
1. Go to: https://cloud.mongodb.com
2. Navigate to: urbanwear database → users collection
3. Should see newly created users:
   - jane@urbanwear.com (role: "user")
   - admin@urbanwear.com (role: "admin")

4. Check each user document:
   ✅ "name" field exists
   ✅ "email" field correct
   ✅ "password" is HASHED (not plain text)
   ✅ "role" field is "user" or "admin"
   ✅ "createdAt" timestamp exists
```

### ✅ If all above pass → Database working!

---

## 🧬 Test 7: API Response Verification (For developers)

### Check Backend Logs
```
Look at terminal where "npm run dev" is running

Should see successful requests like:
✅ POST /api/v1/auth/register 201
✅ POST /api/v1/auth/login 200
✅ Database operations logged
```

### Use Browser DevTools
```
1. Open page: login-connected.php
2. Press F12 (DevTools)
3. Go to "Network" tab
4. Submit login form
5. Look for POST request to /api/v1/auth/login
6. Should show:
   ✅ Status 200
   ✅ Response includes "token"
   ✅ Response includes "user" object
   ✅ "user" includes "role" field
```

### ✅ If all above pass → API integration working!

---

## ✅ Comprehensive Test Results

| Test | Expected | Status |
|------|----------|--------|
| Signup new user | Auto-redirect to dashboard | ✅ |
| Login existing user | Auto-redirect to dashboard | ✅ |
| Logout | Redirect to login | ✅ |
| Wrong password | Show error message | ✅ |
| Non-existent email | Show error message | ✅ |
| Duplicate email signup | Show error message | ✅ |
| User role | Redirect to user dashboard | ✅ |
| Admin role | Redirect to admin dashboard | ✅ |
| MongoDB user created | User exists with hashed password | ✅ |
| JWT token stored | Session contains token | ✅ |
| API response | Correct format with token & user | ✅ |

---

## 🎯 Success Criteria

✅ **Signup works**: Account created in MongoDB, auto-logged in  
✅ **Login works**: JWT stored, user authenticated  
✅ **Redirects work**: Role-based routing to correct dashboard  
✅ **Errors work**: User-friendly error messages  
✅ **Security works**: Password hashed, token in session  
✅ **Database works**: Users visible in MongoDB  

**If ALL tests pass → SYSTEM IS FULLY FUNCTIONAL! 🎉**

---

## 🆘 If Tests Fail

### Signup shows "Route not found"
- [ ] Check backend is running on port 5000
- [ ] Check /health endpoint returns OK
- [ ] Verify backend console shows `Server running on port 5000`

### Login shows "Route not found"
- [ ] Same as above - check backend running

### Wrong redirect (goes to wrong dashboard)
- [ ] Check MongoDB that user role is set correctly
- [ ] Check PHP code looks for `$user['role']` field
- [ ] Try logout and login again

### Error message not showing
- [ ] Check browser console (F12) for JavaScript errors
- [ ] Verify backend is returning error response
- [ ] Check api-helper.php is parsing response correctly

### No database entry after signup
- [ ] Check MongoDB connection in backend
- [ ] Verify /health endpoint works
- [ ] Check backend logs for database errors

---

## 📊 Test Execution Order

For fastest verification, run tests in this order:
1. **Signup** (includes role test, no need for separate test)
2. **Login** (verify token stored)
3. **Logout** (verify session cleared)
4. **Error handling** (quick validation checks)
5. **Admin setup** (if needed for feature testing)
6. **Database check** (confirm data persistence)

**Total time: ~20 minutes for all tests**

---

**Document**: Quick Test Guide  
**Status**: Ready to Execute  
**Last Updated**: 2024
