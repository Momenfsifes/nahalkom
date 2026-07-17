<?php
// السماح بطلبات CORS من نفس النطاق
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// قراءة بيانات JSON المرسلة من الـ Fetch
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// التحقق من صحة البيانات
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة أو فارغة']);
    exit;
}

// التحقق من الحقول الإجبارية
$required = ['institutionId', 'certNumber', 'traineeName', 'nationalId', 'courseName', 'startDate', 'endDate', 'hours', 'days', 'status'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "الحقل '$field' مطلوب"]);
        exit;
    }
}

// إضافة معرف فريد ووقت الإنشاء
$data['id'] = uniqid('cert_');
$data['createdAt'] = date('Y-m-d H:i:s');

// تحديد مسار ملف التخزين (خارج مجلد api للأمان)
$file = __DIR__ . '/../certificates.json';

// قراءة الشهادات الموجودة مسبقاً (إن وجدت)
$certificates = [];
if (file_exists($file)) {
    $existingData = file_get_contents($file);
    $certificates = json_decode($existingData, true) ?: [];
}

// إضافة الشهادة الجديدة للمصفوفة
$certificates[] = $data;

// حفظ البيانات مجدداً في الملف
if (file_put_contents($file, json_encode($certificates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['success' => true, 'message' => 'تم حفظ الشهادة بنجاح']);
} else {
    echo json_encode(['success' => false, 'message' => 'فشل في حفظ الملف. تأكد من صلاحيات المجلد (Permissions)']);
}
?>