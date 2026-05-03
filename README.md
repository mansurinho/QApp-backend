# QApp Backend

## Setup Instructions

### Step 1 — Database
1. Open phpMyAdmin
2. Click the SQL tab
3. Paste the contents of `database.sql`
4. Click Go

### Step 2 — OpenAI API Key
Replace `sk-your-openai-key-here` in these two files:
- `api/fit/analyze.php`
- `api/ai/chat.php`
- `scripts/seed_universities.php`

### Step 3 — Seed Universities
Open in browser: `http://localhost/qapp-backend/scripts/seed_universities.php`
This fills your database with AI-generated university data. Run once only.

### Step 4 — Done
Tell your frontend developer these URLs:

---

## API Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| POST | /api/auth/register.php | Register new student |
| POST | /api/auth/login.php | Login |
| GET  | /api/student/profile.php?user_id=1 | Get student profile |
| POST | /api/student/profile.php | Update student profile |
| GET  | /api/student/documents.php?user_id=1 | Get documents |
| POST | /api/student/documents.php | Update document status |
| GET  | /api/universities/index.php | Get all universities |
| GET  | /api/universities/single.php?id=1 | Get one university |
| POST | /api/fit/analyze.php | Get AI fit score |
| POST | /api/ai/chat.php | Ask AI advisor |

---

## Folder Structure

```
qapp-backend/
├── database.sql              ← run this first in phpMyAdmin
├── .htaccess                 ← CORS settings
├── config/
│   └── db.php                ← database connection
├── api/
│   ├── auth/
│   │   ├── register.php
│   │   └── login.php
│   ├── student/
│   │   ├── profile.php
│   │   └── documents.php
│   ├── universities/
│   │   ├── index.php
│   │   └── single.php
│   ├── fit/
│   │   └── analyze.php       ← uses OpenAI
│   └── ai/
│       └── chat.php          ← uses OpenAI
└── scripts/
    └── seed_universities.php ← run once to fill DB
```
