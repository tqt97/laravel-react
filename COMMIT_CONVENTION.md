# Quy Ước Git Commit

Tài liệu này định nghĩa quy tắc viết Git commit message thống nhất cho toàn bộ dự án.

Dự án áp dụng chuẩn **Conventional Commits**.

Mục tiêu:

- Lịch sử Git rõ ràng và dễ đọc.
- Mỗi commit thể hiện chính xác một thay đổi.
- Dễ review code.
- Dễ tìm kiếm và truy vết thay đổi.
- Hỗ trợ tạo changelog tự động.
- Hỗ trợ Semantic Versioning.
- Giúp quá trình revert, cherry-pick và debug thuận tiện hơn.

---

## 1. Cấu Trúc Commit Message

Commit message sử dụng cấu trúc:

```text
<type>(<scope>): <description>
```

Trong đó:

- `type`: Loại thay đổi.
- `scope`: Phạm vi thay đổi, không bắt buộc.
- `description`: Mô tả ngắn gọn thay đổi.

Ví dụ:

```text
feat(auth): add Google authentication
fix(users): prevent duplicate email submission
refactor(booking): extract availability service
test(auth): add login validation tests
docs(setup): document local installation
```

Có thể bỏ `scope` khi phạm vi thay đổi đã rõ:

```text
docs: update README
chore: initialize project
```

---

## 2. Các Loại Commit Được Sử Dụng

### `feat` — Thêm Tính Năng

Sử dụng khi thêm chức năng hoặc behavior mới cho hệ thống.

Ví dụ:

```text
feat(auth): add Google authentication

feat(users): add user management

feat(booking): add reservation cancellation

feat(profile): add avatar upload

feat(search): add full-text search
```

Không sử dụng `feat` cho refactor, sửa bug hoặc thay đổi cấu hình đơn thuần.

---

### `fix` — Sửa Lỗi

Sử dụng khi sửa một behavior không đúng hoặc lỗi trong hệ thống.

Ví dụ:

```text
fix(auth): handle expired sessions

fix(users): prevent duplicate email registration

fix(booking): prevent overlapping reservations

fix(profile): preserve avatar when updating profile

fix(search): escape special query characters
```

Commit nên mô tả **lỗi được sửa**, không nên chỉ ghi chung chung như:

```text
fix: bug
fix: issue
fix: error
```

---

### `refactor` — Tái Cấu Trúc Code

Sử dụng khi thay đổi cấu trúc code nhưng không thêm tính năng mới và không sửa behavior bên ngoài.

Ví dụ:

```text
refactor(auth): extract authentication service

refactor(users): move filtering logic to query object

refactor(booking): extract reservation action

refactor(profile): simplify profile update flow

refactor(audit): replace duplicated mapping logic
```

Refactor không được làm thay đổi behavior hiện tại của hệ thống.

---

### `perf` — Tối Ưu Hiệu Năng

Sử dụng cho những thay đổi nhằm cải thiện performance.

Ví dụ:

```text
perf(users): eliminate N+1 queries

perf(booking): cache availability results

perf(search): add index for search queries

perf(audit): paginate activity logs

perf(api): reduce response serialization overhead
```

Nên ưu tiên mô tả phần được tối ưu thay vì chỉ ghi:

```text
perf: optimize
```

---

### `test` — Thêm Hoặc Chỉnh Sửa Test

Sử dụng khi commit chỉ liên quan đến test.

Ví dụ:

```text
test(auth): add login validation tests

test(users): cover user creation permissions

test(booking): add reservation concurrency tests

test(profile): cover avatar upload validation

test(api): add unauthorized request tests
```

Nếu test được viết cùng với một feature trong cùng logical change thì có thể nằm trong commit `feat`.

Ví dụ:

```text
feat(booking): prevent overlapping reservations
```

Commit trên có thể chứa cả implementation và test tương ứng.

---

### `docs` — Tài Liệu

