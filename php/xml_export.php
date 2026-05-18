<?php
session_start();

require_once '../config/db.php';

if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    header('Content-Type: text/plain');
    echo 'Access denied.';
    exit;
}

$stmt = $pdo->query(
    'SELECT id, title, code, department, instructor, credits, capacity, enrolled, description, created_at
     FROM courses
     ORDER BY department, title'
);
$courses = $stmt->fetchAll();

header('Content-Type: application/xml; charset=utf-8');
header('Content-Disposition: attachment; filename="greenfield-courses.xml"');

$xml = new DOMDocument('1.0', 'UTF-8');
$xml->formatOutput = true;

$root = $xml->createElement('courses');
$root->setAttribute('institute', 'Greenfield Institute');
$root->setAttribute('exported', date('c'));
$xml->appendChild($root);

foreach ($courses as $course) {
    $node = $xml->createElement('course');
    $node->setAttribute('id', (string) $course['id']);

    $fields = [
        'title'       => $course['title'],
        'code'        => $course['code'],
        'department'  => $course['department'],
        'instructor'  => $course['instructor'],
        'credits'     => (string) $course['credits'],
        'capacity'    => (string) $course['capacity'],
        'enrolled'    => (string) $course['enrolled'],
        'description' => $course['description'] ?? '',
        'created_at'  => $course['created_at'],
    ];

    foreach ($fields as $tag => $value) {
        $el = $xml->createElement($tag);
        $el->appendChild($xml->createTextNode($value));
        $node->appendChild($el);
    }

    $root->appendChild($node);
}

echo $xml->saveXML();
