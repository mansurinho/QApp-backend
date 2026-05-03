# QApp Backend

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