Sử dụng khi chỉ thay đổi tài liệu.

Ví dụ:

```text
docs: update README

docs(setup): add local installation guide

docs(api): document authentication endpoints

docs(booking): document reservation workflow

docs(deployment): add production deployment guide
```

Các file thường thuộc loại này:

```text
README.md
CONTRIBUTING.md
COMMIT_CONVENTION.md
docs/*
```

---

### `style` — Định Dạng Code

Sử dụng cho thay đổi formatting không ảnh hưởng đến logic.

Ví dụ:

```text
style(php): apply Laravel Pint formatting

style(frontend): apply Prettier formatting

style(users): normalize component formatting

style(css): normalize utility class ordering

style(tests): format Pest test files
```

Không sử dụng `style` cho thay đổi giao diện UI.

Ví dụ thêm màu cho button là thay đổi behavior/UI và có thể thuộc `feat` hoặc `fix`, không phải `style`.

---

### `build` — Build System Và Dependencies

Sử dụng khi thay đổi dependency hoặc hệ thống build.

Ví dụ:

```text
build(frontend): add fontaine dependency

build(composer): update Laravel dependencies

build(vite): configure production assets

build(node): upgrade Node.js requirements

build(frontend): update React dependencies
```

Các thay đổi thường gặp:

```text
composer.json
composer.lock
package.json
package-lock.json
pnpm-lock.yaml
vite.config.ts
```

---

### `ci` — CI/CD

Sử dụng cho thay đổi liên quan đến pipeline, automation hoặc continuous integration/deployment.

Ví dụ:

```text
ci: add GitHub Actions workflow

ci(test): run Pest on pull requests

ci(lint): add frontend lint checks

ci(deploy): add production deployment pipeline

ci(security): add dependency vulnerability scan
```

---

### `chore` — Công Việc Bảo Trì

Sử dụng cho những thay đổi maintenance không thuộc các nhóm phía trên.

Ví dụ:

```text
chore: initialize Laravel React starter kit

chore(tooling): configure development tools

chore(git): update gitignore rules

chore(env): update environment example

chore(cleanup): remove unused development files
```

Không nên lạm dụng `chore` cho mọi thay đổi không biết phân loại.

Hãy kiểm tra các loại khác trước khi sử dụng `chore`.

---

### `revert` — Hoàn Tác Commit

Sử dụng khi hoàn tác một thay đổi trước đó.

Ví dụ:

```text
revert: remove optimistic user deletion

revert(auth): restore previous authentication flow

revert(booking): restore previous availability query

revert(api): restore legacy response structure

revert(frontend): restore previous navigation behavior
```

Nếu có thể, phần body nên tham chiếu commit được revert.

---

## 3. Quy Tắc Viết Description

### Sử Dụng Động Từ Dạng Mệnh Lệnh

Nên viết:

```text
feat(users): add user search
fix(auth): handle expired sessions
refactor(booking): extract availability service
```

Không nên:

```text
feat(users): added user search
fix(auth): fixed expired sessions
```

---

## 4. Viết Description Bằng Chữ Thường

Nên:

```text
feat(users): add role filtering
```

Không nên:

```text
feat(users): Add role filtering
```

---

## 5. Không Thêm Dấu Chấm Cuối Commit

Nên:

```text
fix(auth): handle expired sessions
```

Không nên:

```text
fix(auth): handle expired sessions.
```

---

## 6. Giữ Commit Message Ngắn Gọn

Phần subject nên ngắn gọn, ưu tiên không vượt quá khoảng **72 ký tự**.

Nếu cần giải thích chi tiết, sử dụng commit body.

Ví dụ:

```text
fix(booking): prevent overlapping reservations

Lock the availability record before creating a reservation to prevent
concurrent requests from reserving the same time slot.
```

---

## 7. Một Commit Chỉ Nên Chứa Một Logical Change

