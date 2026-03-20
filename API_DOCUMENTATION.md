# 📱 API Documentation - Koperasi Digital

## 🔐 Authentication

API menggunakan Laravel Sanctum untuk authentication.

### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@koperasi.com",
  "password": "password"
}
```

Response:
```json
{
  "success": true,
  "token": "1|abcdef123456...",
  "user": {
    "id": 1,
    "name": "Admin Koperasi",
    "email": "admin@koperasi.com",
    "roles": ["Admin"]
  }
}
```

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

---

## 👥 Members API

### Get All Members
```http
GET /api/members
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "08123456789",
      "status": "active",
      "total_savings": 5000000,
      "total_loans": 10000000
    }
  ]
}
```

### Get Member Detail
```http
GET /api/members/{id}
Authorization: Bearer {token}
```

### Create Member
```http
POST /api/members
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "08123456789",
  "identification_number": "1234567890123456",
  "address": "Jl. Contoh No. 123",
  "occupation": "Karyawan"
}
```

---

## 💰 Savings API

### Get Member Savings
```http
GET /api/members/{member_id}/savings
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "pokok": 1000000,
    "wajib": 2000000,
    "sukarela": 2000000,
    "total": 5000000,
    "history": [
      {
        "id": 1,
        "type": "wajib",
        "amount": 100000,
        "transaction_date": "2024-01-23",
        "description": "Simpanan bulan Januari"
      }
    ]
  }
}
```

### Create Saving
```http
POST /api/savings
Authorization: Bearer {token}
Content-Type: application/json

{
  "member_id": 1,
  "type": "wajib",
  "amount": 100000,
  "transaction_date": "2024-01-23",
  "description": "Optional description"
}
```

---

## 💳 Loans API

### Get Member Loans
```http
GET /api/members/{member_id}/loans
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "active_loan": {
      "id": 1,
      "amount": 10000000,
      "interest_rate": 12,
      "tenor_months": 12,
      "status": "active",
      "remaining_balance": 8500000,
      "next_installment": {
        "installment_number": 3,
        "due_date": "2024-03-23",
        "amount": 916667
      }
    },
    "loan_history": []
  }
}
```

### Create Loan Request
```http
POST /api/loans
Authorization: Bearer {token}
Content-Type: application/json

{
  "member_id": 1,
  "amount": 10000000,
  "interest_rate": 12,
  "tenor_months": 12,
  "notes": "Untuk modal usaha"
}
```

### Approve Loan
```http
POST /api/loans/{loan_id}/approve
Authorization: Bearer {token}
```

### Reject Loan
```http
POST /api/loans/{loan_id}/reject
Authorization: Bearer {token}
Content-Type: application/json

{
  "notes": "Alasan penolakan"
}
```

---

## 📅 Installments API

### Get Installment Schedule
```http
GET /api/loans/{loan_id}/installments
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "installment_number": 1,
      "principal_amount": 833333,
      "interest_amount": 83333,
      "total_amount": 916666,
      "paid_amount": 916666,
      "status": "paid",
      "due_date": "2024-02-23",
      "paid_at": "2024-02-20"
    },
    {
      "id": 2,
      "installment_number": 2,
      "principal_amount": 833333,
      "interest_amount": 83333,
      "total_amount": 916666,
      "paid_amount": 0,
      "status": "pending",
      "due_date": "2024-03-23",
      "paid_at": null
    }
  ]
}
```

### Pay Installment
```http
POST /api/installments/{installment_id}/pay
Authorization: Bearer {token}
Content-Type: application/json

{
  "amount": 916666
}
```

---

## 📊 Dashboard API

### Get Dashboard Stats
```http
GET /api/dashboard
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "total_members": 150,
    "total_savings": 750000000,
    "total_loans": 1500000000,
    "total_installments_due": 125000000,
    "recent_loans": [],
    "overdue_installments": []
  }
}
```

### Get Member Dashboard (for Anggota role)
```http
GET /api/member/dashboard
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "member": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "total_savings": 5000000,
    "savings_breakdown": {
      "pokok": 1000000,
      "wajib": 2000000,
      "sukarela": 2000000
    },
    "active_loan": {
      "amount": 10000000,
      "remaining_balance": 8500000
    },
    "next_installment": {
      "due_date": "2024-03-23",
      "amount": 916666
    }
  }
}
```

---

## 📈 Reports API

### Get Financial Report
```http
GET /api/reports/financial?year=2024&month=1
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "period": {
      "year": 2024,
      "month": 1
    },
    "total_savings": 750000000,
    "total_loans_disbursed": 1500000000,
    "total_installments_paid": 125000000,
    "total_interest_earned": 12500000,
    "outstanding_loans": 1375000000,
    "overdue_amount": 5000000,
    "balance": -625000000
  }
}
```

---

## 🔔 Notifications API (Premium Feature)

### Get Notifications
```http
GET /api/notifications
Authorization: Bearer {token}
```

### Mark as Read
```http
POST /api/notifications/{id}/read
Authorization: Bearer {token}
```

---

## 🔍 Audit Logs API (Admin Only)

### Get Audit Logs
```http
GET /api/audit-logs?page=1&per_page=50
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user": {
        "id": 1,
        "name": "Admin Koperasi"
      },
      "action": "create",
      "model_type": "Member",
      "model_id": 1,
      "description": "Created new member: John Doe",
      "ip_address": "127.0.0.1",
      "created_at": "2024-01-23 10:30:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 50,
    "total": 100
  }
}
```

---

## 🚨 Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "amount": ["The amount must be at least 0."]
  }
}
```

---

## 🔧 Implementation Notes

### TODO: Implement API Controllers

Create `app/Http/Controllers/Api/` folder with:
- `AuthController.php`
- `MemberController.php`
- `SavingController.php`
- `LoanController.php`
- `InstallmentController.php`
- `DashboardController.php`
- `ReportController.php`

### TODO: Add API Routes

In `routes/api.php`:
```php
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::apiResource('members', MemberController::class);
    Route::get('members/{member}/savings', [SavingController::class, 'memberSavings']);
    Route::get('members/{member}/loans', [LoanController::class, 'memberLoans']);
    
    Route::apiResource('savings', SavingController::class);
    Route::apiResource('loans', LoanController::class);
    Route::post('loans/{loan}/approve', [LoanController::class, 'approve']);
    Route::post('loans/{loan}/reject', [LoanController::class, 'reject']);
    
    Route::get('installments', [InstallmentController::class, 'index']);
    Route::post('installments/{installment}/pay', [InstallmentController::class, 'pay']);
    
    Route::get('dashboard', [DashboardController::class, 'dashboard']);
    Route::get('member/dashboard', [DashboardController::class, 'memberDashboard']);
    
    Route::get('reports/financial', [ReportController::class, 'financial']);
});
```

---

## 📱 Mobile App Integration

### Recommended Libraries
- **Axios** for HTTP requests
- **React Query** for data fetching
- **React Native** for mobile app
- **Flutter** alternative

### Example Usage (React Native)
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Login
const login = async (email, password) => {
  const response = await api.post('/login', { email, password });
  const { token } = response.data;
  
  // Store token
  await AsyncStorage.setItem('token', token);
  
  // Set default header
  api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
};

// Get member dashboard
const getDashboard = async () => {
  const response = await api.get('/member/dashboard');
  return response.data.data;
};
```

---

**API Documentation v1.0**
*Last updated: January 23, 2024*
