# Git Ignore Check — Bắt buộc kiểm tra trước khi commit

## Quy tắc bắt buộc

Trước khi commit code, **PHẢI kiểm tra**:

1. **Không commit file trong .gitignore** — đặc biệt:
   - `.env`, `.env.local`, `.env.production` (secrets)
   - `protected/config/params.php`, `protected/config/database.php` (credentials)
   - `node_modules/`, `vendor/` (dependencies)
   - `protected/runtime/` (logs, cache)
   - `.claude/settings.local.json`, `.claude/CLAUDE.local.md` (local settings)

2. **Không commit file nhạy cảm** ngay cả khi chưa có trong .gitignore:
   - API keys, passwords, tokens
   - Database credentials
   - Private keys, certificates

## Kiểm tra trước khi commit

```bash
# Xem danh sách file staged
git diff --cached --name-only

# Kiểm tra file có bị ignore không
git check-ignore -v <file>

# Nếu file nhạy cảm đã được staged, unstage nó
git reset HEAD <file>
```

## Các file KHÔNG BAO GIỜ được commit

| File/Pattern | Lý do |
|--------------|-------|
| `.env*` | Chứa secrets, API keys |
| `protected/config/params.php` | Chứa API key, Portal URL |
| `protected/config/database.php` | Chứa DB credentials |
| `**/credentials*.json` | Credentials |
| `*.pem`, `*.key` | Private keys |
| `node_modules/` | Dependencies (dùng package.json) |
| `vendor/` | PHP dependencies (dùng composer.json) |

## Nếu vô tình commit file nhạy cảm

1. **Xóa khỏi git history** (nếu chưa push):
```bash
git reset --soft HEAD~1
git reset HEAD <file>
```

2. **Nếu đã push** — cần rotate credentials ngay lập tức!
