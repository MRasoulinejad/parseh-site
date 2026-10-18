<?php
declare(strict_types=1);

/** گروه‌هایی که کاربر فعلی اجازه دارد به دیگران بدهد */
function assignable_roles(): array
{
    $sql = is_full_access() ? 'SELECT * FROM roles ORDER BY id' : 'SELECT * FROM roles WHERE is_full_access = 0 ORDER BY id';
    return db_all($sql);
}

function admin_users(): string
{
    require_staff('users.manage');
    return view('admin/users', [
        'rows'  => db_all('SELECT u.*, r.name AS role_name, r.is_full_access FROM users u JOIN roles r ON r.id = u.role_id ORDER BY r.is_full_access DESC, u.name'),
        'title' => 'پرسنل',
    ], 'admin');
}

function admin_user_form(?int $id): string
{
    require_staff('users.manage');
    $u = $id ? db_one('SELECT u.*, r.is_full_access FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?', [$id]) : null;
    if ($id && !$u) {
        abort(404);
    }
    if ($u && (int) $u['is_full_access'] === 1 && !is_full_access()) {
        abort(403, 'فقط کاربران با دسترسی کامل می‌توانند این حساب را ویرایش کنند.');
    }
    return view('admin/user_form', ['u' => $u, 'roles' => assignable_roles(), 'title' => $u ? 'ویرایش ' . $u['name'] : 'تعریف پرسنل جدید'], 'admin');
}

function admin_user_save(?int $id): never
{
    $me = require_staff('users.manage');
    $back = $id ? "/admin/users/{$id}/edit" : '/admin/users/new';
    $existing = $id ? db_one('SELECT u.*, r.is_full_access FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?', [$id]) : null;
    if ($id && !$existing) {
        abort(404);
    }
    if ($existing && (int) $existing['is_full_access'] === 1 && !is_full_access()) {
        abort(403);
    }
    $name = (string) input('name', '');
    $mobile = (string) input('mobile', '');
    $roleId = input_int('role_id') ?? 0;
    $role = null;
    foreach (assignable_roles() as $r) {
        if ((int) $r['id'] === $roleId) {
            $role = $r;
        }
    }
    $pw = (string) ($_POST['password'] ?? '');
    $active = input('is_active') ? 1 : 0;
    $errors = [];
    if (mb_strlen($name) < 3) $errors[] = 'نام را وارد کنید.';
    if (!valid_mobile($mobile)) $errors[] = 'موبایل معتبر نیست.';
    if (!$role) $errors[] = 'گروه کاربری را انتخاب کنید.';
    if (db_value('SELECT 1 FROM users WHERE mobile = ? AND id <> ?', [$mobile, $id ?? 0])) $errors[] = 'این موبایل برای کاربر دیگری ثبت شده است.';
    if ((!$id || $pw !== '') && !valid_password($pw)) $errors[] = 'رمز باید حداقل ۱۰ کاراکتر و شامل حرف و عدد باشد.';
    if ($id && (int) $id === (int) $me['id'] && (!$active || (int) $roleId !== (int) $me['role_id'])) $errors[] = 'نمی‌توانید گروه یا وضعیت حساب خودتان را تغییر دهید.';
    if ($existing && (int) $existing['is_full_access'] === 1 && ($role && (int) $role['is_full_access'] === 0 || !$active)) {
        $others = (int) db_value('SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.is_full_access = 1 AND u.is_active = 1 AND u.id <> ?', [$id]);
        if ($others === 0) $errors[] = 'حداقل یک کاربر فعال با دسترسی کامل باید باقی بماند.';
    }
    if ($errors) {
        with_old($_POST);
        foreach ($errors as $err) flash('error', $err);
        redirect($back);
    }
    $data = ['name' => $name, 'mobile' => $mobile, 'email' => (string) input('email', '') ?: null, 'role_id' => $roleId, 'is_active' => $active];
    if ($pw !== '') {
        $data['password_hash'] = password_hash($pw, PASSWORD_DEFAULT);
    }
    if ($id) {
        db_update('users', $data, 'id = ?', [$id]);
        audit('update', 'users', $id);
    } else {
        $data['created_at'] = now();
        $id = db_insert('users', $data);
        audit('create', 'users', $id);
    }
    flash('success', 'حساب ذخیره شد.' . ($pw !== '' ? ' رمز را حضوری به همکار بدهید و بخواهید پس از اولین ورود تغییرش دهد.' : ''));
    redirect('/admin/users');
}

/* ---------- گروه‌های کاربری ---------- */

function admin_roles(): string
{
    require_staff('users.manage');
    return view('admin/roles', [
        'rows'  => db_all('SELECT r.*, (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id) AS n FROM roles r ORDER BY r.is_full_access DESC, r.id'),
        'title' => 'گروه‌های کاربری',
    ], 'admin');
}

function admin_role_form(?int $id): string
{
    require_staff('users.manage');
    $role = $id ? db_find('roles', $id) : null;
    if ($id && !$role) {
        abort(404);
    }
    if ($role && (int) $role['is_full_access'] === 1 && !is_full_access()) {
        abort(403);
    }
    return view('admin/role_form', ['role' => $role, 'perms' => $role ? (json_decode($role['permissions'], true) ?: []) : [], 'title' => $role ? 'ویرایش گروه' : 'گروه کاربری جدید'], 'admin');
}

function admin_role_save(?int $id): never
{
    require_staff('users.manage');
    $role = $id ? db_find('roles', $id) : null;
    if ($id && !$role) {
        abort(404);
    }
    if ($role && (int) $role['is_full_access'] === 1 && !is_full_access()) {
        abort(403);
    }
    $name = (string) input('name', '');
    if (mb_strlen($name) < 2) {
        flash('error', 'نام گروه را وارد کنید.');
        redirect($id ? "/admin/roles/{$id}/edit" : '/admin/roles/new');
    }
    $perms = array_values(array_intersect(array_keys(all_permissions()), (array) ($_POST['perms'] ?? [])));
    // فقط کاربر با دسترسی کامل می‌تواند مجوز مدیریت کاربران را به گروه دیگری بدهد
    if (!is_full_access() && in_array('users.manage', $perms, true) && !in_array('users.manage', json_decode($role['permissions'] ?? '[]', true) ?: [], true)) {
        $perms = array_values(array_diff($perms, ['users.manage']));
        flash('info', 'مجوز «تعریف پرسنل» را فقط کاربر با دسترسی کامل می‌تواند بدهد.');
    }
    $data = ['name' => $name, 'permissions' => json_encode($perms)];
    if ($id) {
        db_update('roles', $data, 'id = ?', [$id]);
    } else {
        $data['is_full_access'] = 0;
        $data['is_system'] = 0;
        $data['created_at'] = now();
        $id = db_insert('roles', $data);
    }
    audit('save', 'roles', $id);
    flash('success', 'گروه ذخیره شد.');
    redirect('/admin/roles');
}

function admin_role_delete(string $id): never
{
    require_staff('users.manage');
    $role = db_find('roles', (int) $id);
    if (!$role || (int) $role['is_system'] === 1 || (int) $role['is_full_access'] === 1) {
        flash('error', 'این گروه قابل حذف نیست.');
    } elseif (db_value('SELECT 1 FROM users WHERE role_id = ?', [$role['id']])) {
        flash('error', 'ابتدا کاربران این گروه را به گروه دیگری منتقل کنید.');
    } else {
        db_query('DELETE FROM roles WHERE id = ?', [$role['id']]);
        audit('delete', 'roles', (int) $role['id']);
        flash('success', 'گروه حذف شد.');
    }
    redirect('/admin/roles');
}