Mỗi commit nên đại diện cho một thay đổi logic có thể review độc lập.

Không nên:

```text
feat: add users, fix login, update README and format code
```

Nên tách:

```text
feat(users): add user management

fix(auth): handle expired sessions

docs: update installation guide

style: apply project formatting
```

---

## 8. Không Chia Commit Quá Vụn

Không nên:

```text
feat(users): add button

feat(users): add icon

feat(users): change button text

feat(users): add margin

feat(users): change title
```

Nếu tất cả cùng phục vụ một chức năng:

```text
feat(users): add user creation form
```

Một commit cần đủ nhỏ để review dễ dàng nhưng cũng phải đủ lớn để có ý nghĩa.

---

## 9. Implementation Và Test Nên Đi Cùng Nhau

Khi thêm một feature:

```text
feat(booking): add reservation cancellation
```

Commit nên chứa:

- Implementation.
- Validation liên quan.
- Authorization liên quan.
- Test trực tiếp của feature.

Không cần bắt buộc tách thành:

```text
feat(booking): add reservation cancellation
test(booking): test reservation cancellation
```

`test:` phù hợp hơn khi commit chỉ bổ sung hoặc sửa test.

---

## 10. Không Sử Dụng Commit Message Chung Chung

Không được sử dụng:

```text
update
update files
changes
fix
fix bug
fix issue
fix again
final
final fix
test
test123
wip
done
misc
```

Commit message phải cho người đọc biết **đã thay đổi điều gì** mà không cần mở diff.

---

## 11. Scope

Scope mô tả module hoặc khu vực bị ảnh hưởng.

Ví dụ:

```text
auth
users
roles
permissions
booking
profile
audit
api
database
frontend
vite
composer
deployment
```

Ví dụ hoàn chỉnh:

```text
feat(auth): add Google authentication

fix(booking): prevent overlapping reservations

perf(users): eliminate N+1 queries

refactor(audit): extract activity log mapper
```

Không nên tạo scope quá chi tiết:

```text
feat(user-create-form-submit-button): add loading state
```

Thay vào đó:

```text
feat(users): add form submission loading state
```

---

## 12. Breaking Changes

Nếu thay đổi làm mất backward compatibility, thêm `!`:

```text
feat(api)!: change authentication response format
```

Hoặc mô tả trong body/footer:

```text
feat(api): change authentication response format

BREAKING CHANGE: authentication responses now use the new API envelope.
```

Breaking change cần được review đặc biệt cẩn thận.

---

## 13. Commit Dependency

Khi thêm hoặc cập nhật package:

```text
build(frontend): add fontaine dependency
```

Không nên:

```text
chore: update package.json
```

Commit nên nói **tại sao dependency thay đổi**, không chỉ nói file nào thay đổi.

---

## 14. Commit Migration

Migration phục vụ feature nên nằm cùng logical change nếu hợp lý:

```text
feat(booking): add reservation status
```

Commit có thể chứa:

```text
migration
model
action/service
controller
frontend
test
```

Nếu migration là thay đổi database độc lập:

```text
refactor(database): add index to reservation lookup
```

hoặc:

```text
perf(database): add index for reservation queries
```

---

## 15. Commit Lock Files

Khi thay đổi dependency, phải commit lock file tương ứng.

Composer:

```text
composer.json
composer.lock
```

npm:

```text
package.json
package-lock.json
```

pnpm:

```text
package.json
pnpm-lock.yaml
```

Không sử dụng nhiều package manager trong cùng project nếu không có lý do đặc biệt.

---

## 16. Không Commit File Không Liên Quan

Trước khi commit:

```bash
git status
git diff
git diff --staged
```

Không sử dụng một cách máy móc:

```bash
git add .
```

nếu chưa kiểm tra những file sẽ được commit.

Có thể stage chính xác:

```bash
git add app/
git add tests/
```

hoặc:

```bash
git add -p
```

---

