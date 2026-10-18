<?php
declare(strict_types=1);

foreach (glob(APP_PATH . '/controllers/*.php') as $f) {
    require_once $f;
}
foreach (glob(APP_PATH . '/controllers/admin/*.php') as $f) {
    require_once $f;
}

/* ---------- سایت عمومی ---------- */
get('/', 'site_home');
get('/services', 'site_services');
get('/services/{slug}', 'site_service');
get('/posts', 'site_posts');
get('/posts/{slug}', 'site_post');
get('/contact', 'site_contact');
post('/contact', 'site_contact_send');
get('/messages', 'site_messages_lookup_form');
post('/messages', 'site_messages_lookup');
get('/messages/{code}', 'site_message_show');
get('/results', 'site_results');
post('/results', 'site_results_lookup');
get('/results/{id}/report', fn ($id) => site_result_file($id, 'report'));
get('/results/{id}/image/{img}', fn ($id, $img) => site_result_file($id, 'image', $img));

/* ---------- بیمار ---------- */
get('/login', 'patient_login_form');
post('/login', 'patient_login_send');
get('/login/verify', 'patient_verify_form');
post('/login/verify', 'patient_verify');
get('/profile/new', 'patient_profile_form');
post('/profile/new', 'patient_profile_save');
post('/logout', 'patient_logout');
get('/booking', 'booking_services');
get('/booking/done/{id}', 'booking_done');
get('/booking/{slug}', 'booking_slots');
post('/booking', 'booking_confirm');
get('/panel', 'patient_panel');
post('/panel/appointments/{id}/cancel', 'patient_cancel');

/* ---------- پنل مدیریت ---------- */
get('/admin/setup', 'admin_setup_form');
post('/admin/setup', 'admin_setup');
get('/admin/login', 'admin_login_form');
post('/admin/login', 'admin_login');
post('/admin/logout', 'admin_logout');
get('/admin/password', 'admin_password_form');
post('/admin/password', 'admin_password_save');
get('/admin', 'admin_dashboard');

get('/admin/appointments', 'admin_appointments');
get('/admin/appointments/new', 'admin_appointment_form');
post('/admin/appointments/new', 'admin_appointment_store');
get('/admin/appointments/{id}', 'admin_appointment_show');
post('/admin/appointments/{id}/status', 'admin_appointment_status');
post('/admin/appointments/{id}/notes', 'admin_appointment_notes');

get('/admin/patients', 'admin_patients');
get('/admin/patients/new', fn () => admin_patient_form(null));
post('/admin/patients/new', fn () => admin_patient_save(null));
get('/admin/patients/{id}', 'admin_patient_show');
get('/admin/patients/{id}/edit', fn ($id) => admin_patient_form((int) $id));
post('/admin/patients/{id}/edit', fn ($id) => admin_patient_save((int) $id));

get('/admin/results', 'admin_results');
post('/admin/results/{appointment}/upload', 'admin_result_upload');
post('/admin/results/{id}/publish', 'admin_result_publish');
post('/admin/results/{id}/unpublish', 'admin_result_unpublish');
post('/admin/results/{id}/report/delete', 'admin_result_report_delete');
post('/admin/result-images/{id}/delete', 'admin_result_image_delete');

get('/admin/services', 'admin_services');
get('/admin/services/new', fn () => admin_service_form(null));
post('/admin/services/new', fn () => admin_service_save(null));
get('/admin/services/{id}/edit', fn ($id) => admin_service_form((int) $id));
post('/admin/services/{id}/edit', fn ($id) => admin_service_save((int) $id));
post('/admin/categories', 'admin_category_save');
post('/admin/categories/{id}/delete', 'admin_category_delete');

get('/admin/schedules', 'admin_schedules');
post('/admin/schedules', 'admin_schedule_add');
post('/admin/schedules/{id}/delete', 'admin_schedule_delete');
post('/admin/exceptions', 'admin_exception_add');
post('/admin/exceptions/{id}/delete', 'admin_exception_delete');

get('/admin/insurances', 'admin_insurances');
post('/admin/insurances', 'admin_insurance_save');
post('/admin/insurances/{id}/delete', 'admin_insurance_delete');
get('/admin/gallery', 'admin_gallery');
post('/admin/gallery', 'admin_gallery_add');
post('/admin/gallery/{id}/delete', 'admin_gallery_delete');
get('/admin/posts', 'admin_posts');
get('/admin/posts/new', fn () => admin_post_form(null));
post('/admin/posts/new', fn () => admin_post_save(null));
get('/admin/posts/{id}/edit', fn ($id) => admin_post_form((int) $id));
post('/admin/posts/{id}/edit', fn ($id) => admin_post_save((int) $id));
post('/admin/posts/{id}/delete', 'admin_post_delete');
get('/admin/messages', 'admin_messages');
post('/admin/messages/{id}/read', 'admin_message_read');
post('/admin/messages/{id}/reply', 'admin_message_reply');
post('/admin/messages/{id}/delete', 'admin_message_delete');
get('/admin/settings', 'admin_settings');
post('/admin/settings', 'admin_settings_save');

get('/admin/users', 'admin_users');
get('/admin/users/new', fn () => admin_user_form(null));
post('/admin/users/new', fn () => admin_user_save(null));
get('/admin/users/{id}/edit', fn ($id) => admin_user_form((int) $id));
post('/admin/users/{id}/edit', fn ($id) => admin_user_save((int) $id));
get('/admin/roles', 'admin_roles');
get('/admin/roles/new', fn () => admin_role_form(null));
post('/admin/roles/new', fn () => admin_role_save(null));
get('/admin/roles/{id}/edit', fn ($id) => admin_role_form((int) $id));
post('/admin/roles/{id}/edit', fn ($id) => admin_role_save((int) $id));
post('/admin/roles/{id}/delete', 'admin_role_delete');

get('/admin/logs', 'admin_logs');
get('/admin/sms', 'admin_sms_logs');
post('/admin/sms/test', 'admin_sms_test');