## 17. Commit Phải Giữ Repository Ở Trạng Thái Hợp Lệ

Một commit hoàn chỉnh nên:

- Có mục đích rõ ràng.
- Không chứa debug code.
- Không chứa file tạm.
- Không chứa secret.
- Không chứa `.env`.
- Không chứa code đã comment vô lý.
- Không chứa thay đổi ngoài phạm vi.
- Pass formatter.
- Pass lint.
- Pass relevant tests.
- Có thể review độc lập.

---

## 18. Không Commit Secret

Tuyệt đối không commit:

```text
.env
API keys
access tokens
private keys
database passwords
production credentials
OAuth secrets
AWS credentials
```

Nếu secret đã bị commit, xóa file ở commit sau **không đồng nghĩa secret đã an toàn** vì nó vẫn tồn tại trong Git history.

Secret đó phải được rotate/revoke.

---

## 19. Commit Trước Khi Push

Tùy project, trước khi push nên chạy các quality gate tương ứng.

Laravel:

```bash
php artisan test
```

hoặc:

```bash
./vendor/bin/pest
```

Formatting PHP:

```bash
./vendor/bin/pint
```

Frontend lint:

```bash
npm run lint
```

Production build:

```bash
npm run build
```

TypeScript nếu project có script tương ứng:

```bash
npm run types
```

Không bắt buộc chạy toàn bộ test suite cho mọi commit nhỏ nếu quá tốn thời gian, nhưng phải chạy các test liên quan đến phần thay đổi.

---

## 20. Commit Đầu Tiên Của Project

Với project Laravel React Starter Kit vừa khởi tạo:

```text
chore: initialize Laravel React starter kit
```

Sau đó nên phát triển bằng các commit nhỏ có ý nghĩa:

```text
build(frontend): add fontaine dependency

chore(tooling): configure development tools

docs: add project development guidelines

ci(test): add automated test workflow

feat(auth): customize authentication flow
```

---

## 21. Ví Dụ Lịch Sử Git Tốt

Một lịch sử Git tốt có thể trông như sau:

```text
chore: initialize Laravel React starter kit

build(frontend): add fontaine dependency

chore(tooling): configure code quality tools

docs: add development guidelines

feat(users): add user management

feat(roles): add role management

feat(permissions): add permission management

fix(auth): handle expired sessions

perf(users): eliminate N+1 queries

refactor(booking): extract reservation action

test(booking): add concurrency regression tests

ci(test): run test suite on pull requests
```

Chỉ cần đọc lịch sử này, developer đã có thể hiểu khá rõ project phát triển như thế nào.

---

## 22. Checklist Trước Khi Commit

Trước mỗi commit, kiểm tra:

- [ ] Commit chỉ chứa một logical change.
- [ ] Không có file ngoài phạm vi.
- [ ] Không có secret hoặc `.env`.
- [ ] Không còn debug code.
- [ ] Không còn `console.log`, `dd()`, `dump()` không cần thiết.
- [ ] Code đã được format.
- [ ] Lint đã pass nếu có.
- [ ] Relevant tests đã pass.
- [ ] Build đã pass nếu thay đổi frontend/build.
- [ ] Migration đã được kiểm tra nếu thay đổi database.
- [ ] Commit message đúng convention.
- [ ] Description mô tả chính xác thay đổi.
- [ ] Không sử dụng commit message chung chung.
- [ ] Lock file được cập nhật nếu dependency thay đổi.

---

## 23. Nguyên Tắc Cốt Lõi

> **Một commit = một thay đổi logic có thể hiểu, test, review và revert độc lập.**

Commit không cần nhỏ nhất có thể.

Commit cần **nhỏ vừa đủ để review nhưng hoàn chỉnh về mặt logic**.

Một developer khác khi đọc:

```bash
git log --oneline
```

phải có khả năng hiểu quá trình phát triển của project mà không cần mở từng commit.
